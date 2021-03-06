<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\Cookie;


/**
 * @ORM\Entity(repositoryClass="App\Repository\BoardRepository")
 */
class Board
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="array")
     */
    private $matrix = [];

    /**
     * @ORM\Column(type="array")
     */
    private $head_col = [];

    /**
     * @ORM\Column(type="array")
     */
    private $head_row = [];

    /**
     * @ORM\Column(type="array")
     */
    private $ships = [];

    /**
     * @ORM\Column(type="integer")
     */
    private $rows;

    /**
     * @ORM\Column(type="integer")
     */
    private $cols;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $hits = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $miss = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private $show_ships_only;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMatrix(): ?array
    {
        return $this->matrix;
    }

    public function setMatrix(array $matrix): self
    {
        $this->matrix = $matrix;

        return $this;
    }

    public function getHeadCol(): ?array
    {
        return $this->head_col;
    }

    public function setHeadCol(array $head_col): self
    {
        $this->head_col = $head_col;

        return $this;
    }

    public function getHeadRow(): ?array
    {
        return $this->head_row;
    }

    public function setHeadRow(array $head_row): self
    {
        $this->head_row = $head_row;

        return $this;
    }

    public function getShips(): ?array
    {
        return $this->ships;
    }

    public function setShips(array $ships): self
    {
        $this->ships = $ships;

        return $this;
    }

    public function getRows(): ?int
    {
        return $this->rows;
    }

    public function setRows(int $rows): self
    {
        $this->rows = $rows;

        return $this;
    }

    public function getCols(): ?int
    {
        return $this->cols;
    }

    public function setCols(int $cols): self
    {
        $this->cols = $cols;

        return $this;
    }

    public function getHits(): ?array
    {
        return $this->hits;
    }

    public function setHits(?array $hits): self
    {
        $this->hits = $hits;

        return $this;
    }

    public function getMiss(): ?array
    {
        return $this->miss;
    }

    public function setMiss(?array $miss): self
    {
        $this->miss = $miss;

        return $this;
    }

    public function getShowShipsOnly(): ?bool
    {
        return $this->show_ships_only;
    }

    public function setShowShipsOnly(bool $show_ships_only): self
    {
        $this->show_ships_only = $show_ships_only;

        return $this;
    }


    public function showShipsOnly()
    {
        return $this->show_ships_only;
    }

     /**
     * Board constructor.
     *
     *
     */
    public function __construct()
    {
        $this->head_col = range(1,9);
        $this->head_row = range('A', 'J');
        $this->head_col[] = 0;
        $this->rows = 10;
        $this->cols = 10;
        $this->hits = array();
        $this->miss = array();
        $this->show_ships_only = false;
        $this->matrix = array_fill(1,$this->rows,array_fill(1,$this->cols,'.'));
    }


    public function addMiss($miss)
    {
        $m = $this->miss;
        $key = $miss[0].$miss[1];
        $m[$key] = $miss;
        $this->miss = $m;
    }

    public function addHits($hits)
    {
        $this->hits[$hits[0].$hits[1]] = $hits;
    }

    public function getAttempts()
    {
        return count($this->getHits()) + count($this->getMiss());
    }

    public function getSuccessHits()
    {
        return count($this->getHits());
    }


    /**
     *
     * Initilaizer with a new board
     *
     * Static function to generate a new board with ships
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
     * Static function to generate board stored in browser cookie
     *
     * @param array $curr_board Array of values that represent the board
     *
     */
    public static function initWithSavedValues($curr_board = array())
    {
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
     * @param Response Response object to append cookie to
     *
     */
    public function save($response)
    {
        $setup = array(
            'ships' => $this->getShipsCoordinates(),
            'hits' => $this->getHits(),
            'miss' => $this->getMiss());

        $cookie = new Cookie('harry_chow_battleship', json_encode($setup));
        return $response->headers->setCookie($cookie);
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
     * Makes sure coordinates entered are
     *  - alphanumeric
     *  - longer than 0 length
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
        $col_map = array_flip($this->head_row);
        $val = array($col_map[strtoupper($coord[0])] + 1, (int)$int_coord);

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
