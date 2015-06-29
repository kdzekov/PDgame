<?php

require dirname(__DIR__) . '/src/MyApp/PD.php';
require dirname(__DIR__) . '/vendor/autoload.php';  

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\PD;

$server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new PD()
            )
        ),
        8080     
    );

$server->run(); 




