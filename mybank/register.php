
<html>
<head>
<title>Registration</title>
<meta content="">
<link rel="stylesheet"
	href="http://yui.yahooapis.com/pure/0.5.0/pure-min.css">
<style type="text/css"></style>
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
			
			
			//trim input
			$email = trim($_POST['email']);
			$passwd = trim($_POST['passwd']);
			$status = trim($_POST['status']);
			$confirm_passwd = trim($_POST['confirm_passwd']);

			

			$uppercase = preg_match('@[A-Z]@', $passwd);
			$lowercase = preg_match('@[a-z]@', $passwd);
			$number    = preg_match('@[0-9]@', $passwd);

			
			
			
// 			$regex = "^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$";
			
// 			if ( !preg_match( $regex, $email ) ) {
// 				echo $email . " is a not valid email";
// 			}
			//TODO display rules for password
			 if(!$uppercase || !$lowercase || !$number || strlen($passwd) < 8) {
				echo " password not secure ";
			}
			#compare passwords
			else if($passwd!=$confirm_passwd){
				echo "You entered different passwords";
			}



			else{
				
				

				if(!$db = mysqli_connect("localhost", "root", "samurai", "mybank")){
					die("connect() failed");
				}
					
				if(!$stmt_get_user = $db->prepare("SELECT * FROM users WHERE email = ? ")){
					die("prepare() failed: ".$stmt_get_user->error);
				}
					
				if(!$stmt_get_user->bind_param("s", $email)){
					die("bind_param() failed: ".$stmt_get_user->error);
				}
					
				if(!$stmt_get_user->execute()){
					die("execute() failed: ".$stmt_get_user->error);
				}
				

				if($stmt_get_user->fetch()){
					echo "Account exists already";
				}else{
					
					
					$passwd_hash= better_crypt($passwd);

					
					if(!$stmt_insert_user = $db->prepare("INSERT INTO users (email,passwd,is_employee) VALUES (? , ?, ?)"))
					{
						die("prepare() failed: ".$stmt_insert_user->error);
					}


					if(!$stmt_insert_user->bind_param("ssi", $email,$passwd_hash,$status)){
						die("bind_param() failed: ".$stmt_insert_user->error);
					}
					if(!$stmt_insert_user->execute()){
						die("execute() failed: ".$stmt_insert_user->error);
					}
					if(!$stmt_insert_user->close()){
						die("close() failed: ");
					}


					//TODO redirect to page
					echo " Successfully signed up";

					//creation of trans_codes and send email

					//get id of created entry
					if(!$stmt_get_id = $db->prepare("Select id FROM users WHERE email = ?")){
						die("prepare() failed: ".$stmt_get_id->error);
					}

					if(!$stmt_get_id->bind_param("s",$email)){
						die("bind_param() failed: ".$stmt_get_id->error);
					}

					if(!$stmt_get_id->execute()){
						die("execute() failed: ".$stmt_get_id->error);
					}

					if(!$stmt_get_id->bind_result($id)){
						die("bind_result() failed: ".$stmt_get_id->error);
					}


					// has to be successful because row with this email was just added
					if(!$stmt_get_id->fetch()){
						die("fetch() failed: ".$stmt_get_id->error);
					}
					
					$stmt_get_id->close();


					generateTans($id);


				}



				$stmt_get_user->close();
				$db->close();

			}
		}
	}



	function randomPassword($length) {


		$alphanum = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$special  = '~!@#$%^&*(){}[],./?';
		$chars = $alphanum . $special;
		$size = strlen( $chars );
		for( $i = 0; $i < $length; $i++ ) {
			$str .= $chars[ rand( 0, $size - 1 ) ];
		}

		return $str;

	}

	function generateTans($user_id){
		

		
		
		if(!$db = mysqli_connect("localhost", "root", "samurai", "mybank")){
			die("connect() failed");
		}

		

		if(!$select = $db->prepare("SELECT * FROM trans_codes WHERE code = ?")){
			die("prepare() failed: ".$select->error);
		}
		if(!$select -> bind_param("s",$code)){
			die("bind_param() failed: ".$select->error);
		}
		
		if(!$insert = $db->prepare("INSERT INTO trans_codes (user_id,code_number,code) VALUES (? , ?, ?)")){
			die("prepare() failed: ".$insert->error);
		}

		if(!$insert->bind_param("iis",$user_id,$count,$code)){
			die("bind_parame() failed: ".$insert->error);
		}

		$count = 0;
		while($count<100) {
			
			$code = randomPassword(15);
			
			if(!$select-> execute()){
				die('execute() failed: '. $select->error);
			}
			if($select->fetch()){
				//transcode already exists
				echo " found same code in db";
				continue;
			}
			else
			{
				if(!$insert->execute()){
					die('execute() failed: '.$insert->error);
				}
					
				$codes.= $code." ";

				
				$count++;
			}
			
			//TODO send email now or after approving registration? 
			header("Location: index.php");


		}


		$select->close();
		$insert->close();
		$db->close();


	}

	?>

	<form class="pure-form pure-form-stacked" method="post"
		action="<?php echo $_SERVER['PHP_SELF']; ?>">
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
			<input type="submit" class="pure-button pure-button-primary"
				name="submit" value="Register" />
		</p>
	</form>
</body>
</html>

