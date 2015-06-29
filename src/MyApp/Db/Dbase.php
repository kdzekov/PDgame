<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Dbase
{
    private $host;
    private $user;
    private $password;
    private $dbname;
 
    public function __construct($host, $user, $password, $dbname) 
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->dbname = $dbname;
    }
    
    public function connect()
    {
        $mysqli = new mysqli($this->host, $this->user, $this->password, $this->dbname);
        
        if (!$mysqli)
        {
            die(sprintf("[%d] %s\n", mysqli_connect_errno(), mysqli_connect_error()));
        }
        return $mysqli;
    }
}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

