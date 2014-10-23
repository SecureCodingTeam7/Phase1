<html>
  <head>
 

<link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.5.0/pure-min.css">


<meta name="viewport" content="width=device-width, initial-scale=1">



  
    <title>Login</title>
    <meta content="">
    <style
    
    
    ></style>
  </head>
  <body>



<?php

include 'crypt.php';

if($_POST['submit'])
{
	session_start();
	$error="";

	$email = trim($_POST['email']);
	

	$entered_passwd = trim($_POST['passwd']);
	



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
					$_SESSION['user']=$email;
					$_SESSION['status']=$employee;

					if($_SESSION['status']){
						//redirect to employee.php
						echo "Employee login";
						header("Location: employee.php");
					}
					else{
						//redirect to client.php
						echo " Client login";
						header("Location: client.php");
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


 <form class= "pure-form pure-form-stacked" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" >
  <fieldset>
  <legend>Login</legend>
  
  <div class='container'>
  <label for='email'>Email Adress: </label>
  <input type="text" name="email" />
   <span id='login_username_errorloc' class='error'></span>
   </div>
  <div class='container'>
  <label for='password'>Password: </label>
  <input type="password" name="passwd" />
  <span id='login_password_errorloc' class='error'></span>
  </div>
  
  <div class='container'>
  <input type="submit" class="pure-button pure-button-primary" name ="submit" value="Login" />
  </div>
  </fieldset>
  </form>

 <p><?php echo $error ?> </p>

 <a href="register.php">Or Register Here</a>


 

  </body>
</html>




