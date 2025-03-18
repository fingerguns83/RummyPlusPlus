<?php

require dirname(__DIR__, 1) . '/vendor/autoload.php';


use FGFC\enum\InfoType;
use FGFC\enum\MessageType;
use FGFC\Game;
use FGFC\handlers\AckHandler;
use FGFC\handlers\IntentHandler;
use FGFC\handlers\InfoHandler;
use FGFC\handlers\StateHandler;
use FGFC\helpers\Client;
use FGFC\helpers\DebugOutput;
use FGFC\Message;
use FGFC\MessagePayload;
use PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\LoopInterface;

class FiveCrownsSinglePlayerServer implements MessageComponentInterface
{
    private \SplObjectStorage $games;
    private \SplObjectStorage $clients;
    private LoopInterface $eventLoop;

    public function __construct(LoopInterface $loop)
    {
        DebugOutput::send("Started Five Crowns Single Player Server");
        $this->clients = new \SplObjectStorage;
        $this->games = new \SplObjectStorage;
        $this->eventLoop = $loop;

        $this->eventLoop->addPeriodicTimer(4, function () {
            if ($this->clients->count() > 0){
                foreach ($this->clients as $client) {
                    $message = new Message(MessageType::PING, ($client->getPlayer()) ? $client->getPlayer()->getId() : '');
                    $client->getConn()->send($message->formatMessage());
                }
            }
        });
    }

    function onOpen(ConnectionInterface $conn): void
    {
        $client = new Client($conn, null, true, null, null);
        $this->clients->attach($client);

        $message = new Message(MessageType::INFO, new MessagePayload(InfoType::WELCOME));
        $message->send($client->getConn());
    }

    /**
     * @inheritDoc
     */
    function onClose(ConnectionInterface $conn): void
    {
        // TODO: Implement onClose() method.
    }

    /**
     * @inheritDoc
     */
    function onError(ConnectionInterface $conn, \Exception $e): void
    {
        // TODO: Implement onError() method.
    }

    /**
     * @inheritDoc
     */
    function onMessage(ConnectionInterface $from, $msg): void
    {
        $consoleColor = new ConsoleColor();

        $msgarr = json_decode($msg, true);

        echo $consoleColor->apply("color_111", $msg . PHP_EOL);


        switch($msgarr['type']){
            case MessageType::INFO->value:
                (new InfoHandler($this, $from, $msgarr, $this->getEventLoop()))->handle();
                break;
            case MessageType::INTENT->value:
                (new IntentHandler($this, $from, $msgarr, $this->getEventLoop()))->handle();
                break;
            case MessageType::STATE->value:
                (new StateHandler($this, $from, $msgarr, $this->getEventLoop()))->handle();
                break;
            case MessageType::ACK->value:
                (new AckHandler($this, $from, $msgarr, $this->getEventLoop()))->handle();
                break;
            case MessageType::PING:
                break;
        }
    }

    /**
     * @return SplObjectStorage
     */
    public function getClients(): SplObjectStorage
    {
        return $this->clients;
    }

    /**
     * @return SplObjectStorage
     */
    public function getGames(): \SplObjectStorage
    {
        return $this->games;
    }

    /**
     * @return LoopInterface
     */
    public function getEventLoop(): LoopInterface
    {
        return $this->eventLoop;
    }
}

$loop = \React\EventLoop\Factory::create();

$socket = new React\Socket\SocketServer('tcp://0.0.0.0:8988', [], $loop);
$wsServer = new WsServer(new FiveCrownsSinglePlayerServer($loop));
$wsServer->enableKeepAlive($loop, 120);
$server = new IoServer(new HttpServer($wsServer), $socket, $loop);

$server->run();