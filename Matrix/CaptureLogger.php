<?php
class CaptureLogger{
    private string $logfilename;
    private $fileHandle;
    private bool $echooutput;
    public function __construct($logfilename,$echooutput=false){
        $this->logfilename = $logfilename;
        $this->echooutput = $echooutput;
    }
    public function startCapture(){
        $this->fileHandle = fopen($this->logfilename ,'w');
        $callback = function ($buffer)
        {
          fwrite($this->fileHandle,$buffer);
          if($this->echooutput)
            return $buffer;
        };
        ob_start($callback);
    }

    public function endCapture(){
        ob_end_flush();
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }
    }

}
?>