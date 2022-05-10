<!DOCTYPE html>
<html lang="en">
  <head>
    <title>MySQL + PHP News Site</title>
  </head>
  <body>
    <h1>MySQL + PHP News Site</h1>
    <h3>Create Account:</h3>
    <!-- <form action ="create_account.php"  method="post"> -->
    <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
      <label>
        User Name
        <input type="text" name="u" />
      </label>
      <label>
        Password
        <input type="text" name="p" />
      </label>
      <input type="submit" value="Submit" />
    </form>

    
    <?php 
    //NOTE: The absolute stupidest bugs will not show up obviously, if code just doesn't run it's because you probably
    //have a syntax error the code will just stop executing at a certain point.

    //Page submits form to itself. Waits until u and p are set via post request before
    //executing this block of php
   
    require 'connect_mysql.php';
   
    require 'verify_user.php';
    
    $linux_user = 'antond';
    
    
    $username = trim((string)$_POST["u"]);
    $pass = trim((string)$_POST["p"]);


      if ($username != "" && $pass !=""){ //Just checking to make sure they aren't empty strings

        main();

      }

      function main(){
        global $username;
        global $pass;

        $a = user_exists($username);
        $b = filter_username($username);



        if ($a) {
          print("<h4> User already exists! </h4>");
        }
        if (!$b) {
          print("<h4> User contains invalid characters! </h4>");
        }

        if (!user_exists($username) && filter_username($username)){
          create_account($username, $pass);
        }
        else {
          return;
        }
      }

      function create_account($username, $password){
        global $mysqli;
        global $linux_user;
        $a_user = $mysqli->prepare("insert into users (username,pass_hash) values (?,?)");
        if(!$a_user){
          printf("Query Prep Failed: %s\n", $mysqli->error);
          exit;
        }

        $pass_hash = password_hash($password, PASSWORD_DEFAULT);
        $a_user->bind_param('ss', $username, $pass_hash);


     
        
        $executed = $a_user->execute();
      
        
        
      
        $a_user->close();

        //Salt gets stored with the hashed password. The way we have it right now is good.

        //For adding data
        //Timestamp does use integer
        //$q->bind_param('isbsis', $story_id, $title, $body, $user, $timestamp, $link);

          print("
          <h4> Account Created! </h4>
          <form action='/~$linux_user/m3_group/login.php' method='post'>
            <button type='submit'> Return to Login </button>
          </form>
          ");
      }
      
    ?>
  </body>
</html>