<?php

namespace FGFC;

use FGFC\enum\BookType;

class Book
{
    private BookType $type;
    private array $cards;
    public function __construct(BookType $type, array $cards){
        $this->type = $type;
        $this->cards = $cards;
    }

    public function getType(): BookType{
        return $this->type;
    }
    public function getSize(): int{
        return count($this->cards);
    }
    public function getWildCount(Game $game): int{
        $count = 0;
        foreach ($this->cards as $card){
            if ($card->isWildcard($game->getRoundNumber())){
                $count++;
            }
        }
        return $count;
    }

}