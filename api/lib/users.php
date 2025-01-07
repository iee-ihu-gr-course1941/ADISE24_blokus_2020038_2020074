<?php
require_once "dbcon.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

function authUser($username,$password): void{
	global $conn;
	$sql = 'select * from users where username=? and password=?';
	$st = $conn->prepare($sql);
	$st->bind_param('ss', $username, $password);
	$st->execute();
	$res = $st->get_result();
	$r = $res->fetch_assoc();

	if ($r['session_id'] == '') {
		http_response_code(401);
		echo json_encode("Wrong credentials");
		echo json_encode($username . ' ' . $password);
	}else{
		$_SESSION['TOKEN'] = $r["session_id"];
		http_response_code(200);
		echo json_encode('Successful Login');
	}

}

function createUser(string $username,string $password): void{
	global $conn;
	$sql = 'select createUser(?,?)';
	$st = $conn->prepare($sql);
	$st->bind_param("ss",$username);
	$st->execute();
	$res = $st->get_result();
	$s = $res->fetch_assoc();
	if ($s['createUser(?,?)']){
		http_response_code(201);
		echo json_encode("User created");
	}else{
		http_response_code(400);
		echo json_encode("Could not create user");
	}
}

function showUserInfo(): void{
	global $conn;
	$sql = 'select getUserInfo(?)';
	$st = $conn->prepare($sql);
	$st->bind_param('s',$_SESSION['TOKEN']);
	$st->execute();
	$res= $st->get_result();
	$r = $res->fetch_assoc();
	print_r($r['getUserInfo(?)']);
}

function getScoreboard(): void{
	global $conn;
	$sql = 'select getScoreboard()';
	$st = $conn->prepare($sql);
	$st->execute();
	$res= $st->get_result();
	$r = $res->fetch_assoc();
	print_r($r['getScoreboard()']);
}

