<?php

/**
 *
 */

namespace FGFC;

use /**
 * Represents a card.
 *
 * @package FGFC\Card
 */
    FGFC\Card;
use FGFC\helpers\DebugOutput;
use /**
 *
 */
    Ratchet\ConnectionInterface;

/**
 *
 */
class Player
{
    private string $id;
    private string $name;
    private bool $dealer;
    private bool $currentPlayer;
    private int $score;
    private int $roundScore = 0;
    private array|Card $hand = [];
    private ConnectionInterface|null $conn;


    /**
     * Create a new instance of the player class.
     *
     * @param string $id The identifier of the player.
     * @param string $name The name of the player.
     * @param ConnectionInterface|null $connection The connection interface for the player, if available.
     */
    public function __construct(string $id, string $name, ConnectionInterface|null $connection = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->dealer = false;
        $this->currentPlayer = false;
        $this->score = 0;
        $this->conn = $connection;
    }

    // Getters and Setters
    //============================================
    /**
     * Gets the ID of the object.
     *
     * @return string The ID of the object.
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
     * Get the name of the player.
     *
     * @return string The name of the player.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Check if the player is the dealer.
     *
     * @return bool Returns true if the player is the dealer, false otherwise.
     */
    public function isDealer(): bool
    {
        return $this->dealer;
    }

    /**
     * Set the value of dealer.
     *
     * @param bool $dealer The value to set for dealer.
     * @return void
     */
    public function setDealer(bool $dealer): void
    {
        $this->dealer = $dealer;
    }

    /**
     * Check if the player is the current player.
     *
     * @return bool Returns true if the player is the current player, false otherwise.
     */
    public function isCurrentPlayer(): bool
    {
        return $this->currentPlayer;
    }

    /**
     * @param bool $currentPlayer
     */
    public function setCurrentPlayer(bool $currentPlayer): void
    {
        $this->currentPlayer = $currentPlayer;
    }

    /**
     * Get the current score.
     *
     * @return int The current score.
     */
    public function getScore(): int
    {
        return $this->score;
    }

    /**
     * Set the score for the player.
     *
     * @param int $score The score to set.
     * @return void
     */
    public function setScore(int $score): void
    {
        $this->score = $score;
    }
    public function increaseScore(int $score): void
    {
        $this->score += $score;
    }

    /**
     * Get the current round score.
     *
     * @return int The round score.
     */
    public function getRoundScore(): int
    {
        return $this->roundScore;
    }

    /**
     * Set the round score for the player.
     *
     * @param int $roundScore The round score to set.
     * @return void
     */
    public function setRoundScore(int $roundScore): void
    {
        $this->roundScore = $roundScore;
    }

    /**
     * Retrieves the hand of cards.
     *
     * @return array The array containing all the cards in the hand.
     */
    public function getHand(): array
    {
        return $this->hand;
    }

    /**
     * Set the hand of cards.
     *
     * @param array $hand An array of Card objects representing the hand of cards.
     * @return void
     */
    public function setHand(array $hand): void
    {
        $this->hand = $hand;
    }

    /**
     * Get a card from the hand based on its id.
     *
     * @param int $cardId The id of the card to get.
     * @return Card|bool Returns the card if found, false otherwise.
     */
    public function getCard(int $cardId): Card|bool
    {
        foreach ($this->getHand() as $card) {
            if ($card->getId() === $cardId) {
                return $card;
            }
        }
        return false;
    }

    /**
     * @return ConnectionInterface|null
     */
    public function getConn(): ConnectionInterface|null
    {
        return $this->conn;
    }

    /**
     * Set the connection for the object.
     *
     * @param ConnectionInterface|null $conn The connection to set.
     * @return void
     */
    public function setConn(ConnectionInterface|null $conn): void
    {
        $this->conn = $conn;
    }

    // Actions
    //============================================

    /**
     * Draw a card from the deck and add it to the player's hand.
     *
     * @param Deck $deck The deck from which to draw the card.
     * @param bool $fromDiscard
     * @param bool $addToHand
     * @return void
     */
    public function drawCard(Deck $deck, bool $fromDiscard = false, bool $addToHand = true): Card
    {
        $newCard = $deck->drawCard($fromDiscard);
        if ($addToHand) {
            $this->hand[] = $newCard;
        }
        return $newCard;
    }

    /**
     * Add a card to the player's hand.
     *
     * @param Card $card The card to add to the hand.
     * @return void
     */
    public function addCardToHand(Card $card): void
    {
        $this->hand[] = $card;
    }

    /**
     * Remove a card from the player's hand.
     *
     * @param Card $targetCard The card to be removed from the hand.
     * @return void
     */
    public function removeCardFromHand(Card $targetCard): void {
        foreach ($this->hand as $key => $card) {
            if ($card === $targetCard) {
                unset($this->hand[$key]);
                break;
            }
        }
        $this->hand = array_values($this->hand);
    }

    /**
     * Discard a card from the hand based on its id.
     *
     * @param int $targetCard The id of the card to discard.
     * @param Deck $discardDeck
     * @param bool $fromHand
     * @return Card
     */
    public function discard(Card $targetCard, Deck $discardDeck, bool $fromHand): void
    {
        if ($fromHand) {
            foreach ($this->hand as $key => $card) {
                if ($card === $targetCard) {
                    $discardDeck->addCard($card);
                    unset($this->hand[$key]);
                    break;
                }
            }
            $this->hand = array_values($this->hand);
        }
        else {
            $discardDeck->addCard($targetCard);
        }
    }

    /**
     * Retrieves the raw hand data.
     *
     * This method returns an array containing the raw card data for each card in the hand.
     *
     * @return array The raw hand data.
     */
    public function getHandRaw(): array
    {
        $output = [];
        array_map(function (Card $card) use (&$output) {
            $output[] = $card->getCard(true);
        }, $this->getHand());
        return $output;
    }

    /**
     * Displays the cards in the hand.
     *
     * @return array The array containing the cards in the hand.
     */
    public function displayHand(): array
    {
        $output = [];
        foreach ($this->hand as $card) {
            $output[] = $card->getCard(true);
        }
        return $output;
    }

    /**
     * Returns an array representing the state of the player.
     *
     * The returned array will contain the following keys:
     * - id: The player's ID.
     * - name: The player's name.
     * - score: The player's score.
     * - is_dealer: An integer indicating whether the player is the dealer (1) or not (0).
     * - is_current_player: An integer indicating whether the player is the current player (1) or not (0).
     * - hand: The raw representation of the player's hand.
     *
     * @return array The array representing the state of the player.
     */
    public function getPlayerStateArray(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "score" => $this->score,
            "roundScore" => $this->roundScore,
            "is_dealer" => intval($this->dealer),
            "is_current_player" => intval($this->currentPlayer),
            "hand" => $this->getHandRaw()
        ];
    }

    /**
     * Go out in the game by discarding a card and forming books.
     *
     * @param Game $game The game in which the player is going out.
     * @param array $books An array of books, where each book is an array of integers or Card objects.
     * @param int|Card $discard The card to discard, specified either by its index in the player's hand or as a Card object.
     * @param bool $fromHand Indicates whether the discarded card comes from the player's hand.
     * @return bool Returns true if the player successfully goes out, or false if the books are invalid.
     */
    public function goOut(Game $game, array $books, int|Card $discard, bool $fromHand = true): bool
    {
        DebugOutput::send("Entering goOut method.");

        if (!$discard instanceof Card){
            DebugOutput::send("Getting Card instance for discard.");
            $discard = $this->getCard($discard);
        }

        $valid = true;

        foreach ($books as $book) {
            DebugOutput::send("Processing a book in books array");
            $tempBook = [];

            array_map(function (int|Card $bookItem) use (&$tempBook) {
                DebugOutput::send("Processing a book item.");

                if (!$bookItem instanceof Card){
                    DebugOutput::send("Getting Card instance for book item");
                    $tempBook[] = $this->getCard($bookItem);
                }
                else {
                    $tempBook[] = $bookItem;
                }
            }, $book);

            if (!$game->checkSet($tempBook)) {
                DebugOutput::send("Check Set Failed for a booklet.");
                $valid = false;
                break;
            }
        }

        if (!$valid) {
            DebugOutput::send("Validation failed during book checks. Returning from goOut method with false.");
            return false;
        }

        DebugOutput::send("Making last turn as true for the current round");
        $game->getCurrentRound()->setLastTurn(true);

        if (!$game->getCurrentRound()->getOutPlayer()){
            DebugOutput::send("Setting out player for the current round.");
            $game->getCurrentRound()->setOutPlayer($game->getPlayer($this->id));
        }

        DebugOutput::send("Discarding a card...");
        $this->discard($discard, $game->getCurrentRound()->getDiscardDeck(), $fromHand);

        DebugOutput::send("Successfully processed goOut method with true.");
        return true;
    }

}