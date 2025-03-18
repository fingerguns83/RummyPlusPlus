<?php

namespace FGFC;

use FGFC\Book;
use FGFC\enum\BookType;

class SetBook extends Book
{
    private int $value;

    public function __construct(array $cards, int $value)
    {
        $this->value = $value;
        parent::__construct(BookType::SET, $cards);
    }
}