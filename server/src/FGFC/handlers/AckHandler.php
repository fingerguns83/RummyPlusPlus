<?php

namespace FGFC\handlers;

use FGFC\CPUPlayer;
use FGFC\enum\AckType;
use FGFC\enum\MessageType;
use FGFC\enum\StateType;
use FGFC\helpers\DebugOutput;
use FGFC\Message;
use FGFC\MessagePayload;

class AckHandler extends MessageHandler
{
    public function handle() : void {
        switch ($this->msg['payload']['type']){
            case AckType::REGISTRATION->value:
                $this->game->startGame();
                $this->game->nextRound();
                break;
            case AckType::INIT->value:
                foreach ($this->game->getPlayers() as $player){
                    if (!$player instanceof CPUPlayer){
                        $message = new Message(MessageType::STATE, new MessagePayload(StateType::BEGIN, $this->game->getGameState($player->getId())));
                        $message->send($player->getConn());
                    }
                }
                break;
            case AckType::BEGIN->value:
                $currentPlayer = $this->game->getCurrentPlayer();
                if ($currentPlayer instanceof CPUPlayer){
                    $this->eventLoop->addTimer(2, function () use ($currentPlayer) {
                        $currentPlayer->takeTurn($this->game);
                    });
                }
                break;
            case AckType::DRAW->value:
                $currentPlayer = $this->game->getCurrentPlayer();
                if ($currentPlayer instanceof CPUPlayer){
                    $this->eventLoop->addTimer(1, function () use ($currentPlayer) {
                        $currentPlayer->takeTurn($this->game);
                    });
                }
                break;
            case AckType::DISCARD->value:
                if ($this->game->getCurrentRound()->getOutPlayer()){
                    DebugOutput::send("Out Player: " . $this->game->getCurrentRound()->getOutPlayer()->getName());
                }
                else {
                    DebugOutput::send("Nobody's Out Yet!");
                }
                if ($this->game->getCurrentRound()->getOutPlayer() && $this->game->getNextPlayer() === $this->game->getCurrentRound()->getOutPlayer()) {
                    DebugOutput::send("(Out player: " . $this->game->getCurrentRound()->getOutPlayer()->getId() . " is same as next player: " . $this->game->getNextPlayer()->getId() . ")");
                    foreach ($this->game->getPlayers() as $player){
                        $player->increaseScore($player->getRoundScore());
                    }
                    $this->eventLoop->addTimer(2, function (){
                        $this->messages[] = new Message(MessageType::STATE, new MessagePayload(StateType::ENDR, $this->game->getGameState()));
                        $this->sendMessages();
                    });
                }
                else {
                    $this->eventLoop->addTimer(1.5, function(){
                        $this->game->nextPlayer();
                    });
                }
                break;
            case AckType::UPDATE->value:
                $this->eventLoop->addTimer(2, function(){
                    if ($this->game->getCurrentPlayer() instanceof CPUPlayer){
                        $this->game->getCurrentPlayer()->takeTurn($this->game);
                    }
                });
                break;
            case AckType::ENDR->value:
                $this->eventLoop->addTimer(3, function(){
                    $this->game->nextRound();
                });
                break;
            case AckType::OUT->value:
            case AckType::LAY->value:
                break;
        }
    }
}