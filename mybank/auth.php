<?php
session_start();

if(!isset($_SESSION['user']) ||!isset($_SESSION['status'])){
	header("Location: login.php");
	exit();

	
}
?>