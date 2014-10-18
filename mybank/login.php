<?php

include 'crypt.php';

if($_POST['submit'])
{
	session_start();
	$error="";

	$email = $_POST['email'];

	$entered_passwd = $_POST['passwd'];




	if (empty($_POST['email']) || empty($_POST['passwd'])) {
		$error = "Username or Password is invalid";
	}
	else{
		if(!$handler = new mysqli("localhost", "root", "samurai", "mybank")){
			exit("Verbindungsfehler: ".mysqli_connect_error());
		}





		if($stmt = $handler->prepare("SELECT passwd, is_employee, is_active FROM users WHERE email = ? ")){
			
			if(!$stmt->bind_param("s", $email)){
				$error = "can't bind params";
			}

		    if(!$stmt->execute()){
		    	$error =  "can't execute";
		    }
		    
			
			
		    if(!$stmt->bind_result($digest, $employee , $active)){
		    	$error = "can't bind results";
		    }
		 	
		
			if(($stmt->fetch()) && (crypt($entered_passwd,$digest) === $digest)) {
				echo "correct password";
					
					
				if($active){
					$_SESSION['login_user']=$email;


					$_SESSION['status']=$employee;

					if($_SESSION['status']){
						//redirect to employee.php
						echo "Employee login";
					}
					else{
						//redirect to client.php
						echo " Client login";
					}
				}

				else{
					$error = "Registration not confirmed yet";
				}
			}
			else {
				$error = " invalid Password/Username";
			}
			$stmt->close();
		}
		else{
			$error =  "could not prepare statement";
		}
		$handler->close();

	}


}




?>





