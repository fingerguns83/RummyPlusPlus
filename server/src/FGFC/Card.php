<?php

/**
 * Class Card
 * Represents a playing card.
 */

namespace FGFC;
class Card
{
    private int $id;
    private string $suit;
    private int $value;
    private bool $joker = false;

    /**
     * Class constructor.
     *
     * @param int $id The id of the object.
     * @param string|null $suit The suit of the object.
     * @param int $value The value of the object.
     */
    function __construct(int $id, string|null $suit, int $value)
    {
        $this->id = $id;

        if ($suit == null) {
            $this->suit = "";
        } else {
            $this->suit = $suit;
        }

        $this->value = $value;
        if ($value == 0) {
            $this->joker = true;
        }
    }

    /**
     * Get the id of the object.
     *
     * @return int The id of the object.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getSuit(): string
    {
        return $this->suit;
    }

    /**
     * @param string $suit
     */
    public function setSuit(string $suit): void
    {
        $this->suit = $suit;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param int $value
     */
    public function setValue(int $value): void
    {
        $this->value = $value;
    }

    /**
     * Retrieves the card value and suit as a concatenated string.
     *
     * @return string|array The card value and suit.
     */
    public function getCard(bool $asArray): string|array
    {
        $output = match ($this->value) {
            0 => "JOKER",
            11 => "J",
            12 => "Q",
            13 => "K",
            default => $this->value,
        };
        if ($asArray) {
            return ['id' => $this->id, 'suit' => $this->suit, 'value' => $output];
        } else {
            return $output . $this->suit;
        }
    }

    /**
     * @param int $roundNumber
     * @return bool
     */
    public function isWildcard(int $roundNumber): bool
    {
        return $this->getValue() === $roundNumber;
    }

    /**
     * @return bool
     */
    public function isJoker(): bool
    {
        return $this->joker;
    }

    /**
     * @param bool $joker
     */
    public function setJoker(bool $joker): void
    {
        $this->joker = $joker;
    }

    public function getPoints(int $roundNumber) : int {
        if ($this->isJoker()){
            return 50;
        }
        elseif ($this->isWildcard($roundNumber)){
            return 20;
        }
        else {
            return $this->value;
        }
    }
}