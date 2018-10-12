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
        $form = $this->createFormBuilder()
            ->add('coord', TextType::class, array('required' => true, 'label' => 'Coordinates') )
            ->add('submit', SubmitType::class, array('label' => 'Submit'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // process the board with the given input
            // clean get variable, and Validate coordinate
            $curr_board = json_decode($request->cookies->get('harry_chow_battleship'), true);
            $board = Board::initWithSavedValues($curr_board);
            $form_data = $form->getData();
            $status = $board->enterCoords($form_data['coord']);
        } else {
            // create a new board
            $board = Board::initBlankBoard();
            $status = "New Game";
        }


        $response = $this->render('play/play.html.twig', [
            'controller_name' => 'PlayController',
            'status' => $status,
            'board' => $board->outputHTML(),
            'form' => $form->createView()
        ]);

        $board->save($response);

        return $response;
    }
}
