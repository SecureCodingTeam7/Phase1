<html>
  <head>
 <link rel="stylesheet" href="zerotype/css/style.css" type="text/css" media="screen" />
    <title>Login</title>
    <meta content="">
    <style></style>
  </head>
  <body>
  
   <?php
include('login.php'); // Includes Login Script
?>

 
  
  

 <a href="register.php">Or Register Here</a>
 
  <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" >
  <fieldset>
  <legend>Login</legend>
  
  <div class='container'>
  <label for='email'>Email Adresse: </label>
  <input type="text" name="email" />
   <span id='login_username_errorloc' class='error'></span>
   </div>
  <div class='container'>
  <label for='password'>Password: </label>
  <input type="password" name="passwd" />
  <span id='login_password_errorloc' class='error'></span>
  </div>
  
  <div class='container'>
  <input type="submit" name ="submit" />
  </div>
  </fieldset>
  </form>

 <p><?php echo $error ?> </p>



 

  </body>
</html>
