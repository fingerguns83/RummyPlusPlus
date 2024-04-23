<?php

namespace FGFC\enum;

enum StateType: string
{
    case INIT = 'init';
    case BEGIN = 'begin';
    case UPDATE = 'update';
    case ENDR = 'endr';
}