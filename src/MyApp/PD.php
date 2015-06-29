<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MyApp;
require dirname(dirname(__DIR__)) . '/vendor/autoload.php';  
require dirname(dirname(__DIR__)) . '/src/MyApp/Db/Dbase.php';
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class PD implements MessageComponentInterface
{
    protected $clients;
    //static public $mayStart = false;
    public $mayStart = false;
    private $db, $link;
    
    public function __construct() 
    {
        $this->clients = new \SplObjectStorage;
        
        $this->db = new \Dbase('localhost', 'konstantin', 'konstantin', 'pdgame_db');
        $this->link = $this->db->connect();
        
        /*-$this->db = new Dbase('localhost', 'konstantin', 'konstantin', 'pdgame_db');
        $linkobj = $db->connect();*/

  
    }
    public function onOpen(ConnectionInterface $conn) 
    {
        $this->clients->attach($conn);
        
        echo "New connection! {{$conn->resourceId}}\n";
        
        $numRecv=count($this->clients);
        echo "Total ".$numRecv. " clients connected";
    }
    
    public function onMessage(ConnectionInterface $from, $msg) 
    {        
        
        /*echo "Message from: ".$from->resourceId;
        
        if ($this->mayStart)
        //if (self::$mayStart==true)
        {
            $from->send("message accepted");
        }
        else
        {
           $this->SendMsgMyNotStartClient($from);
        }*/
        
        foreach ($this->clients as $client)
        {
            if ($from!=$client)
            {
                $client->send($msg);
            }
        }
        
        //$query = "INSERT into results (user, opponent, move_u, move_o, payoff) VALUES('$uname','$otype',$move,$opp_move,$payoff)";
    
        /*$query = "INSERT into user(name) VALUES('Ivan')";
        if(!$this->link->query($query))
        {
            //rollback
            //$linkobj->rollback();
            die(sprintf("[%d] %s\n", $this->link->errno(), $this->link->error()));
            exit();
        }*/
    }
    
    
    public function  onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occured: {$e->getMessage()}";
        $conn->close();
    }
    
    private function SendMsgMyNotStartClient($from)
    {
        $from->send("The game may still not start, other clients are expected to connect. ");
    } 
    
    public function SetMayStart($value)
    {
        $this->mayStart = $value;
    }
    public function GetMayStart()
    {
        return $this->mayStart;
    }
}

/*if (isset($_POST['server']))
{
   PD::$mayStart=true;
       
   if (PD::$mayStart==true)
   {
        echo "Maystart is set to true";
   }
   else
   {
       echo "Maystart is not set to true";
   }
}*/