<?php

namespace tests\FGFC;

use PHPUnit\Framework\TestCase;
use FGFC\Game;
use FGFC\Card;

class CheckSetTest extends TestCase
{
    /** @var Game */
    private Game $game;
    private array $testCases;

    protected function setUp(): void
    {
        $this->game = new Game();
        $this->game->setRoundNumber(11);
        $this->testCases = $this->setProvider();
    }

    #[\PHPUnit\Framework\Attribute\Group('test')]
    #[\PHPUnit\Framework\Attribute\DataProvider('setProvider')]
    public function testCheckSet()
    {
        foreach ($this->testCases as $name => $data) {
            echo $name . PHP_EOL;
            $result = $this->game->checkSet($data[0]);
            $this->assertEquals($data[1], $result);
        }
    }

    public function setProvider()
    {
        return [
            'valid, value set' => [
                [
                    new Card(1, 'hearts', 10),
                    new Card(1, 'spades', 10),
                    new Card(1, 'diamonds', 10),
                ],
                true
            ],
            'valid, run' => [
                [
                    new Card(1, 'hearts', 5),
                    new Card(1, 'hearts', 6),
                    new Card(1, 'hearts', 7),
                ],
                true
            ],

            'valid, value set with joker' => [
                [
                    new Card(1, 'hearts', 5),
                    new Card(1, null, 0),
                    new Card(1, 'spades', 5),
                ],
                true
            ],
            'valid, run with joker' => [
                [
                    new Card(1, 'hearts', 5),
                    new Card(1, null, 0),
                    new Card(1, 'hearts', 7),
                ],
                true
            ],
            'valid, value set with round wildcard' => [
                [
                    new Card(1, 'hearts', 5),
                    new Card(1, 'stars', 11),
                    new Card(1, 'clubs', 5)
                ],
                true
            ],
            'valid, run with round wildcard' => [
                [
                    new Card(1, 'hearts', 5),
                    new Card(1, 'spades', 11),
                    new Card(1, 'hearts', 7),
                ],
                true
            ],
            'valid, run with two round wildcards' => [
                [
                    new Card(1, 'hearts', 5),
                    new Card(1, 'diamonds', 11),
                    new Card(1, 'clubs', 11),
                    new Card(1, 'hearts', 7),
                ],
                true
            ],
            'valid, run with two jokers' => [
                [
                    new Card(1, 'hearts', 5),
                    new Card(1, null, 0),
                    new Card(1, null, 0),
                    new Card(1, 'hearts', 7),
                ],
                true
            ],
            'valid, run with joker and round wildcard' => [
                [
                    new Card(1, 'hearts', 5),
                    new Card(1, 'clubs', 11),
                    new Card(1, null, 0),
                    new Card(1, 'hearts', 7),
                ],
                true
            ],
            'valid, value set with two jokers' => [
                [
                    new Card(1, 'hearts', 5),
                    new Card(1, null, 0),
                    new Card(1, null, 0),
                    new Card(1, 'spades', 5),
                ],
                true
            ],
            'valid, value set with two round wildcards' => [
                [
                    new Card(1, 'hearts', 5),
                    new Card(1, 'clubs', 11),
                    new Card(1, 'diamonds', 11),
                    new Card(1, 'spades', 5),
                ],
                true
            ],
            'valid, value set with joker and round wildcard' => [
                [
                    new Card(1, 'hearts', 5),
                    new Card(1, null, 0),
                    new Card(1, 'diamonds', 11),
                    new Card(1, 'spades', 5),
                ],
                true
            ],
            'invalid, non-sequential values' => [
                [
                    new Card(1, 'hearts', 3),
                    new Card(1, 'hearts', 4),
                    new Card(1, 'hearts', 6),
                ],
                false
            ],
            'invalid, non-matching values' => [
                [
                    new Card(1, 'spades', 5),
                    new Card(1, 'diamonds', 3),
                    new Card(1, 'clubs', 3),
                ],
                false
            ],
            'invalid, non-sequential values with wildcard' => [
                [
                    new Card(1, 'hearts', 3),
                    new Card(1, 'hearts', 4),
                    new Card(1, 'hearts', 7),
                    new Card(1, 'hearts', 11),
                ],
                false
            ],
            'invalid, non-sequential values with joker' => [
                [
                    new Card(1, 'hearts', 3),
                    new Card(1, 'hearts', 4),
                    new Card(1, 'hearts', 7),
                    new Card(1, null, 0),
                ],
                false
            ],
            'invalid, non-matching values with wildcard' => [
                [
                    new Card(1, 'spades', 5),
                    new Card(1, 'diamonds', 11),
                    new Card(1, 'clubs', 3),
                ],
                false
            ],
            'invalid, non-matching values with joker' => [
                [
                    new Card(1, 'spades', 5),
                    new Card(1, null, 0),
                    new Card(1, 'clubs', 3),
                ],
                false
            ],
            'invalid 2 cards' => [
                [
                    new Card(1, 'hearts', 5),
                    new Card(1, 'hearts', 6),
                ],
                false
            ],
            'invalid 14 cards' => [
                [
                    new Card(1, 'hearts', 3),
                    new Card(1, 'hearts', 4),
                    new Card(1, 'hearts', 5),
                    new Card(1, 'hearts', 6),
                    new Card(1, 'hearts', 7),
                    new Card(1, 'hearts', 8),
                    new Card(1, 'hearts', 9),
                    new Card(1, 'hearts', 10),
                    new Card(1, 'hearts', 11),
                    new Card(1, 'hearts', 12),
                    new Card(1, 'hearts', 13),
                    new Card(1, null, 0),
                    new Card(1, null, 0),
                    new Card(1, null, 0)
                ],
                false
            ],
            'Dummy Test' => [
                [
                    new Card(50, "clubs", 8),
                    new Card(31, "clubs", 6),
                    new Card(90, "clubs", 12)
                ],
                false
            ]
        ];
    }
}