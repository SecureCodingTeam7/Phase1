<!--
   check.php
   
   Copyright 2014 Samurai <samurai@samurai-wtf>
   
   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.
   
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.
   
   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
   MA 02110-1301, USA.
   
   
-->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>untitled</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<meta name="generator" content="Geany 0.21" />
</head>

<body>




 <?php
 
 $email=$_POST['email'];
 $passwd=$_POST['passwd'];
 
 
 if(!mail($email,'SEEES','Hallo')){
	 exit("Fehler beim Versenden der Nachricht");
	 }
 
 $db = mysqli_connect("localhost", "root1", "samurai", "mybank");
if(!$db)
{
 exit("Verbindungsfehler: ".mysqli_connect_error());
}

$insert="INSERT INTO users (email,passwd) VALUES ('$email','$passwd')";

if(!mysqli_query($db,$insert)){
	exit("Fehler beim einfügen in die Datenbank");
}
echo "Erfolgreich";
mysqli_close($db);
?>



</body>

</html>
