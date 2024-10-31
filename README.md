This is a php program used to forward message to and from a voip.ms ip phone provider to Matrix rooms.
Each time a new person sms your number, a new matrix room is created.
Persons from the team you decide are invited into the room and if they reply, the reply is sent via sms back to the caller.
This come with no warranty at all and it does not support end to end encryption.
The program consist of 2 parts.
- Listener
 The program need to actualize itself every 30 seconds. It use matrix lazyloading to consider only pertinent information (the messages), it does not take much ressources, hardly can see it using top.
 A cron task need to be programmed to make sure the program is running.
 Here is the suggested crontab, it need to run each 10 minutes ! :
*/10 * * * * /usr/local/bin/php -q /home/yourwebsite/public_html/yourfolder/Matrix/MatrixListener.php
I would suggest running your cron command line using ssh to be sure everything is all right, much easier that trying to guess.
Note, this program is designed not to be called twice at the same time. If you want to run it many time in less that 10 minutes, delete timerupdate.txt or go get a coffee.
 For the message to be forwarded back to the caller, you will need to enable and configure voip.ms api.
 The matrix user you need to create will represent the caller. You can call him voip.ms or anything that will be easy to catch for your team. You can create it has you would create a normal user except you will need to get his user token that start with syt_
 DO NOT put your matrix user in your team, doing so will result in errors. If you try messenging directly with your matrix user, it will not be sent back to voip.ms insuring you dont get into an infinite loop.
 I tested the code with a homeserver and it went fine as long as the user came from my server only. I tested using matrix.org server and the program would work only for matrix.org users. I think it is due to my homeserver config being not adequate, but its worth mentionning.
 I also tested the php code on my personnal server and on another server I have access and everything went well. Be sure your listener code is working before attempting anything. It should give you some giberish from matrix api. Also you can try opening catchsms.php with some fake arguments, if something wrong, it will throw an error. Watch out your folders in your ftp, some log files are created there.
 


- Voip.ms webhook catcher
 Catch an SMS event coming from your voip.ms provider. In your manage did edit section, you can add this address in the SMS/MMS URL Callback box:
 https://yourwebserver.com/yourfolder/Matrix/catchsms.php?to={TO}&from={FROM}&message={MESSAGE}&files={MEDIA}&id={ID}&date={TIMESTAMP}
 you can also call it directly from your browser without arguments, it will give you an error but you will know it's there.

 The file containing all the configuration must be filled completely. It is in /Matrix/serveridcredsempty.json and you MUST rename it serveridcreds.json

 There is no other config than this file.
NOTE : Better clone the project or read the file in a text editor.
Here is a description of what everything do :

{
    "servername":"myserver.com", # Here is the matrix server of your main user. Tested on home server, should work using matrix.org server too.
    "usertoken":"syt_XXXXXXXXXXXXXXXXXXXXXXX", # Your matrix user token, you must create a user using a regular client and fetch the token. In element desktop you can do so by going over your name -> All Settings -> (lowest left option) Help and about -> (down below) Access token in green.
    "serverusername":"@username:myserver.com", # Your matrix user username
    "secretnamegenerator" : "Any secret string of you choosing here", # Passphrase for the automatic name generator. put anything you like here.
    "dbservername" : "localhost", # Should stay like that
    "dbusername" : "yourDBUsername", # You mysql database username
    "dbpassword" : "yourusernamedatabasepassword", # Your db username password
    "dbname" : "databasenamethatyoucreatedandauthorized", # Your database name, authorized to your database user
    "teammembers" : "@carl:matrix.org,@charly:matrix.org,@peter:matrix.org", # All the members of your team. They will answer the SMSs.
    "api_username" : "myvoipusername@gmail.com", # Your voip.ms account email.
    "api_password" : "myvoipapipassword", # You api password, not your regular password. You must enable it in the voip.ms api section. You must also whitelist your server ip address. The server is not always what it look like so you can use the 0.0.0.0 address that make the api accept any ip address. You should correct that afterward.
    "did" : "2345678900", # The voip.ms did (phone number) that will receive and send the sms.
    "usefakenames" : false # Determine if the Rooms created for each sms you receive will have a name corresponding to the phone number or a unique fake name.
}
