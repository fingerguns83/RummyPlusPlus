<?php

namespace FGFC\handlers;

use FGFC\CPUPlayer;
use FGFC\enum\InfoType;
use FGFC\enum\MessageType;
use FGFC\Game;
use FGFC\Message;
use FGFC\MessagePayload;
use FGFC\Player;
use Ramsey\Uuid\Uuid;

class InfoHandler extends MessageHandler
{
    public function handle() : void{
        switch ($this->msg['payload']['type']){
            case InfoType::REGISTRATION->value:
                foreach($this->server->getClients() as $client){
                    if ($client->getConn() === $this->from) {
                        $client->setPlayer(new Player(Uuid::uuid4()->toString(), $this->msg['payload']['data']['playerName'], $client->getConn()));

                        $gameId = strtoupper($this->msg['payload']['data']['gameId']);

                        $game = null;
                        foreach ($this->server->getGames() as $existingGame) {
                            if ($existingGame->getId() == $gameId) {
                                $game = $existingGame;
                                break;
                            }
                        }
                        if ($game === null) {
                            $game = new Game();
                        }
                        $this->server->getGames()->attach($game);
                        $client->setGame($game);
                        $game->addPlayer($client->getPlayer());
                        /*
                        $client->getGame()->addPlayer($client->getPlayer());
                        $client->getGame()->addPlayer(new CPUPlayer());
                        $client->getGame()->addPlayer(new CPUPlayer());
                        $client->getGame()->addPlayer(new CPUPlayer());
                        */
                        $msg = new Message(MessageType::INFO, new MessagePayload(InfoType::REGISTRATION, $client->getPlayer()->getId()));
                        $msg->send($client->getConn());
                    }
                }
                break;
        }
    }
}