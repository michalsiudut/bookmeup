<?php

require_once  'AppController.php';
require_once __DIR__.'/../repository/UserRepository.php';
require_once __DIR__.'/../repository/CardsRepository.php';

class DashboardController extends AppController{

    private $cardsRepository;

    public function __construct(){
        $this->cardsRepository = new CardsRepository();
    }

    public function dashboard(?int $id = null){

        if($id != null){
            $cards= [
                [
                'id' => 1,
                'title' => 'Ace of Spades',
                'subtitle' => 'Legendary card',
                'imageUrlPath' => 'https://deckofcardsapi.com/static/img/AS.png',
                'href' => '/cards/ace-of-spades'
                ],
            ];
        }
        $userRepository = new UserRepository();
        $users = $userRepository->getUsers();
        var_dump($users);

        return $this->render("dashboard", [
            "cards" => $cards
        ]);
    }

    public function search(){

        header('Content-Type: application/json');
        http_response_code(200);

        $cards = $this->cardsRepository->getCardsByTitle('heart');
        echo json_encode($cards);
        return;
    }
}