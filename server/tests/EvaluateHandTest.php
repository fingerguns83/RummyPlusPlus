<?php

use FGFC\Card;
use FGFC\CPUPlayer;
use FGFC\Game;
use PHPUnit\Framework\TestCase;

class EvaluateHandTest extends TestCase
{
    private CPUPlayer $CPUPlayer;
    private array $testCases;
    
    protected function setUp(): void
    {
        $this->CPUPlayer = new CPUPlayer();
        $this->testCases = $this->setProvider();
    }


    #[\PHPUnit\Framework\Attribute\DataProvider('setProvider')]
    public function testEvaluateHand(): void
    {
        $game = new Game();

        foreach ($this->testCases as $name => $data) {
            echo $name . PHP_EOL;
            $game->setRoundNumber($data['size']);


            // test without groupings
            $this->CPUPlayer->setHand($data['hand']);


            // test with groupings
            $actualResultWithGroupings = $this->CPUPlayer->evaluateHand($game, $data[0], true);
            echo "REPORTED SCORE: " . $actualResultWithGroupings['score'] . PHP_EOL;
            $this->assertIsArray($actualResultWithGroupings);
            $this->assertArrayHasKey('hand', $actualResultWithGroupings);
            $this->assertArrayHasKey('score', $actualResultWithGroupings);
            $this->assertEquals($data['score'], $actualResultWithGroupings['score']);
        }
    }

    public function setProvider() : array {
        return [
        // Valid Runs
        //------------------------------------------------
            'Size 3, Valid Set' => [
                "size" => 3,
                "hand" => [
                    new Card(1, 'hearts', 10),
                    new Card(2, 'spades', 10),
                    new Card(3, 'diamonds', 10),
                ],
                "score" => 0
            ],
            'Size 3, Valid Set with Wild Card' => [
                "size" => 3,
                "hand" => [
                    new Card(1, 'hearts', 10),
                    new Card(2, 'spades', 3),
                    new Card(3, 'diamonds', 10),
                ],
                "score" => 0
            ],
            'Size 3, Valid Set with Joker' => [
                "size" => 3,
                "hand" => [
                    new Card(1, 'hearts', 10),
                    new Card(2, null, 0),
                    new Card(3, 'diamonds', 10),
                ],
                "score" => 0
            ],
            'Size 3, Valid Run' => [
                "size" => 3,
                "hand" => [
                    new Card(1, 'hearts', 9),
                    new Card(2, 'hearts', 10),
                    new Card(3, 'hearts', 11)
                ],
                "score" => 0
            ],
            'Size 3, Valid Run with Wild Card' => [
                "size" => 3,
                "hand" => [
                    new Card(1, 'hearts', 9),
                    new Card(2, 'spades', 3),
                    new Card(3, 'hearts', 11),
                ],
                "score" => 0
            ],
            'Size 3, Valid Run with Joker' => [
                "size" => 3,
                "hand" => [
                    new Card(1, 'hearts', 9),
                    new Card(2, null, 0),
                    new Card(3, 'hearts', 11),
                ],
                "score" => 0
            ],
            // Invalid Runs
            //------------------------------------------------
            'Size 3, Set off by one' => [
                "size" => 3,
                "hand" => [
                    new Card(1, 'hearts', 9),
                    new Card(2, 'spades', 9),
                    new Card(3, 'hearts', 11),
                ],
                "score" => 29
            ],
            'Size 3, Set off by one with wild' => [
                "size" => 3,
                "hand" => [
                    new Card(1, 'hearts', 9),
                    new Card(2, 'spades', 3),
                    new Card(4, 'diamonds', 11),
                ],
                "score" => 40
            ],
            'Size 3, Set off by one with joker' => [
                "size" => 3,
                "hand" => [
                    new Card(1, 'hearts', 9),
                    new Card(2, null, 0),
                    new Card(3, 'diamonds', 11),
                ],
                "score" => 70
            ],
            'Size 3, Set off by two' => [
                "size" => 3,
                "hand" => [
                    new Card(1, 'diamonds', 9),
                    new Card(2, 'spades', 5),
                    new Card(3, 'hearts', 11),
                ],
                "score" => 25
            ],
            'Size 4, Set off by two with wild' => [
                "size" => 4,
                "hand" => [
                    new Card(1, 'hearts', 8),
                    new Card(2, 'stars', 4),
                    new Card(3, 'hearts', 11),
                    new Card(4, 'diamonds', 9)
                ],
                "score" => 48
            ],
            'Size 4, Set off by two with joker' => [
                "size" => 4,
                "hand" => [
                    new Card(1, 'hearts', 8),
                    new Card(2, null, 0),
                    new Card(3, 'hearts', 11),
                    new Card(4, 'diamonds', 9)
                ],
                "score" => 78
            ],
            'Size 4, Run off by two' => [
                "size" => 4,
                "hand" => [
                    new Card(1, 'hearts', 9),
                    new Card(2, 'spades', 13),
                    new Card(3, 'hearts', 11),
                    new Card(4, 'diamonds', 5)
                ],
                "score" => 38
            ],
            "Dummy test" => [
                "size" => 3,
                "hand" => [
                    new Card(98, "stars", 12),
                    new Card(25, "hearts", 5),
                    new Card(34, "hearts", 6)
                ],
                "score" => 23
            ]
        ];
    }
}