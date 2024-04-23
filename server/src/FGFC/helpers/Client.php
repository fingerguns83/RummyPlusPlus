<?php

namespace FGFC\helpers;

use FGFC\Game;
use FGFC\Player;
use Ramsey\Uuid\Uuid;
use Ratchet\ConnectionInterface;

class Client
{
    private ConnectionInterface $conn;
    private Player|null $player = null;
    private bool $singlePlayer = true;
    private Game $game;
    private string $gameId = "";

    /**
     * Constructor.
     *
     * @param ConnectionInterface $conn The connection interface.
     * @param string|null $playerName The name of the player. Can be null.
     * @param bool $singlePlayer Whether the game is single player or not.
     * @param Game $game The game object.
     * @param string|null $gameId The ID of the game. Can be null.
     */
    public function __construct(ConnectionInterface $conn, string|null $playerName, bool $singlePlayer, Game|null $game, string|null $gameId)
    {
        $this->conn = $conn;

        if ($playerName !== null) {
            $this->player = new Player(Uuid::uuid4()->toString(), $playerName);
        }

        $this->singlePlayer = $singlePlayer;

        if ($game !== null) {
            $this->game = $game;
            $this->gameId = $game->getId();
        }

        if ($gameId !== null) {
            $this->gameId = $gameId;
        }
    }

    /**
     * @return ConnectionInterface
     */
    public function getConn(): ConnectionInterface
    {
        return $this->conn;
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function setConn(ConnectionInterface $conn): void
    {
        $this->conn = $conn;
    }

    /**
     * @return Player|null
     */
    public function getPlayer(): Player|null
    {
        return $this->player;
    }

    /**
     * @param Player $player
     */
    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    /**
     * @return string
     */
    public function getGameId(): string
    {
        return $this->gameId;
    }

    /**
     * @param string $gameId
     */
    public function setGameId(string $gameId): void
    {
        $this->gameId = $gameId;
    }

    /**
     * @return Game
     */
    public function getGame(): Game
    {
        return $this->game;
    }
}