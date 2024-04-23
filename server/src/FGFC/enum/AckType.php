<?php

namespace FGFC\enum;

enum AckType: string
{
    case REGISTRATION = 'registration';
    case INIT = 'init';
    case BEGIN = 'begin';
    case UPDATE = 'update';
    case DRAW = 'draw';
    case DISCARD = 'discard';
    case OUT = 'out';
    case LAY = 'lay';
    case ENDR = 'endr';
}