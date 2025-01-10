<?php
require_once "dbcon.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

function getState(int $id): void{
	global $conn;
	$sql = 'SELECT json_data FROM boards WHERE room_id = ?';
	$st = $conn->prepare($sql);
	$st->bind_param('i', $id);
	$st->execute();
	$res = $st->get_result();
	$r = $res->fetch_assoc();

	$board = json_decode($r['json_data'], true);
	echo json_encode($board);
}

function getTurn(int $id): void{
	global $conn;
	$sql = 'select turn_index from rooms where room_index=?';
	$st = $conn->prepare($sql);
	$st->bind_param('s', $id);
	$st->execute();
	$res= $st->get_result();
	$row = $res->fetch_assoc();
	print_r($row['turn_index']);
}

function getPlayers(int $id): void{
	global $conn;
	$sql = 'select getPlayers(?)';
	$st = $conn->prepare($sql);
	$st->bind_param('s', $id);
	$st->execute();
	$res= $st->get_result();
	$row = $res->fetch_assoc();
	print_r($row['getPlayers(?)']);
}

function getPosition(int $id): void{
	global $conn;
	$sql = 'select getPosition(?,?)';
	$st = $conn->prepare($sql);
	$st->bind_param('ss', $id, $_SESSION['TOKEN']);
	$st->execute();
	$res= $st->get_result();
	$row = $res->fetch_assoc();
	print_r($row['getPosition(?,?)']);
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

function removePiece(&$bitmask, $piece) {
	if ($piece < 0 or $piece > 20) {
		throw new InvalidArgumentException("Invalid piece: $piece");
	}

	$bitmask &= ~(1 << $piece);
}

function isPieceAvailable($bitmask, $piece) {
	if ($piece < 0 or $piece > 20) {
		throw new InvalidArgumentException("Invalid piece: $piece");
	}

	return ($bitmask & (1 << $piece)) !== 0;
}

function getPieces($id) {
	global $blocks;
	$bitmask = getBitMask($id);

	$pieces = [];

	for ($i = 0; $i < count($blocks); $i++) {
		if (($bitmask & (1 << $i)) !== 0) {
			$pieces[] = $blocks[$i];
		}
	}

	echo json_encode($pieces);
}

function getPieceIDs($id) {
	global $blocks;
	$bitmask = getBitMask($id);

	$piece_ids = [];

	for ($i = 0; $i < count($blocks); $i++) {
		if (($bitmask & (1 << $i)) !== 0) {
			$piece_ids[] = $i;
		}
	}

	echo json_encode($piece_ids);
}

function test() {
	$bitmask = 0b101011000000000000001;
	$piece = 'z';
	
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

function rotateArray(array $matrix, int $r): array {
    /* rotation angle 0, 90, 180, 270 */
    $r = $r % 360; /* ensure angle is within 0-359 */
    if ($r < 0) {
        $r += 360;
    }

    switch ($r) {
        case 90:
            return rotate90($matrix);
        case 180:
            return rotate90(rotate90($matrix));
        case 270:
            return rotate90(rotate90(rotate90($matrix)));
        case 0:
        default:
            return $matrix;
    }
}

function rotate90(array $matrix): array {
    $rotated = [];
    $rowCount = count($matrix);
    $colCount = count($matrix[0]);

    for ($col = 0; $col < $colCount; $col++) {
        $newRow = [];
        for ($row = $rowCount - 1; $row >= 0; $row--) {
            $newRow[] = $matrix[$row][$col];
        }
        $rotated[] = $newRow;
    }

    return $rotated;
}

function paste(&$board, $b9, $x, $y): void {
	$b9_rows = count($b9);
	$b9_cols = count($b9[0]);

	for ($i = 0; $i < $b9_rows; $i++) {
		for ($j = 0; $j < $b9_cols; $j++) {
			if ($b9[$i][$j] != 0) {
				$board[$y + $i][$x + $j] = $b9[$i][$j];
			}
		}
	}
}

function canPaste(&$board, $b9, $x, $y, $good_color) {
	$b9_rows = count($b9);
	$b9_cols = count($b9[0]);
	$board_rows = count($board);
	$board_cols = count($board[0]);
	$diagonal_connection = false;

	/* out of bounds check */
	if ($x < 0 || $y < 0 || $x + $b9_cols > $board_cols || $y + $b9_rows > $board_rows) {
		return false;
	}

	$orthogonal_dirs = [[-1, 0], [1, 0], [0, -1], [0, 1]];
	$diagonal_dirs = [[-1, -1], [-1, 1], [1, -1], [1, 1]];

	for ($i = 0; $i < $b9_rows; $i++) {
		for ($j = 0; $j < $b9_cols; $j++) {
			if ($b9[$i][$j] != 0) {
				$board_x = $x + $j;
				$board_y = $y + $i;

				/* check if already occupied */
				if ($board[$board_y][$board_x] != 0) {
					return false;
				}

				/* check orthogonal adjacency */
				foreach ($orthogonal_dirs as [$dy, $dx]) {
					$ny = $board_y + $dy;
					$nx = $board_x + $dx;

					if ($ny >= 0 && $ny < $board_rows && $nx >= 0 && $nx < $board_cols) {
						if ($board[$ny][$nx] == $good_color) {
							return false;
						}
					}
				}

				/* check diagonal connection */
				foreach ($diagonal_dirs as [$dy, $dx]) {
					$ny = $board_y + $dy;
					$nx = $board_x + $dx;
					if ($ny >= 0 && $ny < $board_rows && $nx >= 0 && $nx < $board_cols) {
						if ($board[$ny][$nx] == $good_color) {
							$diagonal_connection = true;
						}
					}
				}
			}
		}
	}

	if (!$diagonal_connection) {
		return false;
	}

	return true;
}

function colorize($piece, $color) {
	global $blocks;
	$colored_block = [];
	foreach ($blocks[$piece] as $row) {
		$modified_row = [];
		foreach ($row as $value) {
			if ($value === 1) {
				$modified_row[] = $color;
			} else {
				$modified_row[] = $value;
			}
		}
		$colored_block[] = $modified_row;
	}

	return $colored_block;
}

function isBlocked($color, $board, $bitmask) {

	global $blocks;
	$piece_ids = [];
	for ($i = 0; $i < count($blocks); $i++) {
		if (($bitmask & (1 << $i)) !== 0) {
			$piece_ids[] = $i;
		}
	}

	for ($b = 0; $b<count($piece_ids); $b++) {
		$block = colorize($piece_ids[$b], $color);
		$r = 0;
		do {
			for ($i = 0; $i<20; $i++) {
				for ($j = 0; $j<20; $j++) {
					$canPaste = canPaste($board, $block, $i, $j, $color);
					if ($canPaste == true) {
						return false;
					}
				}
			}
			$block = rotateArray($block, 90);
			$r++;
		} while ($r < 4);
	}
	return true;
}

/* UNSAFE */
function getRoomsArbitrary($room_id, $nstr) {
	global $conn;

	/* WARNING!
         * The parameter $nstr MUST
         * ALWAYS be hardcoded.
         * Otherwise we risk SQLi
         */
	$sql = 'select ' . $nstr . ' from rooms where room_index=?';

	$st = $conn->prepare($sql);
	$st->bind_param('i', $room_id);
	$st->execute();
	$res= $st->get_result();
	$row = $res->fetch_assoc();

	if ($row && isset($row[$nstr])) {
		return $row[$nstr];
	} else {
		return -2;
	}
}

function updateActivity($room_id) {
	global $conn;

	$current_time = date('Y-m-d H:i:s');
	$timeout_limit = '180';

	/* update activity */
	$sql = 'call updateActivity(?,?)';
	$st = $conn->prepare($sql);
	$st->bind_param('si', $_SESSION['TOKEN'], $room_id);
	$st->execute();
	$st->close();

	$sql_check = 'SELECT checkTimeouts(?, ?) AS timed_out_players';
	$st_check = $conn->prepare($sql_check);
	$st_check->bind_param('ii', $room_id, $timeout_limit);
	$st_check->execute();
	$st_check->bind_result($timed_out_players);
	$st_check->fetch();
	$st_check->close();
	
	/* check activity */
	if (!empty($timed_out_players)) {
		$timed_out_array = explode(',', $timed_out_players);
		$messages = [];
		
		foreach ($timed_out_array as $player) {
			$player = ltrim($player);
			$messages[] = "$player has been kicked due to inactivity.";
		}
		
		echo json_encode($messages);
	} else {
		echo json_encode("none");
	}
}

function updateState($room_id, $n, $state): void {
	global $conn;
	$state = 1;
	$sql = 'call updatePlayerState(?,?,?)';
	$st = $conn->prepare($sql);
	$st->bind_param('sii', $n, $room_id, $state);
	$st->execute();
	$st->close();
}

function checkBlocks($room_id, $board) {

	if (getRoomsArbitrary($room_id, 'a_state') == 0) {
		$bitmask = getRoomsArbitrary($room_id, 'a_mask');
		$blocked = isBlocked(1, $board, $bitmask);
		if ($blocked == true) {
			$username = getRoomsArbitrary($room_id, 'player_a');
			echo $username . " is blocked";
			updateState($room_id, 1, 1);
			exit();
		}
	}

	if (getRoomsArbitrary($room_id, 'b_state') == 0) {
		$bitmask = getRoomsArbitrary($room_id, 'b_mask');
		$blocked = isBlocked(2, $board, $bitmask);
		if ($blocked == true) {
			$username = getRoomsArbitrary($room_id, 'player_b');
			echo $username . " is blocked";
			updateState($room_id, 2, 1);
		}
	}

	if (getRoomsArbitrary($room_id, 'c_state') == 0) {
		echo "got shad";
		$bitmask = getRoomsArbitrary($room_id, 'c_mask');
		$blocked = isBlocked(3, $board, $bitmask);
		if ($blocked == true) {
			$username = getRoomsArbitrary($room_id, 'player_c');
			echo $username . " is blocked";
			updateState($room_id, 3, 1);
		}
	}

	if (getRoomsArbitrary($room_id, 'd_state') == 0) {
		$bitmask = getRoomsArbitrary($room_id, 'd_mask');
		$blocked = isBlocked(4, $board, $bitmask);
		if ($blocked == true) {
			$username = getRoomsArbitrary($room_id, 'player_d');
			echo $username . " is blocked";
			updateState($room_id, 4, 1);
		}
	}
}

function isFirstPlacement($board, $color) {
	for ($y = 0; $y < 20; $y++) {
		for ($x = 0; $x < 20; $x++) {
			if ($board[$y][$x] == $color) {
				return false;
			}
		}
	}
	
	return true;
}

function canPasteFirst(&$board, $b9, $x, $y) {
	$b9_rows = count($b9);
	$b9_cols = count($b9[0]);
	$board_rows = count($board);
	$board_cols = count($board[0]);
	$diagonal_connection = false;

	if ($x < 0 || $y < 0 || $x + $b9_cols > $board_cols || $y + $b9_rows > $board_rows) {
		return false;
	}

	if ($board[$x][$y] != 0) {
		return false;
	}

	$on_corner = false;
	for ($i = 0; $i < $b9_rows; $i++) {
		for ($j = 0; $j < $b9_cols; $j++) {
			if ($b9[$i][$j] != 0) {
				$board_x = $x + $j;
				$board_y = $y + $i;
				if (($board_x == 0 and $board_y == 0) or
				    ($board_x == 0 and $board_y == 19) or
				    ($board_x == 19 and $board_y == 0) or
				    ($board_x == 19 and $board_y == 19)) {
					$on_corner = true;
				}
			}
		}
	}

	if (!$on_corner) {
		return false;
	}

	return true;
}


function placePiece($piece, $room_id, $x, $y, $rot) {
	global $conn;

	/* check turn */
	$sql = 'select getTurn(?)';
	$st = $conn->prepare($sql);
	$st->bind_param('s', $room_id);
	$st->execute();
	$res= $st->get_result();
	$row = $res->fetch_assoc();
	$turn = $row['getTurn(?)'];

	$sql = 'select username from users where session_id=?';
	$st = $conn->prepare($sql);
	$st->bind_param('s', $_SESSION['TOKEN']);
	$st->execute();
	$res= $st->get_result();
	$row = $res->fetch_assoc();

	if (!$row) {
		echo "You need to sign in.";
		exit();
	}

	$username = $row['username'];

	if ($username !== $turn) {
		echo "It's not your turn\n";
		exit();
	}

	/* check piece availability */
	$bitmask = getBitMask($room_id);

	if (!(isPieceAvailable($bitmask, $piece)) or ($piece < 0 or $piece > 20)) {
		echo "Piece $piece is not available.";
		exit();
	}

	/*** check if piece fits ***/

	/* get board */
	$sql = 'SELECT json_data FROM boards WHERE room_id = ?';
	$st = $conn->prepare($sql);
	$st->bind_param('s', $room_id);
	$st->execute();
	$res = $st->get_result();
	$r = $res->fetch_assoc();

	$board = json_decode($r['json_data'], true);

	header('Content-Type: application/json');

	/* get color */
	$sql = 'select getColor(?,?)';
	$st = $conn->prepare($sql);
	$st->bind_param('ss', $_SESSION['TOKEN'], $room_id);
	$st->execute();
	$res= $st->get_result();
	$r = $res->fetch_assoc();
	$color = $r['getColor(?,?)'];

	/* corolize */
	$colored_block = colorize($piece, $color);

	/* rotate */
	if (!($rot == 0 or $rot == 90 or $rot == 180 or $rot == 270)) {
		echo "Invalid rotation " . (int)$rot;
		exit();
	}

	$prepared_block = rotateArray($colored_block, $rot);

	/* paste */
	if (isFirstPlacement($board, $color) == true) {
		$canPaste = canPasteFirst($board, $prepared_block, $x, $y);
	} else {	
		$canPaste = canPaste($board, $prepared_block, $x, $y, $color);
	}

	if ($canPaste == false) {
		echo json_encode("Could not paste");
		exit();
	}

	paste($board, $prepared_block, $x, $y);

	$board_bu = $board;
	$board = json_encode($board);

	/* update board */
	$sql = 'update boards set json_data=? where room_id=?';
	$st = $conn->prepare($sql);
	$st->bind_param('si', $board, $room_id);
	$st->execute();
	$st->close();

	/* remove piece */
	removePiece($bitmask, $piece);
	$sql = 'call updateBitMask(?,?,?)';
	$st = $conn->prepare($sql);
	$st->bind_param('ssi', $_SESSION['TOKEN'], $room_id, $bitmask);
	$st->execute();
	$st->close();

	/* check states */
	if ($bitmask == 0) {
		echo $username . "has placed all pieces";
		updateState($room_id, $color, 2);
	}
	checkBlocks($room_id, $board_bu);

	$gameover = true;
	if ((getRoomsArbitrary($room_id, 'a_state') != 0) and
	    (getRoomsArbitrary($room_id, 'b_state') != 0) and
	    (getRoomsArbitrary($room_id, 'c_state') != 0) and
	    (getRoomsArbitrary($room_id, 'd_state') != 0)) {
		echo "Game over\n";
		$sql = 'update rooms set turn_index=-1 where room_index=?';
		$st = $conn->prepare($sql);
		$st->bind_param('i', $room_id);
		$st->execute();
		$st->close();
	}

	/* update turn index */
	$sql = 'call updateTurn(?,?)';
	$st = $conn->prepare($sql);
	$st->bind_param('ii', $color, $room_id);
	$st->execute();
	$st->close();
}

