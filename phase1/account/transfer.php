<?php
include_once(__DIR__."/../class/c_user.php");
include_once(__DIR__."/../include/helper.php");
$loginPage = "../login.php";
$loginRedirectHeader = "Location: ".$loginPage;
session_start();
if ( !isset($_SESSION['user_email']) || !isset($_SESSION['user_level']) || !isset($_SESSION['user_login']) ) {
    echo "Session Invalid. <a href='$loginPage'>Click here</a> to sign in.";
    
    /* No Session -> Redirect to Login */
    //header($loginRedirectHeader);
} else if ( $_SESSION['user_email'] == "" || $_SESSION['user_level'] == "" || $_SESSION['user_login'] == "") {
	echo "Empty Session Data. <a href='$loginPage'>Click here</a> to sign in.";
	
	/* Destroy Session */
	$_SESSION = array();
	session_destroy();
	
	/* Session Data Invalid -> Redirect to Login */
	//header($loginRedirectHeader);
} else {
	/* Session Valid */
	$user = new User();
	$selectedAccount = "none";
	$transferSuccess = 0;
	$transferMessage = "";
	$requiredTAN = -1;
	$user->getUserDataFromEmail( $_SESSION['user_email'] );
	
	if ( isset( $_SESSION['selectedAccount'] ) ) {
		$selectedAccount = $_SESSION['selectedAccount'];
		
		if ( isset( $_POST['creditTransfer'] ) ) {
			//echo $_POST['amount'];
			//echo $_POST['destination'];
			//echo $_POST['tan'];
			
			try {
				if( $user->transferCredits( $_POST, $selectedAccount ) ) {
					$transferSuccess = 1;
					$transferMessage = "Successfully transferred " .$_POST['amount']. " Eur to " .$_POST['destination'];
				} else {
					$transferSuccess = -1;
					$transferMessage = "Transfer Failed.";
				}
			} catch (Exception $e) {
				$transferMessage = $e->errorMessage();
			}		
		}
		
		if ( isset( $_POST['file'] ) ) {
			//$_FILES['file']['tmpname'] )) {
			echo "File uploaded...";
		}
	}
?>
<!doctype html>
<html>
<head>
	<title>Phase1: Credit Transfer</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="../style/style.css" type="text/css" rel="stylesheet" />
	<link href="../style/pure.css" type="text/css" rel="stylesheet" />
</head>
<body>
	<div class="content">
		<div class="top_block header">
			<div class="content">
				<div class="navigation">
					<a href="index.php">Account</a>
					Transfer
					<a href="history.php">History</a>
				</div>
				
				<div class="userpanel">
					<?php echo $_SESSION['user_email'] ?>
					<a href="logout.php">Logout</a><br />
					<?php 
					if ($selectedAccount > 0) {
					echo "Account: ".$selectedAccount;	
					} else {
					echo "Account: none";
					}
					?>
				</div>
			</div>
		</div>
		
		<div class="main">
		<?php 
			if (!isset( $_SESSION['selectedAccount'] )) {
				echo "No account is active at the moment.<br />";
				echo "You can set the active account on the <a href=\"index.php\">Overview page</a>.";
			} else {
				echo "Credit Transfer for Account #".$selectedAccount;
			?>
		<form method="post" action="" class="pure-form pure-form-aligned">
		    <fieldset>
		        <div class="pure-control-group">
		            <label for="destination">Destination</label>
		            <input id="destination" name="destination" type="text" placeholder="Destination" required>
		        </div>
		
		        <div class="pure-control-group">
		            <label for="amount">Amount</label>
		            <input id="amount" name="amount" type="text" placeholder="Amount in Eur" required>
		        </div>
		        
        		<div class="pure-control-group">
        		<label for="amount">TAN #<?php echo $user->getNextTAN( $selectedAccount ); ?></label>
            		<input id="tan" name="tan" type="text" placeholder="**" required>
        		</div>
		
		        <div class="pure-controls">
		            <button type="submit" name="creditTransfer" class="pure-button pure-button-primary">Transfer</button>
		        </div>
		    </fieldset>
		</form>
		
		<form><fieldset>
		<form action="" method="post" enctype="multipart/form-data">
			<input type="file" name="file"><br>
			<input type="submit" value="upload">
		</fieldset></form>
		<?php
			echo $transferMessage;
		}
		?>
		</div>
		</div>
	</div>
</body>
</html>

<?php
}
?>
