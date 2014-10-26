
<?php
    
    include_once(__DIR__."/../class/c_user.php");
    include_once(__DIR__."/../include/helper.php");
    $loginPage = "../login.php";
    $loginRedirectHeader = "Location: ".$loginPage;
    $accountPage = "index.php";
    $accountRedirectHeader = "Location: ".$accountPage;
    
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
    }
    
    else if ( !isset($_SESSION['selectedAccount']) || $_SESSION['selectedAccount'] == "" ) {
        header($accountRedirectHeader);
    }
    else {
        
        /* Session Valid */
        $user = new User();
        $selectedAccount = "none";
        $requiredTAN = -1;
        $uploadMessage = "";
        
        $user->getUserDataFromEmail( $_SESSION['user_email'] );
        
        if ( isset( $_SESSION['selectedAccount'] ) ) {
            $selectedAccount = $_SESSION['selectedAccount'];
            
            
            if(isset($_POST['uploadFile'])){
                
                $name       = "transactionFile";
                $temp_name  = $_FILES['myfile']['tmp_name'];
                if(isset($name)){
                    if(!empty($name)){
                        $location = '../uploads/';
                        if(move_uploaded_file($temp_name, $location.$name)){
                            $uploadMessage = 'uploaded';
                            
                            $command = "./transfer_parser ".$user->id." ".$selectedAccount." ".$user->getNextTan($selectedAccount)." ".$location.$name;
                            
                            if(exec($command)){
                                $uploadMessage=" Transaction succeed";
                                $user->updateNextTan( $selectedAccount);
                                
                                if(!unlink($location.$name)){
                                    echo ("can't delete file");
                                }
                                
                                
                            }
                            else {
                                $uploadMessage = "Please try again";
                            }
                            
                        }
                    }
                }  else {
                    $uploadMessage = "Please try again";
                }
            }
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
<a href="transfer.php">Transfer</a>
<a href="history.php">History</a>
</div>

<div class="userpanel">
<?php echo $_SESSION['user_email'] ?>
<a href="../logout.php">Logout</a><br />
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



<form method="post" action="" class="pure-form pure-form-aligned" enctype='multipart/form-data'>

<fieldset>
<div class = "pure-controls" > Source : #<?php echo $selectedAccount ;?>
</div>
<div class = "pure-controls" > Required Tan : #<?php echo $user->getNextTan($selectedAccount );?>
</div>
<div class="pure-controls">
<input type="file" name="myfile"><br>
</div>
<div class="pure-controls">
<button type="submit" name = "uploadFile" class="pure-button pure-button-primary" > Submit</button>
</div>
</form>
</fieldset>	

<?php echo $uploadMessage; ?>
</body>

</html>
