<?php
class ship {
	var $coordinates = array();

	public function __construct() {

	}
}

class board {

	var $matrix = array();
	var $head_col = array();
	var $head_row = array();
	var $rows;
	var $cols;

	public function __construct() {
		$this->head_col = range('A', 'J');
		$this->head_row = range(1,9);
		$this->head_row[] = 0;
		$this->rows = 10;
		$this->cols = 10;
		$this->matrix = array_fill(1,$this->rows,array_fill(1,$this->cols,'.'));
	}

	public function outputHTML() {
		$grid = '<table>';
		$x = 0;
		while ($x <= $this->cols) {
			$y = 0;
			$grid .= ($x == 0) ? "<thead><th></th>":"<tr>";

			while ($y <= $this->rows) {
				if ($x == 0 && $y> 0) {
					$grid .= "<td>".$this->head_col[$y-1]."</td>";
				} elseif ($x > 0 && $y == 0) {
					$grid .= "<th>".$this->head_row[$x-1]."</th>";
				} elseif ($x > 0 && $y > 0) {
					$grid .= "<td>".$this->matrix[$x][$y]."</td>";
				}	
				$y++;
			}

			$grid .= ($x == 0) ? "</thead>":"</tr>";
			$x++;
		}
		$grid .= "</table>";
		return $grid;
	}

	public function enterCoords() {

	}

	public function updateStats() {

	}

	/**
	 *
	 */
	
	public function setupShips($types = array()) {
		$ships = array();
		foreach ($types as $ship_len) {
			$attempts = 0;
			$not_found = true;
			while ($not_found && $attempts < 100) {
				$attempts++;
				$is_horiz = (rand(0,1));
				$seed = ($is_horiz) ? array(rand(1, $cols - $ship_len), rand(1, $rows)) : array( rand(1, $cols), rand(1, $rows - $ship_len));	
				$ship = createShip($seed, $ship_len, $is_horiz);
				$overlaps = shipOverlaps($ship, $ships);
				if ($overlaps) {
					unset($ship);
				} else {
					$not_found = false;
					$ships[] = $ship;
				}
			}
		}
		return $ships;

	}

	public function getShips() {

	}

}

$rows = 10;
$cols = 10;

function createBoardHTML($grid_arr, $head_col, $head_row, $rows, $cols) {
$grid = '<table>';
$x = 0;
while ($x <= $cols) {
	$y = 0;
	$grid .= ($x == 0) ? "<thead><th></th>":"<tr>";

	while ($y <= $rows) {
		if ($x == 0 && $y> 0) {
			$grid .= "<td>".$head_col[$y-1]."</td>";
		} elseif ($x > 0 && $y == 0) {
			$grid .= "<th>".$head_row[$x-1]."</th>";
		} elseif ($x > 0 && $y > 0) {
			$grid .= "<td>".$grid_arr[$x][$y]."</td>";
		}	
		$y++;
	}

	$grid .= ($x == 0) ? "</thead>":"</tr>";
	$x++;
}
$grid .= "</table>";
return $grid;

}

function getLetterCol($reverse = false) {
	$col = range('A', 'J');
	return ($reverse) ? array_flip($col) : $col;

}

function createBoard($rows, $cols, $hits_coord = array(), $miss_coord = array(), $show_ships = false, $ships = array()) {
//$grid_arr = array_fill(1,10,array_fill(1,10,'.'));
//var_dump($x);exit;
$hits = array();
$miss = array();

//@TODO: refactor hits_coord so it doesn't need to be converted.
if ($show_ships) {
	foreach ($ships as $ship) {
		foreach ($ship as $s_coord) {
			$hits[$s_coord[0]][$s_coord[1]] = 'X';
		}
	}
	$miss = array();
} else {
	foreach ($hits_coord as $hit) {
		$hits[$hit[0]][$hit[1]] = 'X';	
	}
	foreach ($miss_coord as $m) {
		$miss[$m[0]][$m[1]] = '-';
	}
}

$head_row = range(1,9);
$head_row[] = 0;
$head_col = getLetterCol();
foreach ($head_col as $v => $k) {
	foreach ($head_row as $h => $p) {
		$x = $h+1;
		$y = $v+1;
		if (isset($hits[$x][$y])) {
			$grid_arr[$x][$y] = 'X';
		} elseif (isset($miss[$x][$y])) {
			$grid_arr[$x][$y] = '-';
		} elseif ($show_ships) {
			$grid_arr[$x][$y] = '';
		} else {
			$grid_arr[$x][$y] = '.';
		}
	}
}


return createBoardHTML($grid_arr, $head_col, $head_row, $rows, $cols);

}

function checkHit($coord, $ships) {
	return shipOverlaps(array($coord), $ships);
}

function checkComplete() {


}

function isValidCoord($coord) {
	// Is only 2 characters
	if (empty($coord)) return false;

	$len = (strlen($coord) == 2);
	if ($len) {
		$int = (ctype_digit($coord[1]));
		$alpha = (ctype_alpha($coord[0]));
		$show = (strtolower($coord) === 'show');
		$less_than_j = (strcasecmp($coord[0], 'K') < 0); 
		return (($len && $int && $alpha && $less_than_j) || $show);
	}
	return $len;
}

function convertCoord($val) {
	if ($val[1] == 0) return $val[0]."10";
	else return $val;

}

function shipOverlaps($ship, $ships) {
	foreach ($ships as $test_s) {
		foreach ($test_s as $test_coord) {
			foreach ($ship as $ship_coord) {
				if ($ship_coord[0] == $test_coord[0] && $ship_coord[1] == $test_coord[1]) {
					return true;
				}
			}
		
		}
	}
	return false;
}

function createShip($start, $len, $horiz) {
	$ship = array($start);
	$inc = 1;
	while ($inc < $len) {
		if ($horiz) {
			$ship[] = array($start[0] + $inc, $start[1]);
		} else {
			$ship[] = array($start[0], $start[1] + $inc);
		}
		$inc++;
	}
	
	return $ship;

}

function createShips($types, $rows, $cols) {
/**
Loop through ship lengths

pick an orientation
pick a seed point that is (width of board - len of ship)
grow the ship towards width/height of board
if overlaps with any other ship
repeat
else
continue


**/	
	$ships = array();
	foreach ($types as $ship_len) {
		$attempts = 0;
		$not_found = true;
		while ($not_found && $attempts < 100) {
			$attempts++;
			$is_horiz = (rand(0,1));
			$seed = ($is_horiz) ? array(rand(1, $cols - $ship_len), rand(1, $rows)) : array( rand(1, $cols), rand(1, $rows - $ship_len));	
			$ship = createShip($seed, $ship_len, $is_horiz);
			$overlaps = shipOverlaps($ship, $ships);
			if ($overlaps) {
				unset($ship);
			} else {
				$not_found = false;
				$ships[] = $ship;
			}
		}
	}
	return $ships;
}


if ($_POST) {
	// @TODO: clean get variable, and Validate coordinate
	$curr_board = json_decode($_COOKIE['harry_chow_battleship']);
	$hits = array();
	$ships = $curr_board->ships;
	$hits = $curr_board->hits;
	$miss = $curr_board->miss;
	$success_hits = $curr_board->success_hits;
	$attempts = $curr_board->attempts;
		$show_ships = false;

	if (!isset($_POST['coord']) || !isValidCoord($_POST['coord'])) {
		$status = 'Error: Please enter a valid coordinate';
	} else {
		$coord = strtoupper($_POST['coord']);
		if ($coord === 'SHOW') {
			$show_ships = true;
			$status = 'Cheat!';
		} elseif ($success_hits == 13) {
			// You've won, just display results
			$status = 'You won! It took you '.$attempts.' turns to win.  Refresh the page to start a new game.';
		} else {
			$coord = convertCoord($coord);
			$col_map = getLetterCol(true);
			$int_coord = (isset($coord[2])) ? 10 : $coord[1];
			$coord_num = array($int_coord, $col_map[$coord[0]] + 1);
			// Check if it overlaps a battleship
			if (checkHit($coord_num, $ships)) {
				// add to hits array
				// add 1 to attempts
				$hits[] = $coord_num;
				$success_hits++;
				$attempts++;
				$status = 'Hit';
				if (checkComplete()) {
					// Game has been won
				}
			} else {
				$miss[] = $coord_num;
				$attempts++;
				$status = 'Miss';
				// add to miss array
				// add 1 to attempts
			}
		}
}


	$grid = createBoard($rows,$cols, $hits, $miss, $show_ships, $ships);
	$setup = array(
		'ships' => $ships,
		'hits' => $hits,
		'miss' => $miss,
		'success_hits' => $success_hits,
		'attempts' => $attempts);
	setcookie("harry_chow_battleship", json_encode($setup));

} else { 
/**
	$ships = createShips(array(4,5,4), $rows, $cols);
	$grid = createBoard($rows, $cols);//, array(), array(), true, $ships);
	$setup = array(
		'ships' => $ships,
		'hits' => array(),
		'miss' => array(),
		'success_hits' => 0,
		'attempts' => 0);
	setcookie("harry_chow_battleship", json_encode($setup));
	$status = 'New Game';
**/
}

if ($_POST) {
// process the board with the given input

} else {
// create a new board
	$board = new board($rows, $cols);
	$board->setupShips(array(4,4,5));
	$setup = array(
		'ships' => $board->getShips(),
		'hits' => array(),
		'miss' => array(),
		'success_hits' => 0,
		'attempts' => 0);

	setcookie("harry_chow_battleship", json_encode($setup));

	$grid = $board->outputHTML();
	$status = "New Game";
}
?>
<html>
<body>
*** <?= $status ?> ***
<?php echo $grid; ?>
<form name="input" action="Battleships.php" method="POST">
Enter coordinates (row, col), e.g. A5 <input type="input" size="5" name="coord" autofocus>
<input type="submit">
</form>
</body>
</html>
