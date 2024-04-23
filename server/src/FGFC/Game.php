<?php

namespace FGFC;


use FGFC\enum\InfoType;
use FGFC\enum\MessageType;
use FGFC\enum\StateType;
use FGFC\helpers\DebugOutput;
use Ramsey\Uuid\Uuid;

class Game
{
    private string $id;
    private array $players;
    private int $roundNumber = 0;
    private Round|null $currentRound = null;

    public function __construct()
    {
        $this->players = [];
        $this->id = strtoupper(substr(hash('md5', Uuid::uuid4()->toString()), -5));
    }

    // Getters and Setters
    //============================================

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @param Player $player
     * @return Void
     */
    public function addPlayer(Player $player): void
    {
        $this->players[] = $player;
    }

    /**
     * Adds an array of players to the existing players array.
     *
     * @param array $players The array of players to be added.
     * @return void
     */
    public function addPlayers(array $players): void
    {
        $this->players = array_merge($this->players, $players);
    }

    /**
     * Retrieve the list of players.
     *
     * @return array The list of players.
     */
    public function getPlayers(): array
    {
        return $this->players;
    }

    /**
     * Retrieves the player with the specified ID.
     *
     * @param string $id The ID of the player
     *
     * @return Player|bool The player object if found, otherwise false
     */
    public function getPlayer(string $id): Player|bool
    {
        // Iterate over each player in the players array
        foreach ($this->players as $player) {
            // Check if the player's id matches the given id
            if ($player->getId() === $id) {
                // If a match is found, return the player object
                return $player;
            }
        }
        // If no player is found with the given id, return false
        return false;
    }

    /**
     * Get the round number.
     *
     * @return int The round number.
     */
    public function getRoundNumber(): int
    {
        return $this->roundNumber;
    }

    /**
     * @param int $roundNumber
     */
    public function setRoundNumber(int $roundNumber): void
    {
        $this->roundNumber = $roundNumber;
    }

    /**
     * @return Round
     */
    public function getCurrentRound(): Round
    {
        return $this->currentRound;
    }

    /**
     * @param Round $currentRound
     */
    public function setCurrentRound(Round $currentRound): void
    {
        $this->currentRound = $currentRound;
    }

    /**
     * Starts the game if there are more than one player
     * @return void
     */
    public function startGame(): void
    {
        // Output the start of the game
        DebugOutput::send("Starting game (" . $this->id . ")...");

        // Check if more than one player has joined the game
        if (count($this->players) > 1) {
            // Loop through each player in the game
            foreach ($this->players as $gamePlayer) {
                // Apply a function to all players
                array_map(function (Player $player) use (&$gamePlayer) {
                    // Check if the player is a CPU player, and set the content differently
                    if ($player instanceof CPUPlayer) {
                        $content = ['id' => $player->getId(), 'name' => $player->getName(), 'gender' => $player->getGender(), 'score' => $player->getScore()];
                    } else {
                        $content = ['id' => $player->getId(), 'name' => $player->getName()];
                    }
                    // Create a new message for the player
                    $message = new Message(MessageType::INFO, new MessagePayload(InfoType::PLAYER, $content));
                    // Send the message to the player
                    $message->send($gamePlayer->getConn());
                }, $this->getPlayers());
            }
        }
    }

    /**
     * Retrieves the dealer player from the players array
     * @return Player|bool The dealer player if found, otherwise false
     */
    /**
     * Method to get the current dealer from the players.
     */
    public function getDealer(): Player|bool
    {
        // loop over each player in the Game's players
        foreach ($this->players as $player) {
            // if the current player is the dealer
            if ($player->isDealer())
                return $player; // return the player who is the dealer
        }
        return false; // if no dealer is found, return false
    }

    /**
     * Retrieves the current player of the game
     * @return Player|bool The current player object if found, otherwise false
     */
    // A class method that retrieves the current player
    public function getCurrentPlayer(): Player|bool
    {
        // Loop over all the players
        foreach ($this->players as $player) {
            // If the player is the current player
            if ($player->isCurrentPlayer())
                // Return the player
                return $player;
        }
        // If no current player is found, return false
        return false;
    }

    /**
     * Selects the dealer for the game.
     * If there is no current dealer, the first player is assigned as the dealer.
     * If there is a current dealer, the next player in line becomes the dealer.
     * @return void
     */
    public function selectDealer(): void
    {
        $dealer = null; // Initialize dealer variable as null

        // If no dealer exists yet
        if (!$this->getDealer()) {
            // If player array isn't empty
            if (!empty($this->players)) {
                // Select the 4th player in array as dealer
                $this->players[count($this->players) - 1]->setDealer(true);
                $dealer = $this->players[count($this->players) - 1]; // Assign the selected dealer to dealer variable
            }
        }
        // If a dealer already exists
        else {
            // Retrieve the current dealer
            $dealer = $this->getDealer();
            // Find the location of current dealer in players array
            $dealerIndex = array_search($dealer, $this->players);
            // Calculate the index of the next dealer
            $nextDealerIndex = ($dealerIndex + 1) % count($this->players);

            // Set the current dealer's isDealer field to false
            $this->players[$dealerIndex]->setDealer(false);

            // Set the next dealer's isDealer field to true
            $this->players[$nextDealerIndex]->setDealer(true);

            // Update the dealer variable to the next dealer
            $dealer = $this->players[$nextDealerIndex];
        }

        // Send a message with dealer ID to each player
        foreach ($this->players as $player) {
            $message = new Message(MessageType::INFO, new MessagePayload(InfoType::DEALER, $dealer->getId()));
            $message->send($player->getConn());
        }
    }


    /**
     * Sets the first player in the game.
     *
     * This method checks if there is a dealer. If there is no dealer, it calls the `selectDealer` method
     * to determine who should be the dealer. It then calculates the index of the dealer in the players array.
     * Using modulo operator (%), it calculates the index of the next player in the array and assigns it
     * to the `$nextPlayerIndex` variable.
     *
     * If there is a current player, it sets their `isCurrentPlayer` field to `false`.
     * Finally, it sets the `isCurrentPlayer` field of the next player in the players array to `true`,
     * making them the current player for the next turn.
     *
     * Note: The current player is determined based on the clockwise order of players array.
     *
     * @return void
     */
    public function setFirstPlayer(): void
    {
        // Check if a dealer is set, if not select one
        if (!$this->getDealer()) {
            $this->selectDealer();
        }
        // Get the index of the dealer in the players array
        $dealerIndex = array_search($this->getDealer(), $this->players);

        // Calculate the index of the next player (it's a circular index, so if the dealer is the last player, the next player will be the first)
        $nextPlayerIndex = ($dealerIndex + 1) % count($this->players);

        // If there's a current player already set, remove their current player status
        if ($currentPlayer = $this->getCurrentPlayer()) {
            $currentPlayer->setCurrentPlayer(false);
        }

        // Set the next player as the current player
        $this->players[$nextPlayerIndex]->setCurrentPlayer(true);
    }

    public function getNextPlayer(): Player {
        // Initialize the current player index
        $currentIndex = 0;

        // Loop over all players
        foreach ($this->players as $index => $player) {

            // Check if the current player is the one whose turn is now
            if ($player->isCurrentPlayer()) {

                // Store the index of the current player
                $currentIndex = $index;
                break;
            }
        }

        // Get the index of the next player using modulo arithmetic to cycle back to the start of the array if we're at the end
        $nextIndex = ($currentIndex + 1) % count($this->players);

        // Get the next player's object
        return $this->players[$nextIndex];
    }

    /**
     * Moves to the next player in the game.
     *
     * This method finds the current player, sets their `isCurrentPlayer` property to `false`,
     * and then sets the `isCurrentPlayer` property of the next player in the players array to `true`.
     * If the current player is the last player in the array, it wraps around to the first player.
     *
     * @return void
     */
    public function nextPlayer(): void
    {
        // Get the next player's object
        $nextPlayer = $this->getNextPlayer();

        // Mark current player's turn as over
        $this->getCurrentPlayer()->setCurrentPlayer(false);

        // Mark next player's turn as started
        $nextPlayer->setCurrentPlayer(true);

        // Loop over all players
        foreach ($this->players as $player) {

            // Only update state for non-CPU players
            if (!$player instanceof CPUPlayer) {

                // Create a new update message with the current game state for the player
                $update = new Message(MessageType::STATE, new MessagePayload(StateType::UPDATE, $this->getGameState($player->getId())));

                // Send the update message to the player
                $update->send($player->getConn());
            }
        }
    }

    /**
     * Moves to the next round in the game.
     *
     * This method performs the following operations:
     * - Calls the `selectDealer` method to select the dealer for the next round.
     * - Calls the `setFirstPlayer` method to set the first player for the next round.
     * - If the `roundNumber` property is 0, it sets the `roundNumber` property to 3.
     *   Otherwise, it increments the `roundNumber` property by 1.
     * - Creates a new instance of the `Round` class, passing the current game as a parameter.
     * - Calls the `beginRound` method of the newly created `Round` instance to start the round.
     *
     * @return void
     */
    public function nextRound(): void
    {
        // Choose the dealer for the next round
        $this->selectDealer();

        // Set the first player for the next round
        $this->setFirstPlayer();

        // If it's the first round, set round number to 3,
        // Otherwise, increment the round number
        if ($this->roundNumber == 0) {
            $this->roundNumber = 3;
        } else {
            $this->roundNumber++;
        }

        // Initialize the current round
        $this->currentRound = new Round($this);

        // Start the current round
        $this->currentRound->beginRound();
    }

    /**
     * Retrieves the current state of the game.
     *
     * This method returns an array containing the round number, the top card of the discard pile,
     * and the state of each player in the game. The $clientPlayer parameter can be used to filter
     * the player state array, returning only the state of the specified player.
     *
     * @param string|null $clientPlayer The ID of the player to filter the player state array. If null,
     *                                 returns the state of all players.
     * @return array The current state of the game.
     */
    public function getGameState(string|null $clientPlayer = null): array
    {
        // Initialising game state array with roundNumber, discardPileTop, and currentPlayer
        $gameState = ['roundNumber' => $this->roundNumber]; // Fetching the round number

        if ($this->getCurrentPlayer()){
            $gameState['currentPlayer'] = $this->getCurrentPlayer()->getId(); // Getting the id of the current player
        }

        if ($this->currentRound !== null && $this->getCurrentRound()->getDiscardDeck() !== null){
            $gameState['discardPileTop'] = (count($this->currentRound->getDiscardDeck()->getCards()) === 0) ? null : $this->currentRound->getDiscardDeck()->getCards(true)[0]->getCard(true); // Checking if discard pile is empty, if not empty, then fetch the top card
        }
        // Looping through each player
        foreach ($this->players as $player) {
            $gameState['scores'][] = ['id' => $player->getId(), 'score' => $player->getScore()];
            // Checking if clientPlayer is null or not
            if ($clientPlayer == null) {
                // If clientPlayer is null, assigning player's state array to each individual player id in the game state 'players' array
                $gameState['players'][$player->getId()] = $player->getPlayerStateArray();
            } else {
                // If clientPlayer is not null, checking if the player's id matches the clientPlayer
                if ($player->getId() == $clientPlayer) {
                    // If player's id matches, assigning the state of this player to the 'player' key in game state array
                    $gameState['player'] = $player->getPlayerStateArray();
                }
            }
        }
        // Returning the game state array
        return $gameState;
    }

    /**
     * Checks if the given card set is a valid set or run.
     *
     * @param array $cardSet The set of cards to be checked
     *
     * @return bool True if the set is a valid set or run, otherwise false
     */
    public function checkSet(array $cardSet): bool
    {
        // If the number of cards in the set is less than 3 or more than 13, it is not a valid set. Return false.
        DebugOutput::send("Start checking the card set");
        if (count($cardSet) < 3 || count($cardSet) > 13) {
            DebugOutput::send("The number of cards in the set is not within the valid range. Returning false.");
            return false;
        }
        DebugOutput::send("The number of cards in the set is within the valid range");

        // Return true if the card set is a valid set or a valid run.
        // This checks the cards number or suit in sequence, respectively.
        // These are private methods in the Game class.
        // isValidSet: Checks if all cards in the set are of the same rank.
        DebugOutput::send("Checking if the card set is a valid set");
        $isValidSet = $this->isValidSet($cardSet);

        if($isValidSet) {
            DebugOutput::send("The card set is a valid set");
        }

        // isValidRun: Checks if all cards of the same suit are in a consecutive sequence.
        DebugOutput::send("Checking if the card set is a valid run");
        $isValidRun = $this->isValidRun($cardSet);

        if($isValidRun) {
            DebugOutput::send("The card set is a valid run");
        }

        return ($isValidSet || $isValidRun);
    }

    /**
     * Validates if the given set of cards is a valid set.
     *
     * @param array $cards The set of cards to be validated
     *
     * @return bool True if the set is valid, otherwise false
     */
    private function isValidSet(array $cards): bool
    {
        DebugOutput::send("isValidSet method called.");

        // Initialize an array to hold regular cards (those which are not Jokers or Wildcards)
        $regularCards = [];
        DebugOutput::send("Regular cards array initialized.");

        // Loop through each card in the cards array
        foreach ($cards as $card) {
            // If the card is not a Joker or Wildcard, append to the regularCards array
            if (!$card->isJoker() && !$card->isWildcard($this->roundNumber)) {
                $regularCards[] = $card;
                DebugOutput::send("Regular card added to array.");
            }
        }
        if (count($regularCards) == 0) {
            return true;
        }

        // Initialize an array to hold sets of cards with the same value
        $sets = [];
        DebugOutput::send("Sets array initialized.");

        // Loop through each regular card
        foreach ($regularCards as $card) {
            // If card value is not 0, append the card object to the set array at the index of its value
            if ($card->getValue() != 0) {
                $sets[$card->getValue()][] = $card;
                DebugOutput::send("Set added to array.");
            }
        }

        // Calculate the number of unique sets
        $setCount = count($sets);
        DebugOutput::send("Set count calculated: ".$setCount);

        // If there's more than one or no sets, return false
        if ($setCount !== 1) {
            DebugOutput::send("Set count not equal to 1. Returning false.");
            return false;
        }

        // If there exist exactly one which indicates its a valid set, return true
        DebugOutput::send("Valid set found. Returning true.");
        return true;
    }

    /**
     * Validates if the given set of cards is a valid run.
     *
     * @param array $cards The set of cards to be validated
     *
     * @return bool True if the run is valid, otherwise false
     */
    private function isValidRun(array $cards): bool
    {
        DebugOutput::send('isValidRun starts');

        $regularCards = []; // Initialize an array to hold regular cards
        $wildcardCards = []; // Initialize an array to hold wildcard cards

        DebugOutput::send('initializing cards arrays');

        foreach ($cards as $card) { // Loop through each card
            DebugOutput::send('processing card in main loop');

            // If the card is not a joker and not a wildcard for the current round
            if (!$card->isJoker() && !$card->isWildcard($this->roundNumber)) {
                DebugOutput::send('card is not a joker and not a wildcard');

                $regularCards[] = $card; // Add this card to the list of regular cards
            } else {
                DebugOutput::send('card is joker or wildcard');

                $wildcardCards[] = $card; // Add this card to the list of wildcard cards
            }
        }

        $runs = []; // Initialize an array to hold runs

        DebugOutput::send('initializing runs array');

        foreach ($regularCards as $card) { // Loop through each regular card
            DebugOutput::send('processing regular card');

            // If the card value is not 0, add it to the run for its suit
            if ($card->getValue() != 0) {
                DebugOutput::send('card value is not zero');

                $runs[$card->getSuit()][] = $card->getValue();
            }
        }

        // If there is more than one run, this is not a valid run
        if (count($runs) !== 1) {
            DebugOutput::send('more than one run, not a valid run');

            return false;
        }

        DebugOutput::send('looping through each run');

        // Loop through each run
        foreach ($runs as $suit => $values) {
            DebugOutput::send('processing run');

            // Sort the cards in the run by their values
            sort($values);

            DebugOutput::send('cards sorted by values');

            // Get the smallest value in this run
            $minValue = min($values);
            // Get the largest value in this run
            $maxValue = max($values);
            // Calculate the expected number of cards in the run
            $expectedCount = $maxValue - $minValue + 1;
            // Count the actual number of cards in the run
            $actualCount = count($values);
            // Calculate how many cards are missing from the run
            $missingCount = $expectedCount - $actualCount;

            DebugOutput::send('missing cards calculated');

            // If more cards are missing than there are wildcard cards, this is not a valid run
            if ($missingCount > count($wildcardCards)) {
                DebugOutput::send('not a valid run, more cards are missing than there are wildcard cards');

                return false;
            }
        }

        DebugOutput::send('valid run, method completed successfully');

        // If we get to here, this is a valid run
        return true;
    }

    /**
     * Calculates the score of a book.
     *
     * @param array $book The book of cards to calculate the score for
     *
     * @return int The score of the book
     */
    public function calculateBookScore(array $book): int {
        // Initialize the score to 0
        $score = 0;
        // Check if the provided set of cards does not form a valid set or a valid run
        if (!$this->isValidSet($book) && !$this->isValidRun($book)){
            // If the set of cards does not form a valid set or run, calculate the total score of the individual cards
            foreach ($book as $card){
                // Add the points of the individual card to the total score. The points of the card are calculated based on the round number
                $score += $card->getPoints($this->getRoundNumber());
            }
        }
        // Return the calculated score
        return $score;
    }

    /**
     * Calculates the score of a hand based on the given books.
     *
     * @param array $books The books of cards in the hand
     *
     * @return int The calculated score of the hand
     */
    public function calculateHandScore(array $books): int
    {
        // Initialize score to 0
        $score = 0;
        // Loop through each book in the provided array
        foreach ($books as $book){
            // Check if the book is neither a valid set nor a valid run
            if (!$this->isValidSet($book) && !$this->isValidRun($book)){
                // Loop through each card in the invalidated book
                foreach ($book as $card){
                    // Add the points of the current card to the score
                    $score += $card->getPoints($this->getRoundNumber());
                }
            }
        }
        // Return the accumulated score
        return $score;
    }

    public function calculateScore(Player $player, array|null $books, array|null $remainder) : int{
        $score = 0;

        if ($books !== null && count($books) > 0){
            foreach($books as $book){
                $tempBook = [];
                foreach($book as $card){
                    $tempBook[] = $player->getCard($card);
                }
                $score += $this->calculateBookScore($tempBook);
            }
        }
        if ($remainder !== null){
            foreach ($remainder as $card){
                $score += $player->getCard($card)->getPoints($this->getRoundNumber());
            }
        }

        return $score;
    }
}