<?php
require_once('ship.php');
/**
 * Board Class
 *
 * Represents board of battleship
 * @TODO: Refactor and move static values into constants
 *
 */
class Board 
{
	private $matrix;
	private $head_col;
	private $head_row;
	private $ships;
	private $rows;
	private $cols;
    private $hits; 
    private $miss;
    private $show_ships_only;

    public function __construct() 
    {
		$this->head_col = range('A', 'J');
		$this->head_row = range(1,9);
		$this->head_row[] = 0;
		$this->rows = 10;
		$this->cols = 10;
		$this->hits = array();
		$this->miss = array();
        $this->show_ships_only = false;
		$this->matrix = array_fill(1,$this->rows,array_fill(1,$this->cols,'.'));
	}


    /**
     *
     * Getters/Setters
     *  
     */

    public function setShips($ships) 
    {
        $this->ships = $ships;
    }

    public function showShipsOnly() 
    {
        return $this->show_ships_only;
    }

    public function setShowShipsOnly($show) 
    {
        $this->show_ships_only = $show;
    }

    public function getShips() 
    {
        return $this->ships;
    }

    public function setMiss($miss) 
    {
        $this->miss = $miss;
    }

    public function addMiss($miss) 
    {
        $m = $this->miss;
        $key = $miss[0].$miss[1];
        $m[$key] = $miss;
        $this->miss = $m;
    }

    public function getMiss() 
    {
        return $this->miss;
    }

    public function setHits($hits) 
    {
        $this->hits = $hits;
    }

    public function addHits($hits) 
    {
        $this->hits[$hits[0].$hits[1]] = $hits;
    }

    public function getHits() 
    {
        return $this->hits;
    }

    public function getAttempts() 
    {
        return count($this->getHits()) + count($this->getMiss());
    }

    public function getSuccessHits() 
    {
        return count($this->getHits());
    }

    //***********


    /**
     *
     *  Initilaizer with a new board
     *
     */
    public static function initBlankBoard() 
    {
        $inst = new self();
        $ships = $inst->setupShips(array(4,4,5));
        $inst->setShips($ships);
        return $inst;
    }
    /**
     *
     *  Initializer with saved values
     *
     */
    public static function initWithSavedValues() 
    {
        $curr_board = json_decode($_COOKIE['harry_chow_battleship'], true);
        $ships = $curr_board['ships'];
        $hits = $curr_board['hits'];
        $miss = $curr_board['miss'];
        $show_ships = false;

        $inst = new self();
        $inst->setHits($hits);
        $inst->setMiss($miss);
        $ship_objs = array();
        foreach ($ships as $s_coord) {
            $ship_objs[] = Ship::initWithCoord($s_coord);
        }

        $inst->setShips($ship_objs);
        return $inst;
    }

    /**
     *
     * Save the state of the board to browser cookie
     *
     */
    public function save() 
    {
        $setup = array(
            'ships' => $this->getShipsCoordinates(),
            'hits' => $this->getHits(),
            'miss' => $this->getMiss());
        return setcookie("harry_chow_battleship", json_encode($setup));
    }

    /**
     *
     * Helper function to process each point on the grid, and return the 
     * correct display
     *
     * @param $coordinate array
     *
     */
    private function outputElem($coordinate) 
    {
        // Check if it's a ship
        // Check if it's a "show_ship_only"
        // Check if it's a miss
        // Otherwise, it's an unknown
        if ($this->showShipsOnly()) {
            foreach ($this->ships as $ship) {
                if ($ship->overlaps($coordinate)) {
                    return 'X';
                }
            }
            return '';
        } else {
            foreach ($this->getHits() as $hit_coord) {
                if ($hit_coord === $coordinate) {
                    return 'X';
                }
            }
            foreach ($this->getMiss() as $miss_coord) {
                if ($miss_coord === $coordinate) {
                    return '-';
                }
            }

            return '.';
        }
   }

    /**
     * Returns the HTML of the grid table
     *
     */
	public function outputHTML() 
    {

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
					$grid .= "<td>".$this->outputElem(array($x,$y))."</td>";
                    //echo "$x,$y<br>";
				}
				$y++;
			}

			$grid .= ($x == 0) ? "</thead>":"</tr>";
			$x++;
		}
		$grid .= "</table>";
		return $grid;
	}

    /**
     *
     * Helper function to check if the user inputed value is valid
     *
     * @param $coord array
     */
    private function isValidCoord($coord) 
    {
        // Is only 2 characters
        if (empty($coord)) return false;

        $show = (strtolower($coord) === 'show');
        $len = (strlen($coord) == 2);
        if ($len) {
            $int = (ctype_digit($coord[1]));
            $alpha = (ctype_alpha($coord[0]));
            $less_than_j = (strcasecmp($coord[0], 'K') < 0); 
        }
        return (($len && $int && $alpha && $less_than_j) || $show);
    }

    /**
     *
     * Helper function to convert the user coordinate, into grid format
     *
     * @param $coord array
     *
     */
    private function convertCoord($coord) 
    {
        $int_coord = ($coord[1] == 0) ? 10 : $coord[1];
        $col_map = array_flip($this->head_col);
        $val = array((int)$int_coord, $col_map[strtoupper($coord[0])] + 1);

        if ($val[1] == 0) {
            return $val[0]."10";
        } else {
            return $val;
        }
    }

    /**
     *
     * Helper function to check if a coordinate hits any ship on the board
     *
     * @param $coord array
     *
     */
    private function checkHit($coord) 
    {
        foreach ($this->getShips() as $ship) {
            $hit = $ship->overlaps($coord);
            if ($hit) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * Check if any ship is sunk by this hit
     */
    public function checkSunkShip($coord) 
    {
        foreach ($this->getShips() as $ship) {
            if ($ship->overlaps($coord)) {
                return $ship->isSunk($this->getHits());
            }
        }

        return false;
    }

    /**
     *  Processes the coordinate given by user.
     *
     *  @param $coord array
     *
     */
	public function enterCoords($coord) 
    {
        if (!$this->isValidCoord($coord)) {
            return  'Error: Please enter a valid coordinate';
        } else {
            if (strtolower($coord) === 'show') {
                $this->setShowShipsOnly(true);
                return 'Cheat!';
            } elseif ($this->getSuccessHits() == 13) {
                return 'You won! It took you '.$this->getAttempts().' turns to win.  Refresh the page to start a new game.';
            } else {
                $converted_coord = $this->convertCoord($coord);
                $hit = $this->checkHit($converted_coord);
                if ($hit) {
                    $this->addHits($converted_coord);
                    if ($this->getSuccessHits() == 13) {
                        return 'You won! It took you '.$this->getAttempts().' turns to win.  Refresh the page to start a new game.';
                    }

                    if ($this->checkSunkShip($converted_coord)) {
                        return 'You have sunk a ship';
                    } else {
                        return 'Hit';
                    }
                } else {
                    $this->addMiss($converted_coord);
                    return 'Miss!';
                }
            }
        }
	}

	/**
     * Create ships for board
     *
     * Generates ships of the length given by the types array.  Ships are placed randomly
     * on the board, and will not overlap
     * Will stop after attempting 100 times, in case the board can't fit any more ships
     *
     * @param $types array
     *
	 */
	public function setupShips($types = array()) 
    {
		$ships = array();
		foreach ($types as $ship_len) {
			$attempts = 0;
			$not_found = true;
			while ($not_found && $attempts < 100) {
				$attempts++;
                $ship = Ship::initNewShip($ship_len, $this->cols, $this->rows);
				$overlaps = $this->shipOverlaps($ship, $ships);
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

    /**
     * Checks if a ship overlaps with a list of other ships
     *
     * @param $ship array
     * @param $all_ships array
     *
     */
    public function shipOverlaps($ship, $all_ships) 
    {
        foreach ($all_ships as $test_s) {
            $overlaps = $test_s->overlapsWith($ship);
            if ($overlaps) {
                return true;
            }
        }

        return false;
    }

    /**
     *  Returns the list of ship's coordinates on the board
     *
     *  
     */
	public function getShipsCoordinates() 
    {
        $slist = array();
        foreach ($this->ships as $ship) {
            $slist[] = $ship->getCoordinates();
        }

        return $slist;
	}
}

?>
