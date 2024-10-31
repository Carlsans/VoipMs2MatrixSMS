<?php
include_once 'NetworkRequests.php';
include_once 'ServerIdentity.php';
include_once 'Team.php';
class Rooms {
private NetworkRequests $requests;  
private Team $team;  
private ServerIdentity $serverid;
    public function __construct(){
        $this->requests = new NetworkRequests();
        $this->team = new Team();
        $this->serverid = new ServerIdentity();
    }
    // Note, le systeme doit se rappeler des salles par leur id et du num de tel associe
    // ici on s<amuse
    public function doRoomExist($roomalias){
        // get /_matrix/client/v3/directory/room/{roomAlias} 
        echo $roomalias."@".$this->serverid->servername;
        $room_idandservers = $this->requests->request("GET",'/_matrix/client/v3/directory/room/'.urlencode($roomalias.":".$this->serverid->servername));
        if($room_idandservers){
            $this->printArray($room_idandservers);
            return $room_idandservers['room_id'];  
        }
         
        return false;

    }
    public function listLocalRoomAlias($roomid){
        //  get /_matrix/client/v3/rooms/{roomId}/aliases 
        $roomaliases = $this->requests->request("GET",'/_matrix/client/v3/rooms/'.urlencode($roomid).'/aliases');
        return $roomaliases['aliases'];

    }
    public function createRoom($roomname){
        //post /_matrix/client/v3/createRoom 
        $roomparameter = [
            "creation_content" => ["m.federate" => true],
            "name" => "Appel de ".$roomname,
            "preset" => "private_chat",
            //"room_alias_name" => $roomalias, # L'utilisation d'alias est abandonne parce qu il est impossible de garantir leur disponibilite.
            "visibility" => "private",
            "invite" => $this->team->getMembers()
        ];
        $room_id = $this->requests->request("POST",'/_matrix/client/v3/createRoom',$roomparameter);
        return $room_id ["room_id"];
    }
    public function listJoinedRooms(){
        //  get /_matrix/client/v3/joined_rooms 
        $roomlist = $this->requests->request("GET",'/_matrix/client/v3/joined_rooms');
        
        return $roomlist;
    }
    private function printArray($myarray){
        echo "<pre>";
        print_r($myarray);
        echo "</pre><br>";
        

    }


}

?>