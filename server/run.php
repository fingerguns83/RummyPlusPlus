<?php
require __DIR__ . '/vendor/autoload.php';

foreach (glob(__DIR__ . '/FGFC/*.php') as $filename) {
    include $filename;
}

use Ramsey\Uuid\Uuid;


class

$game = new Game();

$game->addPlayers([
    new Player(Uuid::uuid4()->toString(), "Player 1"),
    new Player(Uuid::uuid4()->toString(), "Player 2"),
    new Player(Uuid::uuid4()->toString(), "Player 3")
]);

$game->startGame();