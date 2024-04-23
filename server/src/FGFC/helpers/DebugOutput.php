<?php

namespace FGFC\helpers;

use PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor;

class DebugOutput
{
    public static function send(String $message) : void {
        $enabled = false;

        if ($enabled){
            $consoleColor = new ConsoleColor();
            echo $consoleColor->apply("color_124", "[" . date('h:i:s') . "] " . $message . PHP_EOL);
        }
    }
}