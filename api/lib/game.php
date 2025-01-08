<?php
require_once "dbcon.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

function getState(int $id): void{
	global $conn;
	$sql = 'SELECT json_data FROM boards WHERE room_id = 1';
	$st = $conn->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	$r = $res->fetch_assoc();

	$board = json_decode($r['json_data'], true);
	$board[0][0] = 2; // test //
	$board[0][19] = 1; // test //
	$board[19][0] = 3; // test //
	$board[19][19] = 4; // test //
	echo json_encode($board);
}

function getBitMask($room_id) {
	global $conn;
	$sql = 'select getBitMask(?,?)';
	$st = $conn->prepare($sql);
	$st->bind_param('ss', $_SESSION['TOKEN'], $room_id);
	$st->execute();
	$res= $st->get_result();
	$r = $res->fetch_assoc();

	if ($res->num_rows <= 0) {
		echo json_encode("Error in recieving the bitmask");
		exit();
	}

	$row = $res->fetch_assoc();
	return $r['getBitMask(?,?)'];
}
	
function apitest() {
	global $conn;

	$sql = 'SELECT json_data FROM boards WHERE room_id = 1';
	$st = $conn->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	$r = $res->fetch_assoc();

	$board = json_decode($r['json_data'], true);
	/* test */
	foreach ($board as &$row) {
		if (count($row) > 0) {
			$row[0] = 1;
			$row[count($row) - 1] = 2;
		}
	}

	header('Content-Type: application/json');
	echo json_encode($board);
}

$blocks = [
	[[1]],
	
	[[1, 1]],
	
	[[1, 1, 1]],
	
	[[0, 1],
	 [1, 1]],
	
	[[1, 1, 1, 1]],
	
	[[1, 1],
	 [1, 1]],
	
	[[1, 0],
	 [1, 1],
	 [1, 0]],
	
	[[1, 0],
	 [1, 1],
	 [0, 1]],
	
	[[1, 1],
	 [0, 1],
	 [0, 1]],
	
	[[1, 1, 1, 1, 1]], //
	
	[[1, 1],
	 [1, 1],  // p
	 [1, 0]],
	
	[[1, 0, 0],
	 [1, 1, 1],	   // t
	 [1, 0, 0]],
	
	[[0, 1, 0],
	 [1, 1, 1],	   // x
	 [0, 1, 0]],
	
	[[0, 0, 1],
	 [0, 1, 1],	   // w
	 [1, 1, 0]],
	
	[[0, 0, 1],
	 [0, 0, 1],	   // v
	 [1, 1, 1]],
	
	[[1, 0, 1],
	 [1, 1, 1]],	  // u
	
	[[0, 1, 1],
	 [1, 1, 0],	   // f
	 [0, 1, 0]],
	
	[[1, 1],
	 [0, 1],  // l
	 [0, 1],
	 [0, 1]],
	
	[[0, 1],
	 [1, 1],  // n
	 [1, 0],
	 [1, 0]],
	
	[[0, 1],
	 [1, 1],  // y
	 [0, 1],
	 [0, 1]],
	
	[[1, 1, 0],
	 [0, 1, 0],	   // z
	 [0, 1, 0],
	 [0, 1, 1]]
];

function isPieceAvailable($bitmask, $piece) {
        if ($piece < 0 or $piece > 20) {
                throw new InvalidArgumentException("Invalid piece: $piece");
        }

        return ($bitmask & (1 << $piece)) !== 0;
}

function test() {
	$bitmask = 0b101011000000000000001;
	//$piece = 'z';
	$piece = 20;
	
	if (isPieceAvailable($bitmask, $piece)) {
		echo "Piece $piece is available.";
	} else {
		echo "Piece $piece is not available.";
	}

	removePiece($bitmask, $piece);

	if (isPieceAvailable($bitmask, $piece)) {
		echo "Piece $piece is available.";
	} else {
		echo "Piece $piece is not available.";
	}

	global $conn;
	$sql = 'select json_data from boards where room_id = 1';
	$st = $conn->prepare($sql);
	$st->execute();
	$res= $st->get_result();
	$row = $res->fetch_assoc();

	if ($row && isset($row['json_data'])) {
		$json_data = $row['json_data'];
	
		$board_array = json_decode($json_data, true);
	
		if (json_last_error() === JSON_ERROR_NONE) {
			var_dump($board_array);
		} else {
			echo "Error decoding JSON: " . json_last_error_msg();
		}
	} else {
		echo "No data found for room_id 1.";
	}
}

//function placePiece($piece, $room_id, $x, $y) {
//	global $conn;
//
//	/* check turn */
//	/* check if piece fits */
//	/* get board */
//	/* get color */
//	/* paste */
//	/* update board */
//	/* remove piece */
//	/* next turn */
//}

