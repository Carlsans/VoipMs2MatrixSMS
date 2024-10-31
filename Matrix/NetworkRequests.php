<?php
include_once 'ServerIdentity.php';

class NetworkRequests{
    private ServerIdentity $serverid;
    private bool $debug;

    public function __construct(){
        $this->serverid = new ServerIdentity();
        $this->debug = true;
    }
    private function log($message){
        if ($this->debug) {
            echo "[DEBUG] " . $message . "<br>";
        }
    }
    public function request(string $method, string $endpoint,array $data = null, string $userId = null,bool $usingtimeout = false) {
        $ch = curl_init();
        
        $separator = (strpos($endpoint, '?') === false) ? '?' : '&';
        $url = $this->serverid->homeserverUrl . $endpoint ;
        echo "URL:".$url;
        $headers = [
            'Authorization: Bearer ' . $this->serverid->usertoken,
            'Content-Type: application/json'
        ];
        if ($userId !== null) {
            $headers[] = 'User-Id: ' . $userId;
        }
        
        $this->log("Making $method request to: $url");
        $this->log("Headers: " . json_encode($headers));
        if ($data !== null) {
            $this->log("Data: " . json_encode($data));
        }
        // Note $usingtimeout devrait determiner CURLOPT_TIMEOUT = 35
        $curlsetoptarray = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 35
        ];
        
        curl_setopt_array($ch,  $curlsetoptarray);
       
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        $this->log("Response Code: $httpCode");
        $this->log("Response: $response");
        //var_dump($response);
        
        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        $responseData = json_decode($response, true);
        
        if ($httpCode >= 400) {
            if ($httpCode == 404 && json_decode($response,true)['errcode']=="M_NOT_FOUND")
                return false;
            throw new Exception("API error: HTTP $httpCode - " . ($responseData['error'] ?? $response));
        }
        
        return $responseData;
    }


}
?>
