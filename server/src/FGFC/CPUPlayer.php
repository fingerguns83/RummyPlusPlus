<?php

namespace FGFC;

use Faker\Factory;
use FGFC\enum\ActionType;
use FGFC\enum\BookType;
use FGFC\enum\MessageType;
use FGFC\helpers\DebugOutput;
use Generator;
use PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor;

class CPUPlayer extends Player
{
    private int $calcCount = 0;
    private string $gender;
    private int $difficulty;

    private array|Card $handStrategy = [];
    private Card|null $tempCard = null;
    private array $possibleBookSizes;
    private int $currentHandQuality;

    public function __construct()
    {
        $this->difficulty = rand(1, 100);
        $this->initializePossibleBookSizes();

        $rand = rand(0, 1);
        if ($rand === 1) {
            $this->gender = 'male';
        } else {
            $this->gender = 'female';
        }
        $faker = Factory::create();
        parent::__construct($faker->uuid(), $faker->firstName($this->gender), null);
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    private function initializePossibleBookSizes(): void
    {
        $this->possibleBookSizes = [
            3 => [[3]],
            4 => [[4]],
            5 => [[5]],
            6 => [[3, 3]],
            7 => [[4, 3]],
            8 => [[5, 3], [4, 4]],
            9 => [[3, 3, 3], [5, 4]],
            10 => [[4, 3, 3], [5, 5]],
            11 => [[5, 3, 3], [4, 4, 3]],
            12 => [[3, 3, 3, 3], [4, 4, 4], [5, 4, 3]],
            13 => [[4, 3, 3, 3], [5, 4, 4], [5, 5, 3]]
        ];
    }

    /**
     * Evaluates a set of cards and returns the number of distinct sets of cards.
     *
     * @param array $cards The array of cards to evaluate.
     * @param Game $game The game object.
     * @return int The number of distinct sets of cards.
     */
    private function evaluateSet(array $cards, Game $game): int
    {
        $this->calcCount++;
        DebugOutput::send("Starting to evaluate set of cards");

        // Define an array for non-joker and non-wildcard cards
        $regularCards = [];
        DebugOutput::send("Defined an array for non-joker and non-wildcard cards");

        // Loop through given cards
        foreach ($cards as $card) {
            DebugOutput::send("Evaluating a card in the given cards array");
            // If card is not a joker and not a wildcard based on the round number, add to the regular cards array
            if (!$card->isJoker() && !$card->isWildcard($game->getRoundNumber())) {
                $regularCards[] = $card;
                DebugOutput::send("Added a card to the regular cards array");
            }
        }

        // Define an array for sets of cards with the same value
        $sets = [];
        DebugOutput::send("Defined an array for sets of cards with the same value");

        // Loop through regular cards
        foreach ($regularCards as $card) {
            DebugOutput::send("Evaluating a card in the regular cards array");
            // If the card's value is not 0 (ignoring empty card spots), then add the card to the sets array, using the card's value as the key in the array.
            // This effectively groups cards of the same value into their own array within the sets array.
            if ($card->getValue() != 0) {
                $sets[$card->getValue()][] = $card;
                DebugOutput::send("Added a card to the sets array");
            }
        }

        // The number of distinct sets of cards is the length of the sets array, but we subtract 1 because the array is 0-indexed.
        DebugOutput::send("Calculating the number of distinct sets of cards");
        if (count($sets) == 1){
            return 0;
        }
        else {
            $tempScore = 0;
            foreach($cards as $card){
                $tempScore += $card->getPoints($game->getRoundNumber());
            }
            return $tempScore;
        }
    }

    /**
     * Evaluates the provided array of cards and returns the number of cards left that cannot form a set.
     *
     * @param array $cards The array of cards to evaluate
     * @param Game $game The Game object representing the current game state
     * @return int The number of cards left that cannot form a set
     */
    private function evaluateRun(array $cards, Game $game): int
    {
        DebugOutput::send("Start evaluateRun method processing...");

        // Initialize empty arrays for regular and wildcard cards
        $regularCards = [];
        $wildcardCards = [];
        // Loop over the provided cards
        foreach ($cards as $card) {
            // If the card is not a Joker and not a wildcard (depending on the round number), add it to the regularCards array
            if (!$card->isJoker() && !$card->isWildcard($game->getRoundNumber())) {
                $regularCards[] = $card;
                // Otherwise, add into the wildcardCards array
            } else {
                $wildcardCards[] = $card;
            }
        }
        DebugOutput::send("Regular and wildcard cards separation completed...");

        // Initialize an empty array for card suits
        $suits = [];
        // Loop over the regularCards array
        foreach ($regularCards as $card) {
            // If card value is not 0, add it to the corresponding suit in the suits array
            if ($card->getValue() != 0) {
                $suits[$card->getSuit()][] = $card;
            }
        }
        DebugOutput::send("Regular cards sorted according to suits...");

        $score = PHP_INT_MAX;

        // Loop over the suits array
        foreach ($suits as $suit => $values) {
            // Sort values in ascending order based on their value
            usort($values, function ($a, $b) {
                return $a->getValue() - $b->getValue();
            });
            DebugOutput::send("Cards sorted in ascending order for suit: $suit...");

            // Map over the sorted values and extract their values
            $valuesMethodResults = array_map(function ($obj) {
                return $obj->getValue();
            }, $values);

            // Calculate the minimum and maximum value among the sorted values
            $minValue = min($valuesMethodResults);
            $maxValue = max($valuesMethodResults);
            DebugOutput::send("Min and Max card values calculated for suit ($suit)...");
            DebugOutput::send("Min: $minValue Max: $maxValue");

            // Compute the expected count of the sequence between minimum and maximum values
            $expectedRegularCount = $maxValue - $minValue + 1;
            DebugOutput::send("Expected count: $expectedRegularCount");

            // Get the actual count of values
            $actualRegularCount = count($values);
            DebugOutput::send("Actual count: $actualRegularCount");

            // Calculate the missing count by subtracting actual count from expected count
            $missingCount = $expectedRegularCount - $actualRegularCount;
            DebugOutput::send("Missing count: $missingCount");

            if ($missingCount > count($wildcardCards) || (count($values) + count($wildcardCards) < 3)) {
                $tempScore = 0;
                foreach ($cards as $card) {
                    $tempScore += $card->getPoints($game->getRoundNumber());
                }
                if ($tempScore < $score){
                    $score = $tempScore;
                }
            }
            else {
                $score = 0;
            }
        }
        return $score;
    }

    /**
     * Calculate combinations of elements in a given array.
     *
     * @param array $hand The array of elements
     * @param int $groupSize The size of the desired groups
     * @return Generator|array The generated combinations
     */
    private function calculateCombinations(array $hand, int $groupSize) : Generator|array
    {
        $n = count($hand); // count of elements in hand
        DebugOutput::send("Count of elements in hand: {$n}");

        if ($groupSize > $n) {
            DebugOutput::send("Group size {$groupSize} is greater than count of elements {$n}.");
            return; // if the group size is greater than number of elements, terminate
        }
        if ($groupSize == 0) {
            DebugOutput::send("Group size is 0. Return an empty array.");
            yield []; // in case no group is to be calculated, return an empty array
            return;
        }
        if ($groupSize == $n) {
            DebugOutput::send("Group size {$groupSize} matches the count of elements {$n}. Return initial array.");
            yield $hand; // in case the group size matches the count of elements, return the initial array
            return;
        }
        DebugOutput::send("Start generating all combinations for group size {$groupSize} from the rest of array.");

        // Generate all combinations for group size from the rest of the array.
        // array_slice($hand, 1): array after excluding the first element.
        // The subset here contains all combinations of the remaining elements of the group size
        foreach ($this->calculateCombinations(array_slice($hand, 1), $groupSize) as $subset) {
            yield $subset;
        }

        DebugOutput::send("Start generating all combinations for group size {$groupSize} - 1 and add the first element to each.");

        // Generate all combinations for group size one less than the current from the rest of the array,
        // and add the first element of the hand to each.
        // This gives combinations including the first element.
        foreach ($this->calculateCombinations(array_slice($hand, 1), $groupSize - 1) as $subset) {
            DebugOutput::send("Add the first element to the start of subset.");
            if (isset($hand[0])){
                array_unshift($subset, $hand[0]); // Add the first element to the start of the subset
            }

            yield $subset; // return the subset as a combination
        }
    }


    /**
     * Evaluates possible book combinations based on given values and group sizes.
     *
     * @param array $values The array of values to evaluate.
     * @param array $group_sizes The array of group sizes.
     *
     * @return Generator The generator yielding the possible combinations.
     */
    private function evaluateBook(array $values, array $group_sizes): Generator
    {

        rsort($group_sizes);
        DebugOutput::send("evaluateBook method execution started");

        // Loop over all possible combinations in the set of values.
        foreach ($this->calculateCombinations($values, $group_sizes[0]) as $combination1) {
            DebugOutput::send("Processing first level combinations");

            // If the size of the groups is less than 2, return combination1.
            if (count($group_sizes) < 2) {
                DebugOutput::send("Group sizes count is less than 2, yielding combination1");
                yield [$combination1];
            }

            // Removing elements in $combination1 from $values to find remaining elements.
            $tmpArr1 = array_udiff($values, $combination1, function ($a, $b) {
                // Custom comparison function to compare the IDs of the Card objects
                if ($a->getId() == $b->getId()) {
                    return 0;  // elements are equal
                }
                return $a->getId() < $b->getId() ? -1 : 1;  // elements are not equal
            });

            DebugOutput::send("Removed first level combination values from array");

            // Loop over all possible combos in the set of remaining values.
            foreach (isset($group_sizes[1]) ? $this->calculateCombinations($tmpArr1, $group_sizes[1]) : [] as $combination2) {
                DebugOutput::send("Processing second level combinations");

                // If the size of the groups is less than 3, return combinations.
                if (count($group_sizes) < 3) {
                    DebugOutput::send("Group sizes count is less than 3, yielding combination1 and combination2");
                    yield [$combination1, $combination2];
                }

                // Removing elements in $combination2 from $tmpArr1 to find remaining elements.
                $tmpArr2 = array_values(array_udiff($tmpArr1, $combination2, function ($a, $b) {
                    // Custom comparison function to compare the IDs of the Card objects
                    if ($a->getId() == $b->getId()) {
                        return 0;  // elements are equal
                    }
                    return $a->getId() < $b->getId() ? -1 : 1;  // elements are not equal
                }));

                DebugOutput::send("Removed second level combination values from array");

                // Loop over all possible combos in the set of remaining values.
                foreach (isset($group_sizes[2]) ? $this->calculateCombinations($tmpArr2, $group_sizes[2]) : [] as $combination3) {
                    DebugOutput::send("Processing third level combinations");
                    // Yield the possible combinations
                    yield [$combination1, $combination2, $combination3];
                }
            }
        }
        DebugOutput::send("evaluateBook method execution finished");
    }

    /**
     * Evaluates the CPU player's hand in a game
     *
     * @param Game $game The current game object
     * @param array|null $hand The hand to be evaluated. If null, uses the hand of the current CPU player
     * @param bool $getBooks Determines if the function should return both the hand and the score (true) or just the score (false)
     * @return int|array The score of the evaluated hand or an array containing the hand and the score, depending on the $getBooks flag
     */
    public function evaluateHand(Game $game, array|null $hand = null, bool $getBooks = false): int|array
    {
        DebugOutput::send('Start of method evaluateHand');
        // If a hand is given, it will be used, otherwise the hand of the current CPU player will be used
        if ($hand !== null) {
            $evalHand = $hand;
        } else {
            $evalHand = $this->getHand();
        }
        DebugOutput::send('Hand to be evaluated is set');

        // Initializes the score with a high number (max possible score)
        $score = PHP_INT_MAX;
        $tmpHand = array();

        DebugOutput::send('Start of checking all possible book sizes');
        // Checks all possible book sizes for the current round of the game
        foreach ($this->possibleBookSizes[$game->getRoundNumber()] as $bookSize) {
            DebugOutput::send('Evaluating for book size: ' . count($bookSize));
            // Evaluates the different groups of the hand for the given book size
            foreach ($this->evaluateBook($evalHand, $bookSize) as $combination) {
                $tmpScore = 0;
                DebugOutput::send('Start of evaluating each set in the combination');
                // For each set in the combination, both Set and Run are evaluated and the lowest score is added to the temporary score
                foreach ($combination as $cardSet) {
                    $setScore = $this->evaluateSet($cardSet, $game);
                    if ($setScore == 0){
                        $tmpScore += $setScore;
                    }
                    else {
                        $runScore = $this->evaluateRun($cardSet, $game);
                        DebugOutput::send('Calculated setScore: ' . $setScore . ', runScore: ' . $runScore);
                        ($runScore <= $setScore) ? $tmpScore += $runScore : $tmpScore += $setScore;
                    }
                }
                DebugOutput::send('End of evaluating each set. Temporary score: ' . $tmpScore);
                // If the temporary score is lower than the current score, it becomes the new score and the hand considered is stored
                if ($tmpScore <= 0){
                    $score = $tmpScore;
                    $tmpHand = $combination;
                    break;
                }
                else {
                    if ($tmpScore < $score) {
                        $score = $tmpScore;
                        $tmpHand = $combination;
                        DebugOutput::send('New lowest score found. Updated score: ' . $score);
                    }

                    if ($this->calcCount > 5000){
                        $this->calcCount = 0;
                        break;
                    }
                }
            }
        }
        DebugOutput::send('End of checking all possible book sizes');

        // If the getBooks flag is true, the function returns both the hand and the score. Otherwise, just the score is returned
        if ($getBooks) {
            DebugOutput::send('Returning both hand and score');
            return ['hand' => $tmpHand, 'score' => $score];
        } else {
            DebugOutput::send('Returning only score');
            return $score;
        }
    }

    /**
     * Evaluate the score of a new card in the current hand
     *
     * @param Card $card The new card to be evaluated
     * @param Game $game The current game being played
     * @return array An array containing the evaluated card and its score
     */
    private function evaluateNewCard(Card $card, Game $game): array
    {
        DebugOutput::send("Starting method: evaluateNewCard");
        // Initialize scores array for each card
        $cardScores = [];
        DebugOutput::send("Card scores array Initialized");
        // The new card to be evaluated
        $newCard = $card;
        DebugOutput::send("Set newCard to be evaluated");
        $currentHand = array_values($this->getHand());
        DebugOutput::send("Got the current hand. Cards in hand: " . count($currentHand));
        // Iterate over each card in the current hand
        foreach ($currentHand as $oldCard) {
            DebugOutput::send("Inspecting a card in the current hand");
            // Create a new hand replacing the old card with the new card
            $newHand = array_map(function ($card) use ($oldCard, $newCard) {
                DebugOutput::send("Creating a new hand by replacing old card with new card");
                // Replace the old card with the new card in the current hand
                return $card === $oldCard ? $newCard : $card;
            }, $this->getHand());
            DebugOutput::send("New hand created successfully");
            // Evaluate the score of the new hand
            $score = $this->evaluateHand($game, array_values($newHand));
            DebugOutput::send("Score of the new hand evaluated");
            // Add the old card and its corresponding score to the scores list
            $cardScores[] = [
                'oldCard' => $oldCard,
                'score' => $score
            ];
            DebugOutput::send("Added old card and corresponding score to the scores list");
        }
        // Sort the scores array based on the score in ascending order
        usort($cardScores, function ($a, $b) {
            DebugOutput::send("Sorting the card scores array");
            return $a['score'] - $b['score'];
        });
        DebugOutput::send("Sorted the card scores array based on the score in ascending order");
        // Get the first card and its score from the sorted cardScores array
        $firstEntry = array_shift($cardScores);
        DebugOutput::send("Got the first card and its score from the sorted card Scores array");
        // If the score is less than the current hand quality
        if ($firstEntry['score'] < $this->currentHandQuality) {
            DebugOutput::send("Score is less than the current hand quality, returning the card and its score");
            // Return the card and its score
            return ["card" => $firstEntry['oldCard'], "score" => $firstEntry['score']];
        } else {
            DebugOutput::send("Score is not less than the current hand quality, returning the new card and the current hand quality");
            // Return the new card and the current hand quality
            return ["card" => $card, "score" => $this->currentHandQuality];
        }
    }

    /**
     * Selects a weighted random element from an array based on the difficulty level.
     *
     * @param array $arr The array of elements to select from.
     *
     * @return mixed The selected element.
     */
    function selectWeightedRandom(array $arr) : mixed
    {
        if ($this->difficulty === 100) {
            return $arr[count($arr) - 1];
        }

        if ($this->difficulty === 1) {
            return $arr[0];
        }

        if ($this->difficulty === 50) {
            return $arr[array_rand($arr)];
        }

        $normalizedWeight = round(($this->difficulty - 1) / 99 * (count($arr) - 1));

        return $arr[$normalizedWeight];
    }


    /**
     * Executes a turn when the player must draw a card.
     *
     * @param Game $game The current game.
     */
    private function turnDraw(Game $game): void
    {
        DebugOutput::send("Starting turnDraw method");
        $output = trim(json_encode(json_encode($this->getPlayerStateArray())));
        $consoleColor = new ConsoleColor();
        echo $consoleColor->apply("color_82", $output . PHP_EOL);

        // Evaluating the quality of the current hand
        $this->currentHandQuality = $this->evaluateHand($game);
        DebugOutput::send("Hand quality evaluated");

        // Getting the top card from the discard deck
        $discardTop = $game->getCurrentRound()->getDiscardDeck()->getCards(true)[0];
        DebugOutput::send("Top card from the discard deck retrieved");

        // Evaluating the worst card
        $worstCard = $this->evaluateNewCard($discardTop, $game);
        DebugOutput::send("Worst card evaluated");

        // Check if the worst card isn't the top discarted card
        if ($worstCard['card'] !== $discardTop) {
            DebugOutput::send("Worst card is not the top discarded card");
            // Draw a card from discard deck
            $this->tempCard = $this->drawCard($game->getCurrentRound()->getDiscardDeck(), true, false);
            DebugOutput::send("Drew a card from discard deck");

            // Notify all non-CPU players about the draw
            foreach ($game->getPlayers() as $player) {
                if (!$player instanceof CPUPlayer) {
                    $draw = new Message(MessageType::ACTION, new MessagePayload(ActionType::DRAW3, $game->getGameState($player->getId())));
                    $draw->send($player->getConn());
                }
            }

        } else {
            DebugOutput::send("Worst card is the top discarded card");

            // Randomly choose which pile to draw a card from (0 for Draw 1 pile, 1 for Draw 2 pile)
            $pile = rand(0, 1);

            if ($pile) {
                DebugOutput::send("Chose Draw 1 pile randomly");

                // Draw a card from the Draw 1 pile
                $this->tempCard = $this->drawCard($game->getCurrentRound()->getDraw1(), false, false);
                DebugOutput::send("Drew a card from the Draw 1 pile");

                // Notify all non-CPU players about the draw from Pile 1
                foreach ($game->getPlayers() as $player) {
                    if (!$player instanceof CPUPlayer) {
                        $draw = new Message(MessageType::ACTION, new MessagePayload(ActionType::DRAW1, $game->getGameState($player->getId())));
                        $draw->send($player->getConn());
                    }
                }

            } else {
                DebugOutput::send("Chose Draw 2 pile randomly");

                // Draw a card from the Draw 2 pile
                $this->tempCard = $this->drawCard($game->getCurrentRound()->getDraw2(), false, false);
                DebugOutput::send("Drew a card from the Draw 2 pile");

                // Notify all non-CPU players about the draw from Pile 2
                foreach ($game->getPlayers() as $player) {
                    if (!$player instanceof CPUPlayer) {
                        $draw = new Message(MessageType::ACTION, new MessagePayload(ActionType::DRAW2, $game->getGameState($player->getId())));
                        $draw->send($player->getConn());
                    }
                }
            }
        }
        DebugOutput::send("Ending turnDraw method");
    }

    /**
     * Execute a turn to discard a card.
     *
     * @param Game $game The game object.
     * @return void
     */
    private function turnDiscard(Game $game): void
    {
        // Initialize payload.
        $message = null;

        DebugOutput::send("Starting evaluation of card");
        // Evaluate the new card and store the result in strategy.
        $strategy = $this->evaluateNewCard($this->tempCard, $game);

        // Remove the card specified in the strategy from the hand.
        if ($strategy['card'] !== $this->tempCard){
            DebugOutput::send("Adding temporary card to hand");
            // Add the temporary card to the hand.
            $this->addCardToHand($this->tempCard);
            $this->removeCardFromHand($strategy['card']);
        }

        // If score of evaluated strategy is less than or equal to 0, go out.
        if ($strategy['score'] <= 0) {
            DebugOutput::send("Preparing to go out with a score less than or equal to 0");
            if ($this->goOut($game, $this->evaluateHand($game, null, true)['hand'], $strategy['card'], false)){
                // Prepare "OUT" message
                $message = new Message(MessageType::ACTION, new MessagePayload(ActionType::OUT, $this->getId()));
            }
            else {
                $this->discard($strategy['card'], $game->getCurrentRound()->getDiscardDeck(), false);
            }
        }
        else {
            // Check if it is the last round in the game.
            if ($game->getCurrentRound()->isLastTurn()) {
                DebugOutput::send("Last turn of the game. Preparing to go out or discard a card");
                $books = $this->evaluateHand($game, null, true)['hand'];
                // If the player cannot go out, calculate score and discard a card.
                if (!$this->goOut($game, $books, $strategy['card'], false)) {
                    $this->setRoundScore($game->calculateHandScore($books));
                    $this->discard($strategy['card'], $game->getCurrentRound()->getDiscardDeck(), false);
                    $message = new Message(MessageType::ACTION, new MessagePayload(ActionType::LAY, $this->getId()));
                }
                else {
                    $this->discard($strategy['card'], $game->getCurrentRound()->getDiscardDeck(), false);
                    $message = new Message(MessageType::ACTION, new MessagePayload(ActionType::OUT, $this->getId()));
                }
            }
            // If not the last round, discard the card and set payload to 'DISCARD' for non-CPU players.
            else {
                DebugOutput::send("Not the last round. Discard card");
                $this->discard($strategy['card'], $game->getCurrentRound()->getDiscardDeck(), false);
            }
        }

        DebugOutput::send("Sending a message to all non-CPU players");
        // Send a message to all non-CPU players.
        foreach ($game->getPlayers() as $player) {
            if (!$player instanceof CPUPlayer) {
                $message?->send($player->getConn());
                $discard = new Message(MessageType::ACTION, new MessagePayload(ActionType::DISCARD, $game->getGameState($player->getId())));
                $discard->send($player->getConn());
            }
        }

        DebugOutput::send("Resetting temporary card");
        // Reset temporary card to null.
        $this->tempCard = null;
    }

    /**
     * Takes a turn in the game.
     *
     * If the temporary card is null, it draws a card from the game.
     * Otherwise, it discards the temporary card.
     *
     * @param Game $game The game instance.
     *
     * @return void
     */
    public function takeTurn(Game $game): void
    {
        if ($this->tempCard == null) {
            $this->turnDraw($game);
        } else {
            $this->turnDiscard($game);
        }
        $this->calcCount = 0;
    }

    public function createInitialStrategy(): void {
        $bySuit = [];
        $byValue = [];
        foreach($this->getHand() as $card){
            if ($card->isJoker()){
                $bySuit['joker'] = $card;
            }
            else {
                $bySuit[$card->getSuit()] = $card;
            }
            $byValue[$card->getValue()] = $card;
        }

        foreach ($byValue as $key => $cards){
            if (count($cards) >= 3){
                $this->handStrategy[] = new SetBook($cards, $key);
            }
        }
    }
    public function strategize(Game $game): void{
        if (count($this->handStrategy) == 0){
            $this->createInitialStrategy();
            return;
        }
    }

}