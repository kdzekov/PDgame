<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
     $entryData = array(
        'category' => 'Main'
      , 'title'    => 'Title'
      , 'article'  => 'Article'
      , 'when'     => time()
    );

    $context = new ZMQContext();

    $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
    $socket->connect("tcp://127.0.0.1:5555");

    $socket->send(json_encode($entryData));