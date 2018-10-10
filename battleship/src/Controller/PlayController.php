<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// Handles routing annotations
use Symfony\Component\Routing\Annotation\Route;

// Handles http requests
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Board;
use App\Entity\Ship;

// Form Types
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PlayController extends Controller
{
    /**
     *
     * @Route("/play", name="play")
     */
    public function index(Request $request)
    {
        // A blank ship to create the form with
        $ship = new Ship();
        $form = $this->createFormBuilder($ship)
            ->add('coordinates', TextType::class)
            ->add('submit', SubmitType::class, array('label' => 'Submit'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // process the board with the given input
            // clean get variable, and Validate coordinate
            $board = Board::initWithSavedValues();
            $status = $board->enterCoords($_POST['coord']);
        } else {
            // create a new board
            $board = Board::initBlankBoard();
            $status = "New Game";
        }

//        $board->save();

        return $this->render('play/play.html.twig', [
            'controller_name' => 'PlayController',
            'status' => $status,
            'board' => $board->outputHTML(),
            'form' => $form->createView()
        ]);
    }
}
