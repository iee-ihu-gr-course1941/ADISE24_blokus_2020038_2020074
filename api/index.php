<?php

header("Content-type: application/json; charset=UTF-8");
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

$requestURI = explode("/",$_SERVER["REQUEST_URI"]);
$httpMethod = $_SERVER['REQUEST_METHOD'];

switch ($requestURI[3]) {
	case 'user':{
		if ($requestURI[4]=='auth'){
			echo json_encode("authenticate");
			break;
		} else if ($requestURI[4]=="create"){
			echo json_encode("create user");
			break;
		} else if ($requestURI[4]=='info'){
			echo json_encode("show info");
			break;
		} else if ($requestURI[4]=='test'){
			echo json_encode("hi");
			break;
		} else if ($requestURI[4]=='scoreboard'){
			echo json_encode("show scoreboard");
			break;
	} else {
			http_response_code(404);
			break;
		}
		break;
	}
	/*case 'rooms':
	{
		break;
	}*/
	default:{
		echo json_encode("Not found.");
	http_response_code(404);
		break;
	}
}
