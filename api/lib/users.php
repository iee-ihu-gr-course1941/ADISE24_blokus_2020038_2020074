<?php
require_once "dbcon.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
