<?php

$email=$_POST['email'];
$passwd=$_POST['passwd'];

$db = mysqli_connect("localhost", "root", "samurai", "mybank");
if(!$db)
{
	exit("Verbindungsfehler: ".mysqli_connect_error());
}

?>