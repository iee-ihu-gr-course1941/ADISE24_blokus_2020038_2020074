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
				CASE
					WHEN player_a IS NOT NULL 
						 AND player_b IS NOT NULL 
						 AND player_c IS NOT NULL 
						 AND player_d IS NOT NULL 
						 AND password IS NOT NULL THEN 'full'
					ELSE 'not full'
				END,
				'access',
				CASE
					WHEN password IS NOT NULL THEN 'private'
					ELSE 'public'
				END,
				"created_at", created_at
			)
		)
		from rooms
	);
end$$

DELIMITER ;

DELIMITER $$

drop function if exists joinRoom;
create function joinroom(room_index_param int, session_id_param varchar(255), password_param varchar(255))
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
create function getbitmask(session_id_param varchar(255), room_id_param int) 
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
