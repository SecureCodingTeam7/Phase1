<?php 
include 'auth.php';

session_start();
if($_SESSION['status'] != 1){
	header("Location: index.php");
}
?>