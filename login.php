<!DOCTYPE html>
<html lang="en">
  <head>
    <title>MySQL + PHP News Site</title>
  </head>
  <body>
    <h1>MySQL + PHP News Site</h1>
    <h3>Login:</h3>
    <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
      <label>
        User Name
        <input type="text" name="u" />
      </label>
      <label>
        Password
        <input type="text" name="p" />
      </label>
      <input type="submit" value="Login" />
    </form>
    <h3>Don't have an account?</h3>

    <form action='/~antond/m3_group/create_account.php' method='post'>
     <!-- <form action='/~rnepal/m3_group/create_account.php' method='post'>  -->

    

		<button type='submit'> Create </button>
	</form>
  <?php
//
    require 'connect_mysql.php';
    require 'verify_user.php';
    $linux_user = 'antond';
    $username = trim((string)$_POST["u"]);
    $pass_input = trim((string)$_POST["p"]);

    if ($username != "" && $pass_input != ""){
        main();
    }

    function main(){
      global $mysqli;
      global $linux_user;
      global $username;
      global $pass_input;

      if(!filter_username($username)){
        print("<h4>User contains invalid characters, try again.</h4>");
        exit();
      }

      if (!user_exists($username)){
        print("<h4>User doesn't exist, try again.</h4>");
        exit();
      }

       $q_hash = $mysqli->prepare("select pass_hash FROM users WHERE username='$username'");
      if(!$q_hash){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
      }
      
      $q_hash->execute();
      $q_hash -> bind_result($pass_hash);
      $a = $q_hash->fetch();

      if (password_verify($pass_input, $pass_hash)) {

        session_start();
        $_SESSION["user"] = $username; //Add this user to the session
        $_SESSION["token"] = bin2hex(openssl_random_pseudo_bytes(32));
        //Adds a CSRF token to the users session.
        //All forms submitted on the website will pull the CSRF token from the users session and send it back.
        //If the token we receive from a post request is ever different from our session token.
        //We know theres something suspect afoot.

        print("<h4>Login success</h4>");
        header("Location: home.php");
      }
      else {
        print("<h4>User exists, but password is incorrect, try again.</h4>");
        exit();
      }
    }
  ?>
  </body>
</html>