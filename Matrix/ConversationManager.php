<?php
include_once 'Rooms.php';
include_once 'Messages.php';
include_once 'FrenchNameGenerator.php';
include_once 'databasemanager.php';
include_once 'ServerIdentity.php';
class ConversationManager {
private Rooms $rooms;    
private Messages $messages;
private DatabaseManager $dbmanager;
private ServerIdentity $serverid;
public function __construct(){
$this->rooms = new Rooms();
$this->messages = new Messages();
$this->dbmanager = new DatabaseManager();
$this->serverid = new ServerIdentity();
}
/*

//$dbmanager->createSBChattables();
if ($dbmanager->getSMSChatRoom('4444444444','5555555555')) {
    echo $dbmanager->getSMSChatRoom('4444444444','5555555555');
}else{
    echo "existe pas";
}
$dbmanager->setSMSChatRoom('4444444444','5555555555','server:dfdfdfddddfdf');*/ 
public function receiveSMS($smsinfo){
    //$roomname = "#".$smsinfo['from'];
    #$roomname = "#salle-de-test";
    //$roomid = $this->rooms->doRoomExist($roomname);
    $roomid = $this->dbmanager->getSMSChatRoom($smsinfo['from'],$smsinfo['to']);
    if($roomid){
        echo _("La chambre existe, id: ").$roomid;
        }
        else{
        echo _("La chambre n'existe, pas, créons la.")."<br>";
        
        if ($this->serverid->usefakenames){
            $generator = new FrenchNameGenerator();
		    $scrambledname = $generator->generateName($smsinfo['from']);
        }else{
            $scrambledname = $smsinfo['from'];
        }
        echo $smsinfo['from'] ._(" sera connu en tant que : ").$scrambledname;
        $roomid = $this->rooms->createRoom($scrambledname);
        echo "roomid ".$roomid ;
        
        $this->dbmanager->setSMSChatRoom($smsinfo['from'],$smsinfo['to'],$roomid,$scrambledname );
        //$roomid = $this->rooms->createRoom(ltrim($roomname,'#'));
        }
    $this->messages->sendMessagetoRoom($roomid,$smsinfo['message']);
    }


}

?>