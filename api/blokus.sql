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
