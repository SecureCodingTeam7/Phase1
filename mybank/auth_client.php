<?php 
include 'auth.php';

session_start();
if($_SESSION['status'] !=0){
	header("Location: index.php");
}
?>