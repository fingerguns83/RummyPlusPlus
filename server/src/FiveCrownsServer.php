<?php

require '../vendor/autoload.php';
spl_autoload_register(function ($class_name) {
    include "../" . str_replace("\\", "/", $class_name) . '.php';
});


use FGFC\enum\MessageType;
use FGFC\helpers\Client;
use FGFC\Message;
use FGFC\Player;
use Ramsey\Uuid\Uuid;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\LoopInterface;

class FiveCrownsServer implements MessageComponentInterface
{
    private \SplObjectStorage $clients;
    public LoopInterface $eventLoop;

    public function __construct(LoopInterface $loop)
    {
        DebugOutput::send("Started Five Crowns Server");
        $this->clients = new \SplObjectStorage;
        $this->eventLoop = $loop;

        $this->eventLoop->addPeriodicTimer(4, function () {
            if ($this->clients->count() > 0){
                foreach ($this->clients as $client) {
                    $message = new Message(MessageType::PING, ($client->getPlayer()) ? $client->getPlayer()->id : '');
                    $client->getConn()->send($message->formatMessage());
                }
            }
        });
    }

    function onOpen(ConnectionInterface $conn): void
    {
        $client = new Client(null, null, $conn);
        $this->clients->attach($client);

        $message = new Message(MessageType::WELCOME);
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
        $msgarr = json_decode($msg, true);
        DebugOutput::send($msg);
        switch($msgarr['type']){
            case MessageType::INFO:
                break;
            case MessageType::REGISTRATION->value:
                echo "Received Registration" . PHP_EOL;
                foreach($this->clients as $client){

                    if ($client->getConn() === $from){
                        $client->setPlayer(new Player(Uuid::uuid4()->toString(), $msgarr['data']));
                    }
                }
                break;
        }
    }
}

$loop = \React\EventLoop\Factory::create();

//$socket = new \React\Socket\Server('127.0.0.1:8988', $loop);
$socket = new React\Socket\SocketServer('tcp://127.0.0.1:8988', [], $loop);
$wsServer = new WsServer(new FiveCrownsServer($loop));
$wsServer->enableKeepAlive($loop);
$server = new IoServer(new HttpServer($wsServer), $socket, $loop);
/*$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new FiveCrownsServer($loop)
        )
    ),
    8988
);*/

$server->run();