<?php
Namespace FGFC;

use FGFC\enum\MessageType;
use FGFC\enum\StateType;

class Round
{
    private Game $game;
    private string $roundId;
    private int $roundNumber;
    private Deck $draw1;
    private Deck $draw2;
    private Deck $discard;
    private array $players;
    private bool $lastTurn;
    private Player $outPlayer;

    /**
     * @param Game $game
     */
    public function __construct(Game $game)
    {
        // Save game instance
        $this->game = $game;

        // Get players from game instance
        $this->players = $game->getPlayers();

        // Set round number
        $this->roundNumber = $game->getRoundNumber();

        // Give round new ID
        $this->roundId = \Ramsey\Uuid\Uuid::uuid4()->toString();

        $this->lastTurn = false;
    }

    // Getters
    public function getDiscardDeck(): Deck
    {
        return $this->discard;
    }

    /**
     * @return Deck
     */
    public function getDraw1(): Deck
    {
        return $this->draw1;
    }

    /**
     * @return Deck
     */
    public function getDraw2(): Deck
    {
        return $this->draw2;
    }

    public function getOutPlayer() : Player|bool
    {
        if (!isset($this->outPlayer)) {
            return false;
        }
        return $this->outPlayer;
    }
    public function setOutPlayer(Player $outPlayer) : void {
        $this->outPlayer = $outPlayer;
    }
    public function isLastTurn() : bool {
        return $this->lastTurn;
    }

    public function setLastTurn(bool $bool) : void {
        $this->lastTurn = $bool;
    }


    /**
     * Begins a new round of the game
     *
     * This method creates a big deck, shuffles it, and deals cards to the players.
     * It also initiates the discard pile, splits the remaining deck into two draw piles,
     * and starts the play.
     *
     * @return void
     */
    public function beginRound(): void
    {
        // Reset player for new round
        foreach ($this->players as $player){
            $player->setHand([]);
            $player->setRoundScore(0);
        }

        // Create big deck and shuffle;
        $bigDeck = new Deck();
        $bigDeck->build();
        $bigDeck->shuffle();

        // Deal cards
        for ($i = 0; $i < $this->roundNumber; $i++) {
            foreach ($this->players as $player) {
                $player->drawCard($bigDeck);
            }
        }

        // Initiate Discard Pile and draw first card;
        $this->discard = new Deck();
        $this->discard->addCard($bigDeck->drawCard());

        // Split remaining deck into two draw piles
        $splitDeck = $bigDeck->split();
        $this->draw1 = new Deck($splitDeck[0]);
        $this->draw2 = new Deck($splitDeck[1]);

        // Start play
        $this->send(true);
    }

    private function send(bool $init = false): void
    {
        foreach ($this->players as $player) {
            if (!$player instanceof CPUPlayer){
                if ($init){
                    $message = new Message(MessageType::STATE, new MessagePayload(StateType::INIT, $this->game->getGameState($player->getId())));
                }
                else {
                    $message = new Message(MessageType::STATE, new MessagePayload(StateType::UPDATE, $this->game->getGameState($player->getId())));
                }
                $message->send($player->getConn());
            }
        }
    }

    private function calculateScores(){
        foreach ($this->game->getPlayers() as $player){
            if ($player instanceof CPUPlayer){
                $player->checkRoundScore();
            }
        }
    }

}