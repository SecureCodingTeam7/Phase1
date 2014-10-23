<?php
include_once(__DIR__."/include/db_connect.php"); 
include_once(__DIR__."/class/c_user.php");

if( !(isset( $_POST['checkRegister'] ) ) ) { ?>
<!doctype html>
<html>
<head>
	<title>Phase1: Register Page</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="style/style.css" type="text/css" rel="stylesheet" />
	<link href="style/pure.css" type="text/css" rel="stylesheet" />
</head>
<body>
	<div class="content">
		<div class="top_block header">
			<div class="content">
				<div class="navigation">
				Register
				<a href="login.php">Login</a>
				</div>
				
				<div class="userpanel">
				</div>
			</div>
		</div>
		
		<div class="main">
		<p>Already have an account? <a href="login.php">Click here</a> to sign in.</p>
	
			<form method="post" action="" class="pure-form pure-form-aligned">
		    <fieldset>
		        <div class="pure-control-group">
		            <label for="email">Email</label>
		            <input name="email" id="email" type="email" placeholder="YourAccount@bank.de" required>
		        </div>
		
		        <div class="pure-control-group">
		            <label for="password">Password</label>
		            <input name="password" id="password" type="password" placeholder="***********" required>
		        </div>
		
		        <div class="pure-controls">
		            <button id="SignInButton" type="submit" name="checkRegister" class="pure-button pure-button-primary">Finish Registration</button>
		        </div>
		    </fieldset>
			</form>

		<?php 
	        if (!defined('PDO::ATTR_DRIVER_NAME')) {
	        	echo '[DEBUG] PDO unavailable';
	        }
	        elseif (defined('PDO::ATTR_DRIVER_NAME')) {
	        	echo '[DEBUG] PDO available';
	        }
	    ?>
		</div>
		</div>
	</div>
</body>
</html>
<?php 
} else {
	echo "checkRegister Post";

	$user = new User();
	echo "<br />[DEBUG] Email: ". $_POST['email'];
	echo "<br />[DEBUG] Password: " . $_POST['password'];
	
	if( $user->register( $_POST ) ) {
		echo "<br />Registration Successful. Go to <a href='login.php'>Sign in</a>.";
	} else {
		echo "<br />Unable to register at this time. Please <a href='register.php'>try again</a>.";
	}
}
?>