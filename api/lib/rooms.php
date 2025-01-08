<?php
require_once "dbcon.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

function createRoom(string $password): void{
	/*echo json_encode("SESSION ID: " . $_SESSION['TOKEN']);*/
	global $conn;
	if (!empty($password)) {
		$hashed_password = sha1($password);
	} else {
		$hashed_password = "";
	}
	$sql = 'select createRoom(?,?)';
	$st = $conn->prepare($sql);
	$st->bind_param("ss", $_SESSION['TOKEN'], $hashed_password);
	$st->execute();
	$res = $st->get_result();
	$s = $res->fetch_assoc();
	if ($s['createRoom(?,?)']){
		http_response_code(201);
		if (empty($password)) {
			echo json_encode("Public room created.");
		} else {
			echo json_encode("Private room created.");
		}
	} else {
		http_response_code(400);
		echo json_encode("Room could not be created.");
	}
}

function joinRoom(int $id, string $password): void{
	global $conn;
	if (!empty($password)) {
		$hashed_password = sha1($password);
	} else {
		$hashed_password = "";
	}
	$sql = 'select joinRoom(?,?,?)';
	$st = $conn->prepare($sql);
	$st->bind_param('sss', $id, $_SESSION['TOKEN'], $hashed_password);
	$st->execute();
	$res= $st->get_result();
	$r = $res->fetch_assoc();
	print_r(json_encode($r['joinRoom(?,?,?)']));
}

function getRooms(): void{
	global $conn;
	$sql = 'select getRooms()';
	$st = $conn->prepare($sql);
	$st->execute();
	$res= $st->get_result();
	$r = $res->fetch_assoc();
	print_r($r['getRooms()']);
}
