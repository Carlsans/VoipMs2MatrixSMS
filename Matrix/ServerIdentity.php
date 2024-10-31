<?php
class ServerIdentity{
    public string $homeserverUrl;
    public string $servername;
    public string $usertoken;
    public string $serverusername;
    public string $secretnamegenerator;
    public string $dbservername;
    public string $dbusername;
    public string $dbpassword;
    public string $dbname;
    public array $teammembers;
    public string $api_username;
    public string $api_password;
    public string $did;
    public bool $usefakenames;
    public function __construct(){
        if(!file_exists(__DIR__.'/serveridcreds.json')){
            echo "Rename SwitchBoard/SwitchBoard/Matrix/serveridcredsempty.json to serveridcreds.json and enter your credentials in the newly renamed file.";
            exit(-1);
        }
        $credential = json_decode(file_get_contents(__DIR__.'/serveridcreds.json'),true);
        $this->homeserverUrl = "https://".$credential['servername'];
        $this->servername = $credential['servername'];
        $this->usertoken = $credential['usertoken']; // carl user token
        $this->serverusername = $credential['serverusername'];
        $this->secretnamegenerator = $credential['secretnamegenerator'];
        $this->dbservername = $credential['dbservername'];
        $this->dbusername = $credential['dbusername']; 
        $this->dbpassword = $credential['dbpassword']; 
        $this->dbname = $credential['dbname'];
        $this ->teammembers = explode(',',$credential['teammembers']); 
        $this->api_username = $credential['api_username'];
        $this->api_password = $credential['api_password'];
        $this->did = $credential['did'];
        $this->usefakenames = $credential['usefakenames'];
   

    }

}

?>