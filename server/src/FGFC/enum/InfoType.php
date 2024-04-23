<?php

namespace FGFC\enum;

enum InfoType: string
{
    case WELCOME = 'welcome';
    case REGISTRATION = 'registration';
    case DEALER = 'dealer';
    case PLAYER = 'player';
    case BADOUT = 'badout';
}