<?php
include_once 'databasemanager.php';
class LogManager{
	public string $logmanagerfolder;
	private $currentlogcontent;
	private $logfor;
	private $databasemanager;
	private $echoresult;
	private $tablevel;
	private $logmanagerpath;
	private $logfile;

	
	function __construct(string $logfor,int $iduser=-1){
		//echo "logiduser=".$iduser;
		$this->currentlogcontent = "";
		$this->logfor = $logfor;
		$this->databasemanager = new DatabaseManager();
		$this->echoresult = True;
		$this->tablevel = 0;
		
		$filename = $this->sanitizefilename("");
		if ($iduser != -1) $filename .= "id".strval($iduser);
		$this->logmanagerpath = dirname(__FILE__)."/logs/".$filename.$logfor."log.txt";
		//echo "<br>".$this->logmanagerpath."<br>";
		$this->logfile = fopen($this->logmanagerpath, "a") or die("Unable to open file!");

	}
	function getCurrentlog(){
		return $this->currentlogcontent;
		
	}
	function islogempty(){
		if(!file_exists($this->logmanagerpath))
			return True;
		
		if(filesize($this->logmanagerpath)==0)
			return True;
		
		return False;
		
	}
	function cleanlog(){
		fclose($this->logfile);
		file_put_contents($this->logmanagerpath, "");
		
	}
	public static function deletealllogs() {
		$fullPath = dirname(__FILE__)."/logs/";
		array_map('unlink', glob( "$fullPath*.txt"));
		echo _("Tous les journaux ont été effacés.");
	}
	
	function displaylog(){
		if($this->islogempty()){
			echo _("Journal vide");
			return;
		}
		//echo getcwd();
		ob_start();
		echo "<br><A href='#".$this->logfor."endlog' id='".$this->logfor."'>"._("Aller à la fin du journal")."</A><br>";
		include  $this->logmanagerpath;
		echo "<A id='".$this->logfor."endlog'></A>";
		$string = ob_get_clean();
		$string = str_replace("\n","<br>\n",$string);
		$string = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp",$string);
		echo $string;
		echo "<br><A href='#".$this->logfor."' id='".$this->logfor."'>"._("Aller au début du journal")."</A><br>";
		
	}
//pb240514: je met en comm la vieille fonction et je met celle de mon cru
// 	function writelinetolog($line){
// 	    $this->currentlogcontent .= $line."\n";
// 	    fwrite($this->logfile, $this->displaytabs().$line."\n");
// 		if($this->echoresult)
// 			$line = str_replace("\n","<br>\n",$line."\n");
// 			$line = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp",$line);
// 			return $line;
// 	}
	function writelinetolog($line){
		$this->currentlogcontent .= $line."\n";
		fwrite($this->logfile, $this->displaytabs().$line."\n");
		if ($this->logfor == "genstatistics"):
			echo $this->displaytabs().$line."\n";
		elseif ($this->echoresult):
			$line = str_replace("\n","<br>\n",$line."\n");
			$line = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp",$line);
		endif;
		return $line;
	}
	function writeseparator(){
		$this->writelinetolog("_______________________________________________________\n\n");
	}
	function trimandcloselog(){
		$this->writeseparator();
		$maxlines = 10000;
		fclose($this->logfile);
		// Coupe le log à la longueur maxlines en enlevant les lignes au début du fichier
		// Trim log at maxlines length deleting lines at the beginning of the file
		$lines = file($this->logmanagerpath);
		if(count($lines)> $maxlines){
			$linesover = abs($maxlines - count($lines));
			$trimmedlines = array_slice($lines,$linesover,count($lines));
			file_put_contents($this->logmanagerpath, implode("", $trimmedlines));
			
		}
	}
	function sanitizefilename($filename){
		// Remove anything which isn't a word, whitespace, number
		// or any of the following caracters -_~,;[]().
		// If you don't need to handle multi-byte characters
		// you can use preg_replace rather than mb_ereg_replac
		$filename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);
		// Remove any runs of periods 
		$filename = mb_ereg_replace("([\.]{2,})", '', $filename);
		return $filename;
	}
	function displaytabs(){
		$returnstring = "";
		for ($x = 0; $x < $this->tablevel; $x++){
			$returnstring .= "\t";
			
		}
		return $returnstring;
		
	}
	function incrementtab(){
		$this->tablevel += 1;
	}
	function decrementtab(){
		$this->tablevel -= 1;
		if ($this->tablevel < 0)  $this->tablevel = 0;
	}
}


?>
