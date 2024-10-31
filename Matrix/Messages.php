<?php
include_once 'NetworkRequests.php';
include_once 'ServerIdentity.php';
include_once 'Rooms.php';
include_once 'VoipMs.php';
include_once 'databasemanager.php';
class Messages{
    private NetworkRequests $requests;
    private ServerIdentity $serverid;
    private Rooms $rooms;
    private VoipMs $voipms;
    private DatabaseManager $dbmanager;
    public function __construct(){
        $this->dbmanager = new DatabaseManager();
        $this->requests = new NetworkRequests();
        $this->serverid = new ServerIdentity();
        $this->rooms = new Rooms();
        $this->voipms = new VoipMs();
    }
    public function sendMessagetoRoom($roomid,$message){
    // put /_matrix/client/v3/rooms/{roomId}/send/{eventType}/{txnId} 
    $messagecontent = [
        'body' => $message,
        'msgtype' => 'm.text'
    ];
        $eventid = $this->requests->request("PUT",'/_matrix/client/v3/rooms/'.urlencode($roomid).'/send/'. urlencode("m.room.message") .'/'.uniqid(),$messagecontent);
        return $eventid;
    }
    public function syncMessages($withtimeout = true,$messageonly=true){
        // get /_matrix/client/v3/sync 
        $nextbatchfile = dirname(__FILE__)."/next_batch.tok";
        $data =[];
        $getquery = "";
        if(file_exists($nextbatchfile)){
            $data['since'] = file_get_contents($nextbatchfile);
        }
        if($withtimeout){
            $data['timeout'] = 30000; // 30 s
        }
        if($messageonly){
            $data['filter']=  json_encode($this->getRoomMessageFilter());
        }
        //$data =[]; //   EEEENLLLLLLVVVVEEEEEEERRRRRR
        if(!empty($data)){
            $getquery = "?".http_build_query($data);
        }
        $sync_data = $this->requests->request("GET",'/_matrix/client/v3/sync'.$getquery, usingtimeout: true);
        #$this->printArray($sync_data);
        if(array_key_exists('rooms', $sync_data)){
            $roomlist = $sync_data['rooms']['join'];
            echo "Room list : ";
            $this->printArray($roomlist);
            foreach($roomlist as $roomid =>$roomcontent){
                echo "roomid".$roomid."<br>";
                $events = $roomcontent['timeline']['events'];
                //$this->printArray($events);
                foreach($events as $event){
                    if($event['type']=="m.room.message"){
                        echo $event['sender']. " vs ". $this->serverid->serverusername."<br>";
                        // Here we ignore the server user name because he actually represent
                        // the remote sms user. Here we only care about people in the room
                        // answering to him
                        if(strcmp($event['sender'],$this->serverid->serverusername)==0)
                            continue;
                        echo $event['sender']."<br>";
                        
                        if($event['content']['msgtype']=="m.text"){
                            $message = $event['content']['body'];
                            echo "Message=".$message."<br>";
                            echo "Venant de la piece : <pre>";
                            echo "roomid".$roomid."<br>";
                            $correspondants = $this->dbmanager->getSMSChatCorrespondantsFromRoomID($roomid);
                            var_dump($correspondants);
                            //$roomaliases = $this->rooms->listLocalRoomAlias($roomid);
                            //print_r($roomaliases);
                            if(!is_null($correspondants)){
                               
                                echo "Message repondu au numero ".$correspondants['fromnumber']."<br>";
                                echo "Envoye par ". substr($event['sender'],1,strpos($event['sender'],':')-1)."<br>" ;
                                echo "Message :".$message."<br>";
                                $this->voipms->sendSMS($message,$correspondants['fromnumber']);
                                
                                }
                            }
                            echo "</pre><br>";
                        }
                        
                    }
                }
            }
        
        
        //[rooms][join][!NeJIKdjtgnBGnmvAJk:matrix.moduloinfo.ca](id salle)[timeline][events][0...123]
        //[type] => m.room.message
        /*[content] => Array
            (
                [msgtype] => m.text
                [body] => 9 X 0 = 0
            )*/ 
        echo "next_batch".$sync_data['next_batch'];
        file_put_contents($nextbatchfile,$sync_data['next_batch']);

    }

    public function getRoomMessageFilter() {
        return [
            'room' => [
                'timeline' => [
                    'limit' => 50,
                    'types' => ['m.room.message'],
                    'not_types' => [
                        'm.room.member',
                        'm.room.topic',
                        'm.room.name',
                        'm.room.avatar',
                        'm.room.canonical_alias'
                    ]
                ],
                'ephemeral' => [
                    'types' => []
                ],
                'state' => [
                    'types' => []
                ],
                'account_data' => [
                    'types' => []
                ]
            ],
            'presence' => [
                'types' => []
            ],
            'account_data' => [
                'types' => []
            ]
        ];
    }
    private function printArray($myarray){
        echo "<pre>";
        print_r($myarray);
        echo "</pre><br>";
        
    }

}
?>