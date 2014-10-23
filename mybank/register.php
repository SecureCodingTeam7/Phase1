
<html>
<head>
<title>Registration</title>
<meta content="">
<style></style>
</head>
<body>
	<h1>Please Enter all required Information</h1>
	<?php

	include 'crypt.php';
	if($_POST['submit']){
		$error="";
			
		if (!$_POST['email']){
			echo " Please enter your email adresse";
		}
		else if($_POST['status']==-1){
			echo " Please Select your Status";
		}
			
		else if(!$_POST['passwd']){
			echo " Please enter a password";
		}
		else{
			$email =$_POST['email'];  #muss geparst werden
			$passwd=$_POST['passwd'];
			$status =$_POST['status'];
			$confirm_passwd=$_POST['confirm_passwd'];


			$uppercase = preg_match('@[A-Z]@', $passwd);
			$lowercase = preg_match('@[a-z]@', $passwd);
			$number    = preg_match('@[0-9]@', $passwd);

			if(!$uppercase || !$lowercase || !$number || strlen($passwd) < 8) {
				echo " password not secure ";
			}
			#compare passwords
			else if($passwd!=$confirm_passwd){
				echo "You entered different passwords";
			}



			else{

				$db = mysqli_connect("localhost", "root", "samurai", "mybank") or exit(mysql_error());
					
				$stmt = $db->prepare("SELECT * FROM users WHERE email = ? ") or exit(mysql_error());
					
				if(!$stmt->bind_param("s", $email)){
					$error = "can't bind params";
				}
					
				if(!$stmt->execute()){
					$error =  "can't execute";
				}
					

				if($stmt->fetch()){
					echo "Account exists already";
				}else{
					$passwd_hash= better_crypt($passwd);
					$insert_stat = $db->prepare("INSERT INTO users (email,passwd,is_employee) VALUES (? , ?, ?)") or die(mysql_errno());
					$insert_stat->bind_param("ssi", $email,$passwd_hash,$status);
					$insert_stat->execute();
					echo " Successfully signed up";

					//creation of trans_codes and send email;

				}

				$stmt->close();

			}
		}
	}

	?>

	<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<p>
			Your Email: <input type="text" name="email" />
		</p>
		<p>
			<select name="status" size="1">
				<option value="-1">Your Status</option>
				<option value="1">Employee</option>
				<option value="0">Client</option>
			</select>
		</p>
		<p>
			Your Password: <input type="password" name="passwd" />
		</p>
		<p>
			Confirm Password: <input type="password" name="confirm_passwd" />
		</p>

		<p>
			<input type="submit" name="submit" value="Register" />
		</p>
	</form>
</body>
</html>

