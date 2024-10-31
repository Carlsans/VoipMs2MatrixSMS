<?php
include_once 'ServerIdentity.php';
class Team{
    private array $members;
    public function __construct(){
        $serverid = new ServerIdentity();
        $this->members = $serverid->teammembers;
    }
    public function getMembers(){
        return $this->members;
    }


}
?>