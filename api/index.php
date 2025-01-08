<?php

header("Content-type: application/json; charset=UTF-8");
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "lib/users.php";
require_once "lib/rooms.php";
require_once "lib/game.php";

$requestURI = explode("/",$_SERVER["REQUEST_URI"]);
$httpMethod = $_SERVER['REQUEST_METHOD'];
//echo '<pre>';
//print_r($requestURI);
//echo '</pre>';

switch ($requestURI[3]) {
	case 'user':{
		if ($requestURI[4]=='auth'){
			authUser($requestURI[5],$requestURI[6]);
			break;
		} else if ($requestURI[4]=="create"){
			createUser($requestURI[5],$requestURI[6]);
			break;
		} else if ($requestURI[4]=='info'){
			showUserInfo();
			break;
		} else if ($requestURI[4]=='lol'){
			echo json_encode("hi");
			break;
		} else if ($requestURI[4]=='scoreboard'){
			getScoreboard();
			break;
	} else {
			http_response_code(404);
			break;
		}
		break;
	}
	case 'game':
	{
		switch ($requestURI[4]) {
			case 'state':
			{
				getState($requestURI[5]);
				break;
			}
			default:
			{
				http_response_code(404);
				echo json_encode("Not found.");
				break;
			}
		}
		break;
	}
	case "rooms": {
		if ($requestURI[4]=='join'){
			if (count($requestURI) == 6) {
				joinRoom($requestURI[5], "");
			} else {
				joinRoom($requestURI[5], $requestURI[6]);
			}
			break;
		} else if ($requestURI[4]=='info'){
			getRooms();
			break;
		} else if ($requestURI[4]=='create'){
			if (count($requestURI) == 5) {
				createRoom("");
			} else {
				createRoom($requestURI[5]);
			}
			break;
		} else {
			http_response_code(404);
			json_encode('Not found.');
			break;
		}
	}
	case "test": {
		test();
		break;
	}
	case "apitest": {
		apitest();
		break;
	}
	default:{
		echo json_encode("Not found.");
		http_response_code(404);
		break;
	}
}
