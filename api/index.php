<?php

header("Content-type: application/json; charset=UTF-8");
//require_once "lib/ErrorHandler.php";
//set_exception_handler("ErrorHandler::handleException");
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
			if (count($requestURI) < 7) {
				echo json_encode("Not enough arguments provided.");
				exit();
			}
			authUser($requestURI[5],$requestURI[6]);
			break;
		} else if ($requestURI[4]=="create"){
			if (count($requestURI) < 7) {
				echo json_encode("Not enough arguments provided.");
				exit();
			}
			createUser($requestURI[5],$requestURI[6]);
			break;
		} else if ($requestURI[4]=='info'){
			showUserInfo();
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
				if (count($requestURI) < 6) {
					echo json_encode("Not enough arguments provided.");
					exit();
				}
				getState($requestURI[5]);
				break;
			}
			case 'turn':
			{
				if (count($requestURI) < 6) {
					echo json_encode("Not enough arguments provided.");
					exit();
				}
				getTurn($requestURI[5]);
				break;
			}
			case 'pieces':
			{
				if (count($requestURI) < 6) {
					echo json_encode("Not enough arguments provided.");
					exit();
				}
				getPieces($requestURI[5]);
				break;
			}
			case 'pieceids':
			{
				if (count($requestURI) < 6) {
					echo json_encode("Not enough arguments provided.");
					exit();
				}
				getPieceIDs($requestURI[5]);
				break;
			}
			case 'players':
			{
				if (count($requestURI) < 6) {
					echo json_encode("Not enough arguments provided.");
					exit();
				}
				getPlayers($requestURI[5]);
				break;
			}
			case 'position':
			{
				if (count($requestURI) < 6) {
					echo json_encode("Not enough arguments provided.");
					exit();
				}
				getPosition($requestURI[5]);
				break;
			}
			case 'update_activity':
			{
				if (count($requestURI) < 6) {
					echo json_encode("Not enough arguments provided.");
					exit();
				}
				updateActivity($requestURI[5]);
				break;
			}
			case 'scores':
			{
				if (count($requestURI) < 6) {
					echo json_encode("Not enough arguments provided.");
					exit();
				}
				calculateScores($requestURI[5]);
				break;
			}
			case 'place':
			{
				if (count($requestURI) == 10) {
					placePiece($requestURI[6], $requestURI[5], $requestURI[7],$requestURI[8],$requestURI[9]);
				} else  {
					echo json_encode("Wrong number of arguments were provided.");
				}
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
	default:{
		echo json_encode("Not found.");
		http_response_code(404);
		break;
	}
}
