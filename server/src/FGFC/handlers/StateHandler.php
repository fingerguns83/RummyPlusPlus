<?php

namespace FGFC\handlers;

use FGFC\enum\StateType;

class StateHandler extends MessageHandler
{
    public function handle() : void{
        switch ($this->msg['payload']['type']){
            case StateType::INIT->value:
                foreach($this->server->getClients() as $client){
                    if ($client->getConn() === $this->from) {
                        foreach ($client->getGame()->getPlayers() as $player){
                            if ($player->isCurrentPlayer()){
                                $player->takeTurn();
                            }
                        }
                    }
                }
                break;
            case StateType::UPDATE:
                break;
        }
    }
}