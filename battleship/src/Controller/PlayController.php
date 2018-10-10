<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class PlayController extends Controller
{
    /**
     * @Route("/play", name="play")
     */
    public function index()
    {
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



        $status = "";
        $board = "";
        return $this->render('play/play.html.twig', [
            'controller_name' => 'PlayController',
            'status' => $status,
            'board' => $board
        ]);
    }
}
