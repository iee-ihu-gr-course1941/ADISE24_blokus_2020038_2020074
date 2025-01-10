--drop table if exists users;
create table if not exists users
(
	id		int auto_increment primary key,
	username	varchar(255)  not null,
	session_id	varchar(255)  null,
	password	varchar(255)  not null,
	score		int default 0 not null
);

drop function if exists createUser;
DELIMITER $$
create function createUser(username_param varchar(255), password_param varchar(255)) returns tinyint(1)
begin
	if exists(select * from users where username= username_param) then
		signal sqlstate '45000' set message_text = 'Username already exists';
	else
		insert into users(username,password,session_id) values (username_param,password_param,LEFT(UUID(), 8));
		return true;
	end if;
end;

drop function if exists getUserInfo;
create function getUserInfo(session_id_param varchar(255)) returns longtext
begin
	declare id_val int;
	declare username_val varchar(255);
	declare score_val int;

	select id into id_val
	from users
	where session_id = session_id_param;

	if id_val is null then
		signal sqlstate '45000' set message_text = 'You need to login first';
	end if;

	select username, id, score from users where session_id=session_id_param into username_val, id_val, score_val;

	return json_object(
			'username',username_val,
			'id',id_val,
			'score',score_val
		   );
end;

drop function if exists getScoreboard;
create function getScoreboard() returns longtext
begin
	return (
		select json_arrayagg(
			json_object(
				'username', username,
				'score', score
			)
		)
		from users
		order by score desc
	);
end;


-- rooms

-- drop table if exists rooms;
create table if not exists rooms (
	room_index int auto_increment primary key,
	player_a varchar(255),
	player_b varchar(255),
	player_c varchar(255),
	player_d varchar(255),
	a_mask bigint default 2097151,	-- bitmask for pieces remaining for player a (21 bits set by default)
	b_mask bigint default 2097151,
	c_mask bigint default 2097151,
	d_mask bigint default 2097151,
	a_state tinyint default 0,	-- 0 playing, 1 blocked, 2 placed all pieces
	b_state tinyint default 0,
	c_state tinyint default 0,
	d_state tinyint default 0,
	a_activity TIMESTAMP default NULL,
	b_activity TIMESTAMP default NULL,
	c_activity TIMESTAMP default NULL,
	d_activity TIMESTAMP default NULL,
	turn_index int default 0,
	password varchar(255),		-- optional password for private games (hashed for security)
	created_at datetime default current_timestamp
);

-- drop table if exists boards;
create table if not exists boards (
	room_id int primary key,
	json_data text default '[ [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0], [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0] ]'
);
-- "
drop function if exists createRoom;
create function createRoom(session_id_param varchar(255), 
						   password_param varchar(255)) 
returns int
begin

	declare username_a varchar(255);
	declare room_id int;

	select username from users where session_id=session_id_param into username_a;
	IF username_a IS NULL THEN
		SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No user found for given session ID';
	END IF;

	insert into rooms (player_a, password, turn_index, created_at)
	values (username_a,
			case when password_param is not null and password_param != '' 
				 then password(password_param) 
				 else null 
			end,
			1,
			current_timestamp);

	set room_id = last_insert_id();
	insert into boards (room_id) values (room_id);
	
	return room_id;
end;


drop function if exists getRooms;
create function getRooms() returns longtext
begin
	return (
		select json_arrayagg(
			json_object(
				'id', room_index,
				'status',
				case
					when player_a is not null 
						 and player_b is not null 
						 and player_c is not null 
						 and player_d is not null then 'full'
					else 'not full'
				end,
				'access',
				case
					when password is not null then 'private'
					else 'public'
				end,
				"created_at", created_at
			)
		)
		from rooms
	);
end$$

DELIMITER ;

DELIMITER $$

drop function if exists joinRoom;
create function joinRoom(room_index_param int, session_id_param varchar(255), password_param varchar(255))
returns varchar(255)
deterministic
begin
	declare username_tmp varchar(255);
	declare password_tmp varchar(255);
	declare player_a_tmp varchar(255);
	declare player_b_tmp varchar(255);
	declare player_c_tmp varchar(255);
	declare player_d_tmp varchar(255);
	declare updated_field varchar(255);

	select password into password_tmp
	from rooms
	where room_index = room_index_param limit 1;
	if not (password_tmp = password_param)
	   or not (password_tmp is NULL and password_param = '') then
		return "Wrong password";
	end if;

	select username into username_tmp
	from users
	where session_id = session_id_param limit 1;
	
	-- get currnt players
	select player_a, player_b, player_c, player_d
	into player_a_tmp, player_b_tmp, player_c_tmp, player_d_tmp
	from rooms
	where room_index = room_index_param; 

	if player_a_tmp = username_tmp or player_b_tmp = username_tmp or player_c_tmp = username_tmp or player_d_tmp = username_tmp then
		return "Already joined.";
	end if;

	-- check for the first empty field and update
	if player_a_tmp is null then
		update rooms set player_a = username_tmp where room_index = room_index_param limit 1;
		set updated_field = 'player_a';
	elseif player_b_tmp is null then
		update rooms set player_b = username_tmp where room_index = room_index_param limit 1;
		set updated_field = 'player_b';
	elseif player_c_tmp is null then
		update rooms set player_c = username_tmp where room_index = room_index_param limit 1;
		set updated_field = 'player_c';
	elseif player_d_tmp is null then
		update rooms set player_d = username_tmp where room_index = room_index_param limit 1;
		set updated_field = 'player_d';
	else
		set updated_field = 'no empty slots available.';
	end if;
	
	return updated_field;
end$$

DELIMITER ;

DELIMITER $$
drop function if exists getState;
create function getState(room_index_param int) returns longtext
begin
	return (
		select json_arrayagg(
			json_object(
				-- 'id', room_id,
				'board', json_data
			)
		)
		from boards
		where room_id = room_index_param
	);
end$$

DELIMITER ;

DELIMITER $$
-- 12abcdefghilnptuvwxyz
drop function if exists getBitMask;
create function getBitMask(session_id_param varchar(255), room_id_param int) 
returns int
deterministic
begin
	declare username_val varchar(255);
	declare bitmask int;

	select username from users where session_id=session_id_param into username_val;

	select 
		case 
			when player_a = username_val then a_mask
			when player_b = username_val then b_mask
			when player_c = username_val then c_mask
			when player_d = username_val then d_mask
			else null
		end
	into bitmask
	from rooms
	where (player_a = username_val
	   or player_b = username_val 
	   or player_c = username_val 
	   or player_d = username_val)
	   and room_index = room_id_param
	limit 1;

	return bitmask;
end$$

DELIMITER ;

DELIMITER $$

drop function if exists getPlayerState;
create function getPlayerState(n int, room_id_param int)
returns int
deterministic
begin
        declare state_val int;

        select
                case
                        when n = 1 then a_state
                        when n = 2 then b_state
                        when n = 3 then c_state
                        when n = 4 then d_state
                        else null
                end
        into state_val
        from rooms
        where room_index = room_id_param
        limit 1;

        return state_val;
end$$

DELIMITER ;

DELIMITER $$
-- 12abcdefghilnptuvwxyz
drop function if exists getTurn;
create function getTurn(room_id_param int) 
returns varchar(255)
deterministic
begin
	-- declare username_val varchar(255);
	declare turn_val int;
	select turn_index into turn_val from rooms where room_index = room_id_param;

	if turn_val = 1 then
		return (select player_a from rooms where room_index = room_id_param);
	elseif turn_val = 2 then
		return (select player_b from rooms where room_index = room_id_param);
	elseif turn_val = 3 then
		return (select player_c from rooms where room_index = room_id_param);
	elseif turn_val = 4 then
		return (select player_d from rooms where room_index = room_id_param);
	end if;
end$$

DELIMITER ;

DELIMITER $$

drop procedure if exists updateBitMask;
create procedure updateBitMask(
	in session_id_param varchar(255),
	in room_id_param int,
	in new_bitmask int
)
begin
	declare username_val varchar(255);

	-- retrieve username from session_id
	select username
	into username_val
	from users
	where session_id = session_id_param;

	-- update the appropriate bitmask column
	update rooms
	set 
		a_mask = case when player_a = username_val then new_bitmask else a_mask end,
		b_mask = case when player_b = username_val then new_bitmask else b_mask end,
		c_mask = case when player_c = username_val then new_bitmask else c_mask end,
		d_mask = case when player_d = username_val then new_bitmask else d_mask end
	where 
		room_index = room_id_param
		and (player_a = username_val
			 or player_b = username_val
			 or player_c = username_val
			 or player_d = username_val);

end$$

DELIMITER ;

DELIMITER $$

drop procedure if exists updatePlayerState;
create procedure updatePlayerState(
        in n int,
        in room_id_param int,
        in new_state int
)
begin
        update rooms
        set
                a_state = case when n = 1 then new_state else a_state end,
                b_state = case when n = 2 then new_state else b_state end,
                c_state = case when n = 3 then new_state else c_state end,
                d_state = case when n = 4 then new_state else d_state end
        where
                room_index = room_id_param
                and (
                        (n = 1 and player_a is not null) or
                        (n = 2 and player_b is not null) or
                        (n = 3 and player_c is not null) or
                        (n = 4 and player_d is not null)
                );
end$$

DELIMITER ;


DELIMITER $$
drop function if exists getColor;
create function getColor(session_id_param varchar(255), room_id_param int)
returns int
deterministic
begin
	declare username_val varchar(255);
	declare color int;

	select username
	into username_val
	from users
	where session_id = session_id_param;

	select 
		case 
			when player_a = username_val then 1
			when player_b = username_val then 2
			when player_c = username_val then 3
			when player_d = username_val then 4
			else null
		end
	into color
	from rooms
	where room_index = room_id_param
	  and username_val in (player_a, player_b, player_c, player_d);

	return color;
end$$

DELIMITER ;

DELIMITER $$

DROP PROCEDURE IF EXISTS updateTurn$$
CREATE PROCEDURE updateTurn(current_turn INT, room_index_param INT)
BEGIN
    DECLARE new_turn INT;
    DECLARE a_state_val INT;
    DECLARE b_state_val INT;
    DECLARE c_state_val INT;
    DECLARE d_state_val INT;
    DECLARE iteration_count INT;

    SELECT a_state, b_state, c_state, d_state
    INTO a_state_val, b_state_val, c_state_val, d_state_val
    FROM rooms
    WHERE room_index = room_index_param
    LIMIT 1;

    SET new_turn = current_turn;
    SET iteration_count = 0;

    -- Loop until a valid turn is found or the safety limit is reached
    REPEAT
        SET iteration_count = iteration_count + 1;

        SET new_turn = CASE
            WHEN new_turn = 4 THEN 1
            ELSE new_turn + 1
        END;

    UNTIL
        ((new_turn = 1 AND a_state_val = 0) OR
         (new_turn = 2 AND b_state_val = 0) OR
         (new_turn = 3 AND c_state_val = 0) OR
         (new_turn = 4 AND d_state_val = 0)) 
         OR iteration_count > 4
    END REPEAT;

    --  test
    IF iteration_count <= 4 THEN
        UPDATE rooms
        SET turn_index = new_turn
        WHERE room_index = room_index_param
        LIMIT 1;
    END IF;
END$$

DELIMITER ;

-- DELIMITER $$
-- 
-- drop procedure if exists updateTurn$$
-- 
-- create procedure updateTurn(current_turn int, room_index_param int)
-- begin
-- 	declare new_turn int;
-- 
-- 	set new_turn = case
-- 		when current_turn = 4 then 1
-- 		else current_turn + 1
-- 	end;
-- 
-- 	update rooms
-- 	set turn_index = new_turn
-- 	where room_index = room_index_param limit 1;
-- end$$
-- 
-- DELIMITER ;

DELIMITER $$

drop function if exists getPlayers $$
create function getPlayers(room_index_param int)
returns json
deterministic
begin
	declare result json;

	select json_object(
		player_a, 1,
		player_b, 2,
		player_c, 3,
		player_d, 4
	)
	into result
	from rooms
	where room_index = room_index_param
	limit 1;

	return result;
end $$

DELIMITER ;
DELIMITER $$

drop function if exists getPosition $$
create function getPosition(room_id_param varchar(255), session_id_param varchar(255))
returns int
deterministic
begin
	declare player_position int default 0;
	declare username_val varchar(255);

	select username into username_val
	from users
	where session_id = session_id_param;

	if username_val is not null then
		select case 
			when player_a = username_val then 1
			when player_b = username_val then 2
			when player_c = username_val then 3
			when player_d = username_val then 4
			else 0
		end into player_position
		from rooms
		where room_index = room_id_param;
	end if;

	return player_position;
end$$

DELIMITER ;

DELIMITER $$

drop procedure if exists updateActivity $$
create procedure updateActivity(session_id_param varchar(255), room_id_param int)
deterministic
begin
	declare username_val varchar(255);

	-- retrieve username from session_id
	select username
	into username_val
	from users
	where session_id = session_id_param;

	-- update the appropriate bitmask column
	update rooms
	set 
		a_activity = case when player_a = username_val then current_timestamp else a_activity end,
		b_activity = case when player_b = username_val then current_timestamp else b_activity end,
		c_activity = case when player_c = username_val then current_timestamp else c_activity end,
		d_activity = case when player_d = username_val then current_timestamp else d_activity end
	where 
		room_index = room_id_param
		and (player_a = username_val
			 or player_b = username_val
			 or player_c = username_val
			 or player_d = username_val);
end$$

DELIMITER ;

DELIMITER $$

drop procedure if exists removePlayer$$

create procedure removeplayer(username_param varchar(255), room_index_param int)
begin
    update rooms
    set
        player_a = case when player_a = username_param then null else player_a end,
        player_b = case when player_b = username_param then null else player_b end,
        player_c = case when player_c = username_param then null else player_c end,
        player_d = case when player_d = username_param then null else player_d end,
        a_activity = case when player_a = username_param then null else a_activity end,
        b_activity = case when player_b = username_param then null else b_activity end,
        c_activity = case when player_c = username_param then null else c_activity end,
        d_activity = case when player_d = username_param then null else d_activity end
    where
        (player_a = username_param or player_b = username_param or player_c = username_param or player_d = username_param)
        and room_index = room_index_param;
end$$

DELIMITER ;

