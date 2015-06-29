<?php


//require dirname(__DIR__) . '/src/MyApp/Chat.php';
require dirname(__DIR__) . '/vendor/autoload.php';  

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\Chat;

use React\EventLoop\Factory;
use React\Socket\Server as Reactor;
use React\ZMQ\Context;


    $loop   = React\EventLoop\Factory::create();
    $chat   = new Chat();

$context    = new React\ZMQ\Context($loop);
$pull       = $context->getSocket(ZMQ::SOCKET_PULL);
$pull->bind('tcp://127.0.0.1:5555'); // Binding to 127.0.0.1 means the only client that can connect is itself
$pull->on('message', array($chat, 'ZMQHandle'));


$sock = new React\Socket\Server($loop);
$sock->listen(8080, '127.0.0.1');

/*$server = IoServer::factory(
        new HttpServer(
            new WsServer(
               $chat
            )
        ),
        8080        
    );

$server->run();*/

//for console

/*$server = IoServer::factory(
        
        $chat,
        8080
        );

$server->run();*/

//ZermoMQ

$webServer  = new Ratchet\Server\IoServer(
     new Ratchet\Http\HttpServer(
        new Ratchet\WebSocket\WsServer(
                //new Ratchet\Wamp\WampServer(
                        $chat
                        )
               // )
             ),
       $sock
        );
$loop->run();

//$server->run();

//for web browser
/*$server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new Chat()
            )
        ),
        8080        
    );

$server->run();*/


