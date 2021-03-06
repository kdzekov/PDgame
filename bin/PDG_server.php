<?php


require dirname(__DIR__) . '/vendor/autoload.php'; 

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\PDG;

use React\EventLoop\Factory;
use React\Socket\Server as Reactor;
use React\ZMQ\Context;


    $loop   = React\EventLoop\Factory::create();
    $pdgame = new MyApp\PDG;

    // Listen for the web server to make a ZeroMQ push after an ajax request
    $context = new React\ZMQ\Context($loop);
    $pull = $context->getSocket(ZMQ::SOCKET_PULL);
    $pull->bind('tcp://127.0.0.1:5555'); // Binding to 127.0.0.1 means the only client that can connect is itself
    $pull->on('message', array($pdgame, 'ZMQHandle'));

    // Set up our WebSocket server for clients wanting real-time updates
    $webSock = new React\Socket\Server($loop);
    $webSock->listen(8080, '127.0.0.1'); // Binding to 0.0.0.0 means remotes can connect
    $webServer = new Ratchet\Server\IoServer(
        new Ratchet\Http\HttpServer(
            new Ratchet\WebSocket\WsServer(
               // new Ratchet\Wamp\WampServer(
                    $pdgame
                //)
            )
        ),
        $webSock
    );

    $loop->run();


