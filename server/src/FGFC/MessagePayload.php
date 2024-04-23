<?php

namespace FGFC;

use FGFC\enum\ActionType;
use FGFC\enum\InfoType;
use FGFC\enum\StateType;

class MessagePayload
{
    public ActionType|InfoType|StateType $type;
    public string|array|null $content;
    public function __construct(ActionType|InfoType|StateType $type, string|array|null $content = null)
    {
        $this->type = $type;
        if ($content !== null) {
            $this->content = $content;
        }
        else {
            $this->content = "";
        }
    }

}