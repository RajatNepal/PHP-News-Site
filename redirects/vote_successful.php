

<!DOCTYPE html>
<html lang="en">
  <head>
    <title>MySQL + PHP News Site</title>
  </head>
  <body>
    <h1>MySQL + PHP News Site</h1>
    <h4>Vote Successful!</h4>
    <?php
    
      $linux_user = 'antond';
      print("test");

      require '../utility.php';

      var_dump((string)$_POST["vote"]);

			if ((string)$_POST["vote"] != null){ //Form re-submission lets you go above your limited upvotes
				record_vote((string)$_POST["vote"]); //Not sure comparing to null is working
			}

      print("test");


      header("Location: ../home.php");
    
    ?>

  </body>
</html>