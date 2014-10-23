
   <?php
include('auth.php');  

if(isset($_SESSION['user']) && isset($_SESSION['status'])){
	echo $_SESSION['user'];
	echo $_SESSION['status'];
	if($_SESSION['status']==1){
		header("Location: employee.php");
		exit();
	}
	else if($_SESSION['status']==0){
		header("Location: client.php");
		exit();
	}

	else{
		die("Error with Session Key");
	}
	
}

?>

 
  
  



