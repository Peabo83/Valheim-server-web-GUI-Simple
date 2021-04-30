# Valheim-Server-Web-GUI-Simple (V1.01 4/25/2021)

## Features
- Web page that publicly shows the status of valheimserver.service
- Has a public facing Copy to clipboard button for easy pasting into Valheim
- Can show (publicly or not) the Seed of the running world with a custom link to http://valheim-map.world/
### When Logged in
- Turn off/on the valheimserver.service process
- Download a copy of your .DB and .FWL files

**This GUI works/looks best in Chrome, ironing out non-Chrome errors is on the to-do list**

## Credits
Simple no database login from https://gist.github.com/thagxt/94b976db4c8f14ec1527<br>
This would not work without https://github.com/Nimdy/Dedicated_Valheim_Server_Script

## Screenshots
![alt text](https://i.imgur.com/wwmZNAx.jpg)<br>
<br>
![alt text](https://i.imgur.com/Bgi12YX.jpg)<br>

## Install instructions
These instrcutions assume you are working on Ubuntu server as outlined by Nimdy.

1) Follow Nimdy's instuctions for setting up and configuring your Valheim server ( https://github.com/Nimdy/Dedicated_Valheim_Server_Script#readme )

If you did not enable HTTP when creating the VM you will need to enable it.

Per the GCP help file: https://cloud.google.com/vpc/docs/special-configurations
```
If you already have existing default-http and default-https firewall rules, you can apply the firewall rule to existing instances by enabling the Allow HTTP or Allow HTTPS options on the instance's details page.

Go to the VM instances page.
Click the name of the desired instance.
Click Edit button at the top of the page.
Scroll down to the Firewalls section.
Check the Allow HTTP or Allow HTTPS options under your desired VPC network.
Click Save.
```

2) Install PHP and Apache2

```
sudo apt install php libapache2-mod-php
```

Verify that the install was successful by putting the IP of the server in your web browser. You should see the default Apache2 Ubuntu page. If you have connection issues with this default page, you should verify that HTTP is enabled on the VM.

3) Remove the default html folder from /var/www/ and then install repository to /var/www/ then set appropriate permissions.

```
sudo rm -R /var/www/
cd ~
git clone https://github.com/Peabo83/Valheim-Server-Web-GUI.git
sudo cp -R ~/Valheim-Server-Web-GUI/www/ /var/
sudo chown -R www-data /var/www/
sudo chown -R :www-data /var/www/
```

Now when visting the IP of the server you should see the main GUI screen.

4) Change the default username/password/hash keys. Using your preferred text editor open /var/www/VSW-GUI-CONFIG, you will see the inital section with the variables to change:
```
// *************************************** //
// *              VARIABLES              * //
// *************************************** //
	$username = 'Default_Admin';
	$password = 'ch4n93m3';
	$random1 = 'secret_key1';
	$random2 = 'secret_key2';
	$hash = md5($random1.$pass.$random2); 
	$self = $_SERVER['REQUEST_URI'];
	$make_seed_public = false;
        $server_log = true;
```
Change $username and $password to your preffered values. Change $random1 and $random2 to any variables of your choice, like 'Valheim365' and 'OdinRules'.

5) To execute systemctl commands the PHP user (www-data) needs to be able to run systemctl commands, which by default it can not. The following will allow www-data to run the specific commands used to make the GUI work.

```
sudo visudo
```
This will open your sudo file, add the following at the bottom:

```
# Valheim web server commands
www-data ALL = (root) NOPASSWD: /bin/systemctl restart valheimserver.service
www-data ALL = (root) NOPASSWD: /bin/systemctl start valheimserver.service
www-data ALL = (root) NOPASSWD: /bin/systemctl stop valheimserver.service
www-data ALL = (root) NOPASSWD: /bin/grep
```

Then hit <kbd>CTRL</kbd> + <kbd>X</kbd> to exit VI, you will be prompted to save, so press <kbd>Y</kbd>. VI will then ask where to save a .tmp file, just hit <kbd>Enter</kbd> again. After you save the .tmp visudo will check the file for errors, if there are none it will push the content to the live file automatically.


-----------------
Notes for updating<br>

##Hide Apache Version and Operating System
/etc/apache2/apache2.conf

added

ServerSignature Off 
ServerTokens Prod

##Disable Directory Listing and FollowSymLinks

/etc/apache2/apache2.conf

EDIT - add the "-" in options

<Directory /var/www/>
        Options -Indexes -FollowSymLinks
        AllowOverride None
        Require all granted
</Directory>

Also to Disable TRACE HTTP Request ADD:

TraceEnable off


##mod_security and mod_evasive

sudo apt install libapache2-mod-security2 -y
sudo systemctl restart apache2

sudo apt install libapache2-mod-evasive -y
sudo systemctl restart apache2




