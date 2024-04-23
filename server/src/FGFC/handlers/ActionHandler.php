<?php

namespace FGFC\handlers;

use FGFC\enum\ActionType;
use FGFC\enum\InfoType;
use FGFC\enum\MessageType;
use FGFC\Message;
use FGFC\MessagePayload;

class ActionHandler extends MessageHandler
{

    public function handle() : void {
        $player = $this->game->getPlayer($this->msg['payload']['data']['player']);
        switch ($this->msg['payload']['type']){
            case ActionType::DRAW1->value:
                $player->drawCard($this->game->getCurrentRound()->getDraw1());
                $this->messages[] = new Message(MessageType::ACTION, new MessagePayload(ActionType::DRAW1, $this->game->getGameState($player->getId())));
                break;
            case ActionType::DRAW2->value:
                $player->drawCard($this->game->getCurrentRound()->getDraw2());
                $this->messages[] = new Message(MessageType::ACTION, new MessagePayload(ActionType::DRAW2, $this->game->getGameState($player->getId())));
                break;
            case ActionType::DRAW3->value:
                $player->drawCard($this->game->getCurrentRound()->getDiscardDeck(), true);
                $this->messages[] = new Message(MessageType::ACTION, new MessagePayload(ActionType::DRAW3, $this->game->getGameState($player->getId())));
                break;
            case ActionType::DISCARD->value:
                $player->discard($player->getCard($this->msg['payload']['data']['cardId']), $this->game->getCurrentRound()->getDiscardDeck(), true);
                $this->messages[] = new Message(MessageType::ACTION, new MessagePayload(ActionType::DISCARD, $this->game->getGameState($player->getId())));
                break;
            case ActionType::OUT->value:
                $score = $this->game->calculateScore($player, $this->msg['payload']['data']['books'], $this->msg['payload']['data']['remainder']);
                if ($score > 0){
                    $this->messages[] = new Message(MessageType::INFO, new MessagePayload(InfoType::BADOUT, $this->game->getGameState($player->getId())));
                }
                else {
                    if (!$this->game->getCurrentRound()->getOutPlayer()){
                        $this->game->getCurrentRound()->setOutPlayer($player);
                    }
                    $this->game->getCurrentRound()->setLastTurn(true);

                    $player->discard($player->getCard($this->msg['payload']['data']['discard']), $this->game->getCurrentRound()->getDiscardDeck(), true);
                    $this->messages[] = new Message(MessageType::ACTION, new MessagePayload(ActionType::OUT, $player->getId()));
                    $this->messages[] = new Message(MessageType::ACTION, new MessagePayload(ActionType::DISCARD, $this->game->getGameState($player->getId())));
                }
                break;
            case ActionType::LAY->value:
                $player->setRoundScore($this->game->calculateScore($player, $this->msg['payload']['data']['books'], $this->msg['payload']['data']['remainder']));
                $player->discard($player->getCard($this->msg['payload']['data']['discard']), $this->game->getCurrentRound()->getDiscardDeck(), true);
                $this->messages[] = new Message(MessageType::ACTION, new MessagePayload(ActionType::LAY, $player->getId()));
                $this->messages[] = new Message(MessageType::ACTION, new MessagePayload(ActionType::DISCARD, $this->game->getGameState($player->getId())));
                break;
            case ActionType::CHECKSET->value:
                break;
        }
        $this->sendMessages();
    }
}