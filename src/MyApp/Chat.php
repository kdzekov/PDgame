<?php
   
namespace MyApp;
require dirname(dirname(__DIR__)) . '/vendor/autoload.php';  

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Chat implements MessageComponentInterface, WampServerInterface
{
    
    
    protected $clients;


    public function __construct() 
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) 
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
        $conn->send("You are connected on the server, hehehe....");
    }

    public function onMessage(ConnectionInterface $from, $msg) 
    {
       /* $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');*/

        foreach ($this->clients as $client) 
        {
            if ($from !== $client) 
            {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
            }    
        }
        
        echo $msg;
        
       // var_dump($from);
        
        //logika koajshto ke proveruva odreden broj na konekccii, ako gi nema ili e neparen 
        //prakja povratna poraka.
        
        //logika kojashto po random nachin ke pravi parovi. Dokolku site ne pratile poraka i dokolku par
        //tnerot ne ispratil poraka da vrati povratna informacija za toa.
    }
    
    public function ZMQHandle($entry)
    {
        //echo $entry;
        
        $entry_data = json_decode($entry, true);
        print_r($entry_data);
        
        /*foreach ($this->clients as $client) 
        {
             $client->send($entry_data);
        }*/
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
     
    public function onSubscribe(ConnectionInterface $conn, $topic) 
    {
        
    }
    public function onUnSubscribe(ConnectionInterface $conn, $topic) 
    {
        
    }

    
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) 
    {
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->close();
    }
}
