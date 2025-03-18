<?php

namespace FGFC\handlers;

use FGFC\CPUPlayer;
use FGFC\Game;
use FiveCrownsSinglePlayerServer;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;

class MessageHandler
{
    public FiveCrownsSinglePlayerServer $server;
    public ConnectionInterface $from;
    public array $msg;
    public Game|null $game;
    public array $messages = [];
    public LoopInterface $eventLoop;
    public function __construct(FiveCrownsSinglePlayerServer $server, ConnectionInterface $from, array $msg, LoopInterface $eventLoop)
    {
        $this->server = $server;
        $this->from = $from;
        $this->msg = $msg;
        $this->eventLoop = $eventLoop;
        foreach($this->server->getClients() as $client) {
            if ($client->getConn() === $this->from) {
                $this->game = $client->getGame();
            }
        }
    }
    public function sendMessages() : void
    {
        if ($this->game === null){
            foreach($this->messages as $message){
                $message->send($this->from);
            }
        }
        foreach ($this->game->getPlayers() as $player) {
            if (!$player instanceof CPUPlayer){
                foreach ($this->messages as $message) {
                    $message->send($player->getConn());
                }
            }
        }
    }
}