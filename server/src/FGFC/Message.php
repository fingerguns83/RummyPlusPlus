<?php

namespace FGFC;
use FGFC\enum\MessageType;
use PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor;
use Ratchet\ConnectionInterface;

class Message
{
    private MessageType $type;
    private string|array $content;

    public function __construct(MessageType $type, string|MessagePayload|array|null $content = null)
    {
        $this->type = $type;

        if ($content instanceof MessagePayload) {
            $this->content['type'] = $content->type;
            $this->content['data'] = $content->content;
        }
        elseif ($content == null){
            $this->content = "";
        }
        else {
            $this->content = $content;
        }
    }

    /**
     * @return MessageType
     */
    public function getType(): MessageType
    {
        return $this->type;
    }

    /**
     * @param MessageType $type
     */
    public function setType(MessageType $type): void
    {
        $this->type = $type;
    }

    /**
     * @return String
     */
    public function getContent(): string|null
    {
        return $this->content;
    }

    /**
     * @param String $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * Formats the message as a JSON string.
     *
     * @return string The formatted JSON string.
     */
    public function formatMessage(): string
    {
        return trim(
            json_encode(
                array(
                    'type' => $this->type,
                    'payload' => $this->content
                )
            )
        );
    }

    /**
     * Sends the formatted message to the given connection.
     *
     * @param ConnectionInterface|null $conn The connection to send the message to, or null if no connection is provided.
     * @return void
     */
    public function send(ConnectionInterface|null $conn): void
    {
        $consoleColor = new ConsoleColor();
        if ($conn == null){
            return;
        }
        echo $consoleColor->apply("color_100", $this->formatMessage() . PHP_EOL);
        $conn->send($this->formatMessage());
    }
}