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
			//TODO regex for email adress
			$email = trim($_POST['email']);  
			$passwd = trim($_POST['passwd']);
			$status = trim($_POST['status']);
			$confirm_passwd = trim($_POST['confirm_passwd']);


			$uppercase = preg_match('@[A-Z]@', $passwd);
			$lowercase = preg_match('@[a-z]@', $passwd);
			$number    = preg_match('@[0-9]@', $passwd);

			
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
					
				if(!$stmt = $db->prepare("SELECT * FROM users WHERE email = ? ")){
					die("prepare() failed: ".$stmt->error);
				}
					
				if(!$stmt->bind_param("s", $email)){
					die("bind_param() failed: ".$stmt->error);
				}
					
				if(!$stmt->execute()){
					die("execute() failed: ".$stmt->error);
				}
					

				if($stmt->fetch()){
					echo "Account exists already";
				}else{
					$passwd_hash= better_crypt($passwd);

					if(!$insert_stat = $db->prepare("INSERT INTO users (email,passwd,is_employee) VALUES (? , ?, ?)") or die(mysql_errno())){
						die("prepare() failed: ".$insert_stmt->error);
					}


					if(!$insert_stat->bind_param("ssi", $email,$passwd_hash,$status)){
						die("bind_param() failed: ".$insert_stmt->error);
					}
					if(!$insert_stat->execute()){
						die("execute() failed: ".$insert_stmt->error);
					}
					if(!$insert_stat->close()){
						die("close() failed: ".$insert_stmt->error);
					}


					//TODO redirect to page
					echo " Successfully signed up";

					//creation of trans_codes and send email

					//get id of created entry
					if(!$id_stmt = $db->prepare("Select id FROM users WHERE email = ?")){
						die("prepare() failed: ".$id_stmt->error);
					}

					if(!$id_stmt->bind_param("s",$email)){
						die("bind_param() failed: ".$id_stmt->error);
					}

					if(!$id_stmt->execute()){
						die("execute() failed: ".$id_stmt->error);
					}

					if(!$id_stmt->bind_result($id)){
						die("bind_result() failed: ".$id_stmt->error);
					}


					// has to be successful because row with this email was just added
					if(!$id_stmt->fetch()){
						die("fetch() failed: ".$id_stmt->error);
					}


					generateTans($id);


				}



				$stmt->close();
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

				//save trans_code in db
				$count++;
			}


		}


		$select->close();
		$insert->close();
		$db->close();


	}

	?>