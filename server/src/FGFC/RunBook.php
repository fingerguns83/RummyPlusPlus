<?php

namespace FGFC;

use FGFC\Book;
use FGFC\enum\BookType;

class RunBook extends Book
{
    private string $suit;
    private int $min;
    private int $max;

    public function __construct(array $cards, string $suit)
    {
        $values = [];
        foreach ($cards as $card) {
            if (!$card->isJoker()){
                $values[] = $card->getValue();
            }
        }
        $this->min = min($values);
        $this->max = max($values);

        $this->suit = $suit;

        parent::__construct(BookType::RUN, $cards);
    }

}