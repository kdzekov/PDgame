<?php

namespace MyApp;
require dirname(dirname(__DIR__)) . '/vendor/autoload.php';  
//require dirname(dirname(__DIR__)) . '/src/MyApp/Db/Dbase.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

//use Ratchet\Wamp\WampServerInterface;

//use MyApp\Db\Dbase;

//, WampServerInterface
class PDG implements MessageComponentInterface
{
    //stores the client objects in the container
    protected $clients;
    //flag that determines when the game can start. It is set to TRUE by the
    //administrator on START button.
    private $mayStart = false;
    //the number of rounds. It can be regulated by the adminitrator.
    private $rounds = 5;
    //the current round
    private $currentRound = 1;
    //the number of players. this variable is still not regulated
    private $players;
    //the payoff matrix. It can be changed via the admin panel.
    private $payoff = array(
        'R' => 35,
        'S' => 3,
        'T' => 40,
        'P' => 7
    );
    
    private $db, $link, $fp;
    private $newRound = true;

    //two dimensional array in which pairs of players are created for every round.
    private $pairs = array();
    //array with all the connected clients. It is updated on set_pairs() procedure.
    private $clients_arr = array();
    //client's pointer on the moves and payoff vectors.
    private $client_loc = array();
    //two dimensional array containing the information about the moves of the clients.
    private $moves  =   array();
    //two dimensional array containing the information about the payoff of the clients.
    private $game_payoff = array();
    //resourceid as key, username as value
    private $users = array();
    //array with moves of the game
    private $game_moves = array('C','D');
    
    
    public function __construct() 
    {
        $this->clients = new \SplObjectStorage;
        
        /*$this->db = new \Dbase('localhost', 'konstantin', 'konstantin', 'pdgame_db');
        $this->link = $this->db->connect();*/
        
        //$path = '/srv/www/htdocs/pdg_realtime';
        $path = '../results.txt';
        //$this->fp = fopen('../../results.txt', 'w+');
        $this->fp = fopen($path, 'w+');
        
    }
   
    public function onOpen(ConnectionInterface $conn) 
    {
        $this->clients->attach($conn);
        echo "New connection! {{$conn->resourceId}}";
        $numRecv    =   count($this->clients);
        echo "Total ".$numRecv. " clients connected\n";           
    }
    
    public function onMessage(ConnectionInterface $from, $msg) 
    {    
        //add username to the client
        $this->handleUsers($from,$msg);
        
        if ($this->mayStart)
        { //this block handles the interaction in the game
            if ($this->currentRound <= $this->rounds)
            {

                /*if (strlen($msg)==4)
                {
                    echo '<pre>';
                        print_r($this->client_loc);
                    echo '</pre>';
                    
                    echo '\n';
                    
                    echo '<pre>';
                        print_r($this->moves);
                    echo '</pre>';
                    
                    echo '\n';
                    
                    echo '<pre>';
                        print_r($this->game_payoff);
                    echo '</pre>';
                }*/
                
                $this->manageDecision($from, $msg);
                print "A message sent: ".$msg."\n";
                    
            }
            else
            {
                $msg = 'The game has finished, thank you for the participation!';
                $this->broadcastMessage($msg);
                //to put eventually code for stop server.
            }
           
        }
        else 
        {
            
            //condition, if you made move..
            if (in_array($msg, $this->game_moves))
            {
                $warning = 'inf:The game hasn\'t started yet. Please wait!'; 
                $this->sendtoClient($warning, $from);
            }
            
        }
    }
    
    private function manageDecision($from, $msg)
    {    
        if (!$this->connectedLater($from))      
        { 
            
             $resId = $from->resourceId;
             //locate the client/player in the moves/pairs array
             $row = $this->client_loc[$resId][0];
             $col = $this->client_loc[$resId][1];

             $first_update = TRUE;
                
             $this->updateMoves($row, $col,$from, $msg, $first_update);
             $this->updatePayoffs($from, $first_update);
             $this->endCurrentRound();      
        }
    }
    
    //Function that handles admin requests
    public function ZMQHandle($entry)
    {
        //this admin message is parsed and appropriate function is called. For
        //example function for setting or initialization, function for start etc.
        //echo "This is a message from the alternative channnel: ".$entry;
        
        $admin = json_decode($entry);
        
        if (isset($admin->gamestart)) // start game admin intervention
        {
           $this->mayStart = TRUE;
           $this->set_pairs();
           
           
           $this->newRound = FALSE;
           $msg = 'inf:The game has started, make your first choice!';
           $this->broadcastMessage($msg);
           
           echo "The game start set to true!";
           
        } // setup game by admin
        else if (isset($admin->rounds))
        {
            $this->rounds       = $admin->rounds;
            $this->players      = $admin->players;
            
            $this->payoff['R']  = $admin->R;
            $this->payoff['S']  = $admin->S;
            $this->payoff['T']  = $admin->T;
            $this->payoff['P']  = $admin->P;
            
            $msg = 'pff:'.$this->payoff['R'].'-'.$this->payoff['S'].'-'.$this->payoff['T'].'-'.$this->payoff['P'];
            $this->broadcastMessage($msg);
        }
    }
    
    public function  onClose(ConnectionInterface $conn)
    {
        //detach the client that closes the connection
        $this->clients->detach($conn);
        
        //removes the closing client from the array of clients
        $key = array_search($conn, $this->clients_arr);
        unset($this->clients_arr[$key]);
        $this->clients_arr = array_values($this->clients_arr);
        
        //new
        unset($this->users[$conn->resourceId]);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e)
    {

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
    
    /* public function onSubscribe(ConnectionInterface $conn, $topic) {
    }
    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
    }
    
     public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        // In this application if clients send data it's because the user hacked around in console
        //$conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        // In this application if clients send data it's because the user hacked around in console
        //$conn->close();
    }*/
    
    
    private function conn_exists($conn)
    {
        if ($this->clients->contains($conn))
                return true;
        return false;
    }
        
    
    private function broadcastMessage($msg)
    {
        foreach ($this->clients as $client)
        {
            $client->send($msg);
        }
    }
    
    private function sendtoClient($msg,$to)
    {
         foreach ($this->clients as $client)
         {
             if ($client==$to)
             {
                 $client->send($msg);
             }
         }
    }

    
    private function set_pairs()
    {
        
        $n_client = 0;
        $this->pairs = array();
        $this->moves = array();
        $this->clients_arr = array();
        $this->client_loc = array();
        $this->game_payoff = array();
              
        
        foreach($this->clients as $client)
        {
            array_push($this->clients_arr, $client);
        }
        
        //makes random pairs of players
        shuffle($this->clients_arr);
        
        $n_client = count($this->clients_arr);
        
        $k=1;
        for($i=1; $i< $n_client; $i=$i+2)
        { 
            $this->pairs[$k][1]=$this->clients_arr[$i-1];
            $this->pairs[$k][2]=$this->clients_arr[$i];
            
            $this->moves[$k][1] = 0;
            $this->moves[$k][2] = 0;
            
            $this->game_payoff[$k][1] = 0;
            $this->game_payoff[$k][2] = 0;
            
            $res_id1 = $this->clients_arr[$i-1]->resourceId;
            $res_id2 = $this->clients_arr[$i]->resourceId;
            
            $this->client_loc[$res_id1] = array($k,1);
            $this->client_loc[$res_id2] = array($k,2);

            $k++;
        }
    }
    
    
    private function updateMoves($row, $col, $to, $move, &$fu)
    {
         echo "The associated move is: ".$this->moves[$row][$col].'\n';
        
         if (is_string($this->moves[$row][$col]))
         {
              //send message to the client that he allready made a move
              $note = "inf:You have allready made your choice. Wait until the next round. Thank you! ";
              $this->sendtoClient($note, $to);
              print "more then one choice of the client..";
              $fu = FALSE;
         }
         else
         {
              $this->moves[$row][$col] = $move;
         }
    }
    
    
    private function updatePayoffs($player, $fu)
    {
        
        $resId      = 0;
        $resid_2    = 0;
        $row        = 0;
        $col        = 0;
        $col_pair   = 0;
        
        $move_1 = "";
        $move_2 = "";
        
        $resId  = $player->resourceId;
        $row    = $this->client_loc[$resId][0];
        $col    = $this->client_loc[$resId][1];
        
        if ($col==1)
            $col_pair = 2;
        else
            $col_pair=1;
        
        $player_2 = $this->pairs[$row][$col_pair];

        
        if (is_string( $this->moves[$row][$col_pair]))
        {
            $move_1 = $this->moves[$row][$col];
            $move_2 = $this->moves[$row][$col_pair];
            
            //it could be added some code for move conversion [when sent back to client]
            
            if (($move_1=='C')&&($move_2=='C'))
            {
                $this->game_payoff[$row][$col]      = $this->payoff['R'];
                $this->game_payoff[$row][$col_pair] = $this->payoff['R'];
            }
            else if (($move_1=='C')&&($move_2=='D'))
            {
                $this->game_payoff[$row][$col]      = $this->payoff['S'];
                $this->game_payoff[$row][$col_pair] = $this->payoff['T'];
            }
            else if (($move_1=='D')&&($move_2=='C'))
            {
                $this->game_payoff[$row][$col]      = $this->payoff['T'];
                $this->game_payoff[$row][$col_pair] = $this->payoff['S'];
                
            }elseif (($move_1=='D')&&($move_2=='D')) 
            {
                $this->game_payoff[$row][$col]      = $this->payoff['P'];
                $this->game_payoff[$row][$col_pair] = $this->payoff['P'];
            }
            

            if ($fu)
            {
                $msg_1 = "inf: You played ".$move_1." and the opponent played ".$move_2.". You got ".$this->game_payoff[$row][$col] ." points.";
                $msg_2 = "inf: You played ".$move_2." and the opponent played ".$move_1.". You got ".$this->game_payoff[$row][$col_pair] ." points.";
        
                $this->sendtoClient($msg_1, $player);
                $this->sendtoClient($msg_2, $player_2);
            }
                
        }
    }
    
    private function endCurrentRound()
    {
        
        $res_id     = 0;
        $row        = 0;
        $col        = 0;
        $all_played = TRUE;
        
        //check whether all clients have played for this round
       /*foreach ($this->clients_arr as $client)
        {
            $res_id = $client->resourceId;
            $row = $this->client_loc[$res_id][0];
            $col = $this->client_loc[$res_id][1];
            
            //check if there is a player that didn't play the current round
            if (!is_string($this->moves[$row][$col]))
            {
                $all_played = FALSE;
                break;
            }
        }*/
        
        foreach ($this->client_loc as $res_id=>$rc)
        {            
            $row = $rc[0];
            $col = $rc[1];
            
            //check if there is a player that didn't play the current round
            if (!is_string($this->moves[$row][$col]))
            {
                $all_played = FALSE;
                break;
            }
        }
        
        if ($all_played)
        {
            $this->log_data();
            $this->set_pairs();
            $this->currentRound++;
            //log data, separate function
            
            //sleep(5);
            if (!($this->currentRound > $this->rounds)){
                $msg = 'new:A new round has started, please make your choice!';               
            }
            else{
                $msg='new:The game has finished, thank you for the participation!';
                //cod for client disconnect
            }
            $this->broadcastMessage($msg);
        } 
    }
    
    //This function logs data to a file
    private function log_data()
    {
        if ($this->currentRound == 1)
        {
            $title = "Round Player1 Player2 Move1 Move2 Mode ";
            fputs($this->fp, $title);
        }
        
        
        foreach ($this->pairs as $pair)
        {
            
            $player_1 = $pair[1];
            $player_2 = $pair[2];
            
            $res_id1 = $player_1->resourceId;
            $res_id2 = $player_2->resourceId;
            
            $row1 = $this->client_loc[$res_id1][0];
            $col1 = $this->client_loc[$res_id1][1];
            
            $row2 = $this->client_loc[$res_id2][0];
            $col2 = $this->client_loc[$res_id2][1];
            
            
            $move_1 = $this->moves[$row1][$col1];
            $move_2 = $this->moves[$row2][$col2];
            
            $move_1_n = $this->convertMove($move_1);
            $move_2_n = $this->convertMove($move_2);
            
            //if ((is_string($move_1)) && (is_string($move_2)))
            //{
            //$record = $this->currentRound." ".$res_id1." ".$res_id2." ".$move_1_n." ".$move_2_n.'\r\n';
            
            $record = $this->currentRound." ".$this->users[$res_id1]." ".$this->users[$res_id2]." ".$move_1_n." ".$move_2_n."\n";
            fputs($this->fp, $record);
            
            //}
        }
        
        if ($this->currentRound == $this->rounds)
        {
            fclose($this->fp);
        }
    }
    
    private function convertMove($move)
    {
        switch ($move)
        {
            case 'C':
                return 1;
                break;
            case 'D':
                return 2;
                break;
            default:
                return 'disconn';
                break;
        }
    }
    
    private function connectedLater($client)
    {
        
        $connected_later = FALSE;
        $res_Id = $client->resourceId;
        $active_players = array_keys($this->client_loc);
        
        
        //clients that connect later
        if (!in_array($client, $this->clients_arr))
        {
            
            $connected_later = TRUE;
            $msg = 'inf:You have connected later, wait until the next rount!';
            $this->sendtoClient($msg, $client);
            
            
            $msg = 'pff:'.$this->payoff['R'].'-'.$this->payoff['S'].'-'.$this->payoff['T'].'-'.$this->payoff['P'];
            $this->sendtoClient($msg, $client);
            //update payoff matrix function
            
        }//clients that do not have pair partner
        elseif (!in_array($res_Id, $active_players))
        {
            $connected_later = TRUE;
            $msg = 'inf:Because of the odd number of players you don\'t have an opponent. Pleae wait until some next round!';
            $this->sendtoClient($msg, $client);
        }
        
        return $connected_later;
    }
    
    private function handleUsers($client, $username)
    {
        if(!in_array($username, $this->game_moves))
        {
            $this->users[$client->resourceId]=$username;
            echo $client->resourceId.'=>'.$username."\n";
        }
    }
        
        
}