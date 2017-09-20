<?php

/**
 * Ship class
 *
 * Represnets a ship.  Used by board.
 *
 */
class Ship 
{
    private $coordinates;
    private $length;


    /**
     * Initializer for new ship
     *
     * Create new ship object with given params
     *
     * @param $length
     * @param $col
     * @param $row
     *
     * @return Ship
     */
    public static function initNewShip($length, $col, $row)
    {
        $inst = new self();
        $coord = $inst->generateCoords($length, $col, $row);
        $inst->setCoordinates($coord);
        $inst->setLength($length);
        return $inst;
    }


    /**
     * Initializer for new ships with coordinates
     *
     * Create new ship object at the given coordinates
     *
     * @param $coordinates
     * @return Ship
     */
    public static function initWithCoord($coordinates)
    {
        $inst = new self();
        $inst->setCoordinates($coordinates);
        $inst->setLength(count($coordinates));
        return $inst;
    }

    // Getters/Setters
    public function getLength() 
    {
        return $this->length;
    }
    public function setLength($len) 
    {
        $this->length = $len;
    }
    public function getCoordinates() 
    {
        return $this->coordinates;
    }
    public function setCoordinates($coord) 
    {
        $this->coordinates = $coord;
    }

    /**
     *
     *  Generate the coordinates for this ship
     *
     *
     *  Uses the given params to generate a randomized ship, contained within the 
     *  given column and row counts
     *
     *  @param int $len
     *  @param int $max_col
     *  @param int $max_row_
     *
     */
    public function generateCoords($len, $max_col, $max_row) 
    {
        $is_horiz = (rand(0,1));
        $start = ($is_horiz) ? array(rand(1, $max_col - $len), rand(1, $max_row)) : array( rand(1, $max_col), rand(1, $max_row - $len));    

        $ship = array($start);
        $inc = 1;

        while ($inc < $len) {
            if ($is_horiz) {
                $ship[] = array($start[0] + $inc, $start[1]);
            } else {
                $ship[] = array($start[0], $start[1] + $inc);
            }
            $inc++;
        }

        return $ship;
    }


    /**
     *
     * Checks if the given ship overlaps with this ship
     *
     * @param $other_ship array
     *
     */
    public function overlapsWith($other_ship) 
    {
        foreach ($other_ship->getCoordinates() as $ship_coord) {
            if ($this->overlaps($ship_coord)) {
                return true;
            }
        }

        return false;
    }

    /**
     *  Checks if the given coordinate overlaps this ship
     *
     * @param $coord array
     *
     */
    public function overlaps($coord) 
    {
        foreach ($this->getCoordinates() as $s_coord) {
            if ($coord === $s_coord) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if this ship has been sunk
     * 
     * @param $hits array
     */
    public function isSunk($hits) 
    {
        $total_hits = 0;
        foreach ($this->getCoordinates() as $s_coord) {
            foreach ($hits as $hit) {
                if ($hit === $s_coord) {
                    $total_hits++;
                }
            }
        }

        return ($total_hits == $this->getLength());
    }
}

?>
