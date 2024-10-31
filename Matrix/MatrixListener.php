#!/usr/bin/env php
<?php
include_once 'Messages.php';
include_once 'logmanager.php';    
class MatrixListener {
    private $timeoutinsec = 1000;
    private $messages;
    private $lastBatch = null;
    private $running = true;
    private $baseUrl;
    private $accessToken;
    private $retryDelay = 5; // seconds
    private $maxRetries = 3;
    private LogManager $logmanager;

    public function __construct() {
        $this->logmanager = new LogManager("matrixlistener");
        $this->messages = new Messages();
       
    }
    public function isRunning(){
       $timeinsec = file_get_contents(__DIR__."/timerupdate.txt");
       $timeinsec = intval($timeinsec );
       if(time()-$timeinsec > $this->timeoutinsec){
        echo "Not running !";
        return false;
       }
       return true;

    }
    public function updateTimer(){
        file_put_contents(__DIR__."/timerupdate.txt",strval(time()));

    }
    public function start() {
        $this->log("Listener starting...");
        
        while ($this->running) {
            try {
                $this->listen();
                $this->updateTimer();
                sleep(2);
            } catch (Exception $e) {
                $this->log("Error: " . $e->getMessage());
                sleep($this->retryDelay);
            }
        }
    }
    
    private function listen() {
        $this->messages->syncMessages(true);
        
    }
    
    public function shutdown($signal = null) {
        $this->log("Shutting down...");
        $this->running = false;
    }
    
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $this->logmanager->writelinetolog("[$timestamp] $message");
        error_log("[$timestamp] $message");
    }
}

$matrixlistener = new MatrixListener();


if($matrixlistener->isRunning()){
    echo "Running, we get out";
    exit(0);
}else{
    $matrixlistener->start();
}

?>