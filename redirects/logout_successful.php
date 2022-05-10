

<!DOCTYPE html>
<html lang="en">
  <head>
    <title>MySQL + PHP News Site</title>
  </head>
  <body>
    <h1>MySQL + PHP News Site</h1>
    <h4>Logout Successful!</h4>
    <?php
      $linux_user = 'antond';
        session_start();
        session_destroy();
        print("<form action='/~$linux_user/m3_group/home.php' method='post'>
		    <button type='submit'> Home </button>
		    </form>");
    ?>

  </body>
</html>