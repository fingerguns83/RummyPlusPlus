<?php
Namespace FGFC;
use FGFC\Card;

class Deck
{
    private array $cards;
    private bool $subDeck = false;

    /**
     * @param array|null $cards
     */
    public function __construct(array|null $cards = null)
    {
        if ($cards != null) {
            $this->cards = $cards;
            $this->subDeck = true;
        }
    }


    /**
     * Builds the deck of cards
     * @return void
     */
    public function build(): void
    {
        $suits = ["clubs", "diamonds", "hearts", "spades", "stars"];
        $id = 0;
        for ($i = 3; $i <= 13; $i++) {
            foreach ($suits as $suit) {
                $this->cards[] = new Card($id, $suit, $i);
                $this->cards[] = new Card($id + 1, $suit, $i);
                $id += 2;
            }
        }
        for ($j = 0; $j < 6; $j++) {
            $this->cards[] = new Card($id, null, 0);
            $id++;
        }
    }

    /**
     * Add a card to the deck
     *
     * @param Card $card The card to be added
     * @return void
     */
    public function addCard(Card $card): void
    {
        $this->cards[] = $card;
    }

    /**
     * Split the deck in two and return an array of two card arrays
     *
     * @return array|bool Two card arrays or false if sub-deck
     */
    public function split(): array|bool
    {
        if ($this->subDeck) {
            return false;
        }
        $half = count($this->cards) / 2;
        $firstHalf = array_slice($this->cards, 0, $half);
        $secondHalf = array_slice($this->cards, $half);
        return [$firstHalf, $secondHalf];
    }

    /**
     * @param bool $reverse For discard pile, use reverse to get top card (last card added)
     * @return array
     */
    public function getCards(bool $reverse = false): array
    {
        if ($reverse) {
            return array_reverse($this->cards);
        }
        return $this->cards;
    }

    /**
     * Shuffle the deck between 5 and 10 times, randomly
     *
     * @return void
     */
    public function shuffle(): void
    {
        for ($i = 0; $i < rand(5, 10); $i++) {
            shuffle($this->cards);
        }
    }

    /**
     * @return int
     */
    public function getNumberOfCards(): int
    {
        return count($this->cards);
    }

    /**
     * Draws a card from the top of the deck.
     *
     * @return Card The card that was drawn from the deck.
     */
    public function drawCard(bool $discard = false): Card
    {
        if ($discard) {
            return array_pop($this->cards);
        }
        return array_shift($this->cards);
    }
}