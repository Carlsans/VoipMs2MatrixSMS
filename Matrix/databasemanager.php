<?php
include_once 'ServerIdentity.php'; 
class DatabaseManager {
	private $servername;
	private $username;
	private $password;
	private $dbname;
	private $conn;
	private $serverid;
	function __construct() {
		$this->serverid = new ServerIdentity();
		$this->servername = $this->serverid->dbservername;
		$this->username = $this->serverid->dbusername;
		$this->password = $this->serverid->dbpassword;
		$this->dbname = $this->serverid->dbname ;
		// Create connection
		$this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
		// Check connection
		if ($this->conn->connect_error) {
			die("<h1>Connection failed: " . $this->conn->connect_error . "</h1>");
			
		}
		$sql = "SHOW TABLES LIKE 'smschatrooms'";
		$result = $this->conn->query($sql);
		// If the table does not exist :
		if (!mysqli_fetch_array($result)){
			$this->createChattables();
		}	
	}	
	public function testandrefreshdb(){
		if($this->conn->error == "MySQL server has gone away" ){
			echo "db connection problem, trying to renew now....";
			$this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
		}
		
	}
	private function displaytable($tablename){
		$sql = "SELECT * FROM `$tablename`";
		$result = $this->conn->query($sql);
		$array = [];
		while($row = mysqli_fetch_assoc($result)) {
			$array[] = $row;
		  }
		
		echo "<pre>";
		print_r($array);
		echo "</pre>";
	}

	public function createChattables(){
		echo "Creating Chat tables";
		$sql = "SHOW TABLES LIKE 'smschatrooms'";
		$result = $this->conn->query($sql);
		if (mysqli_fetch_array($result)){
			echo "TABLE smschatrooms already exist";
			return;
		}
		
		$sql = "
		CREATE TABLE IF NOT EXISTS `smschatrooms` (
			`idsmschatrooms` int(11) NOT NULL,
			`fromnumber` varchar(10) NOT NULL,
			`tonumber` varchar(10) NOT NULL,
			`chatroomid` varchar(60) NOT NULL,
			`scrambledname` varchar(60) NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
		ALTER TABLE `smschatrooms`
			ADD PRIMARY KEY (`idsmschatrooms`);
		ALTER TABLE `smschatrooms`
			MODIFY `idsmschatrooms` int(11) NOT NULL AUTO_INCREMENT;
		COMMIT;
		";
		$this->conn->multi_query($sql);
		do {
		if ($result = $this->conn->store_result()) {
			var_dump($result->fetch_all(MYSQLI_ASSOC));
			$result->free();
		}
		} while ($this->conn->next_result());
	}
	public function getSMSChatRoom($fromnumber,$tonumber){
		//$sql = "DROP TABLE smschatrooms";
		//$result = $this->conn->query($sql);
		//exit();
		$this->displaytable('smschatrooms');
		mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL);
		$sql = "SELECT chatroomid FROM  `smschatrooms` WHERE fromnumber = '$fromnumber' AND tonumber = '$tonumber' ";
		try {
			$result = $this->conn->query($sql);
		} catch (mysqli_sql_exception $e) {
			// Poutine pour obtenir le code d erreur. Si la table n'existe pas on la cree
			$eArr = (array)$e;
    		$sqlstate = $eArr["\0*\0code"];
			//echo "CODE=".$sqlstate ;
			// ici on cree les tables et on rappelle la fonction. Sinon on lance l exception 
			if($sqlstate==1146){
				mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT) ;
				$this->createChattables();
				$this->getSMSChatRoom($fromnumber,$tonumber);
				return;
			}
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT) ;

		}
		// Recall it so we have the results anyways
		$result = $this->conn->query($sql);
		
		if ($result->num_rows == 0) {
			echo _("La salle de chat n'existe pas.");
			return False;	
		}
		return  $result->fetch_assoc()['chatroomid'];

	}
	public function setSMSChatRoom($fromnumber,$tonumber,$chatroomid,$scrambledname){
		
		$sql = "INSERT INTO `smschatrooms`
			SET `fromnumber` = '$fromnumber',
			`tonumber` = '$tonumber',
			`chatroomid` = '$chatroomid',
			`scrambledname` = '$scrambledname'
			";
		$result = $this->conn->query($sql);
		
		return  true;

	}
	public function getSMSChatCorrespondantsFromRoomID($roomid){
		//$this->displaytable('smschatrooms');
		$sql = "SELECT fromnumber, tonumber FROM  `smschatrooms` WHERE chatroomid = '$roomid' ";
		echo "sql".$sql."<br>";
		$result = $this->conn->query($sql);
		return $result->fetch_assoc();
	}

}

?>
