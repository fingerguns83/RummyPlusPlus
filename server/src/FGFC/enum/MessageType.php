<?php
Namespace FGFC\enum;
enum MessageType: string
{
    case INFO = 'info';
    case INTENT = 'intent';
    case ACTION = 'action';
    case PING = 'ping';
    case STATE = 'state';
    case ACK = 'ack';
}