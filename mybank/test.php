
<html>
  <head>
 <link rel="stylesheet" href="zerotype/css/style.css" type="text/css" media="screen" />
    <title>Login</title>
    <meta content="">
    <style></style>
  </head>
  <body>
  

<?php

// function rand_sha1($length) {
// 	$max = ceil($length / 40);
// 	$random = '';
// 	for ($i = 0; $i < $max; $i ++) {
// 		$random .= sha1(microtime(true).mt_rand(10000,90000));
// 	}
// 	return substr($random, 0, $length);
// }

// function randomPassword($length) {
	
	
// $alphanum = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
// $special  = '~!@#$%^&*(){}[],./?';
// $chars = $alphanum . $special;
// 	$size = strlen( $chars );
// 	for( $i = 0; $i < $length; $i++ ) {
// 		$str .= $chars[ rand( 0, $size - 1 ) ];
// 	}
	
// 	return $str;
	
// }

// function generateTans(){
// 	if(!$db = mysqli_connect("localhost", "root", "samurai", "mybank")){
// 		die("connect() failed");
// 	}

	
// // 	$select = $db->prepare("SELECT * FROM trans_codes WHERE code = ?");
// // 	$select -> bind_param("s",$code);
// 	if(!$insert = $db->prepare("INSERT INTO trans_codes (user_id,code_number,code) VALUES (? , ?, ?)")){
// 		die("prepare() failed: ");
// 	}
	
// 	if(!$insert->bind_param("iis",$user_id,$count,$code)){
// 		die("bind_parame() failed: ".$insert->error());
// 	}
	
// 	$count = 0;
// 	while($count<100) {
// 		$code = randomPassword(15);
// // 		$select-> execute();
// 		if(false){
// 			//transcode already exists
// 			echo " found same code in db";
// 			continue;
// 		}
// 		else
// 		{
// 			echo " <p> Number $count:  $code"."</p>";
// 			 if(!$insert->execute()){
// 			 	die('execute() failed: '. htmlspecialchars($insert->error));
// 			 }
			
// 			$codes.= $code." ";

// 			//save trans_code in db
// 			$count++;
// 		}
			

// 	}

	
	
// 	$insert->close();
// 	$db->close();


// }

header("Location: index.php");
?>

</body>
</html>