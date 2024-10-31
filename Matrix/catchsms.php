<?php
include_once 'CaptureLogger.php';
$capturelogger = new CaptureLogger('catchsmslog.html',$echooutput=true);//,$echooutput=true);
$capturelogger->startCapture();
include_once 'ConversationManager.php';
$conversation = new ConversationManager();
//https://mysite.com/sms.php?to={TO}&from={FROM}&message={MESSAGE}&files={MEDIA}&id={ID}&date={TIMESTAMP}
$report = "to ".$_GET['to']."\n";
$report .= "from ".$_GET['from']."\n";
$report .= "message ".$_GET['message']."\n";
$report .= "files ".$_GET['files']."\n";
$report .= "date ".$_GET['date']."\n";
//echo $report;
$filename = dirname(__FILE__)."/catchedsms.txt";
//echo $filename;
file_put_contents($filename,$report);
$SMS = [
    'to' => $_GET['to'],
    'from' => $_GET['from'],
    'message' => $_GET['message'],
    'files' => $_GET['files'],
    'date' => $_GET['date']
];
$fakeSMS = [
    'to' => '5814770415',
    'from' => '7377177777',
    'message' => 'ffffe',
    'files' => '{MEDIA}',
    'date' => '2024-10-28 10:59:07',

];
// Here you can use the Fake SMS for tests 
$conversation->receiveSMS($SMS);
$capturelogger->endCapture();
echo "OK";
?>