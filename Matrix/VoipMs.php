<?php
include_once('ServerIdentity.php');
class VoipMs{

    
    public function sendSMS($message,$destinationnumber){
        //echo "  Entree fonction: sendSMS\n";
        //Assure qu'aucun caractère indésirable se retrouve dans la requête GET
        //$message = filter_var($message, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        $serverid = new ServerIdentity();
        $query_params = array(
            'api_username' => $serverid->api_username,
            'api_password' => $serverid->api_password,
            'method' => "sendSMS",
            'did' => $serverid->did,
            'dst' => $destinationnumber,
            'message' => $message,
        );
        
        $method = "sendSMS" ;
        $curlurl = "https://voip.ms/api/v1/rest.php?";
        $curlurl .= http_build_query($query_params);
        
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_URL, $curlurl);
        $result = curl_exec($ch);
        //$connectionerror = $this->connectionError($iduser,$ch,"sendSMS");
        //if($connectionerror) return $connectionerror;
        $data= json_decode($result,true);
        echo $data["status"]. _(" pour le numéro de destination : ").$destinationnumber."\n";
        curl_close($ch);
        return $data["status"];
        
    }
    
}
?>