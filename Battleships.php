<?php

require_once('board.php');
if ($_POST) {
    // process the board with the given input
	// clean get variable, and Validate coordinate
	$board = Board::initWithSavedValues();
    $status = $board->enterCoords($_POST['coord']);
} else {
    // create a new board
	$board = Board::initBlankBoard();
	$status = "New Game";
}
$board->save();

?>
<html>
    <body>
    *** <?= $status; ?> ***
    <?php echo $board->outputHTML(); ?>
        <form name="input" action="Battleships.php" method="POST">
            Enter coordinates (row, col), e.g. A5 <input type="input" size="5" name="coord" autofocus>
            <input type="submit">
        </form>
    </body>
</html>
