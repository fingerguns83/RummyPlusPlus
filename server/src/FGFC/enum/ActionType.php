<?php
namespace FGFC\enum;
enum ActionType: string
{
    case START = 'start';
    case DRAW1 = 'draw1';
    case DRAW2 = 'draw2';
    case DRAW3 = 'drawdiscard';
    case DISCARD = 'discard';
    case CHECKSET = 'checkset';
    case OUT = 'out';
    case LAY = 'lay';
}