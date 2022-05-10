<!DOCTYPE html>
<html lang="en">
  <head>
    <title>MySQL + PHP News Site</title>
  </head>
  <body>
    <h1>MySQL + PHP News Site</h1>
    <?php

    require 'connect_mysql.php';
	  require 'utility.php';
    require 'verify_user.php';
    //Creates an object, $mysqli, which enables us to interact with our mysql database
    //via our user.

  check_csrf();
	$linux_user = "antond";
	main(); 

	function main(){
	  global $linux_user;
    global $mysqli;
		global $session_active;
    global $story_added_bool;

		session_start();


        if ($_POST["action_group"]!=null){

            handle_group_post((string)$_POST["action_group"]); //Explodes post string and handles request
        }

        $user = @$_SESSION["user"];
        
	      print("<h1>$user</h1>");

        printf("
          <form action='/~$linux_user/m3_group/home.php' method='post'>
          <input type='hidden' name='token' value='%s'/>
            <button type='submit'> Return Home </button>
          </form>
          ",(string)$_SESSION["token"]);


        print("<p>Make sure to join a group before posting. You can only post to groups you have joined!</p>");

          
	    	printf("<form action='/~$linux_user/m3_group/groups.php' method='post'>
	        <input type='hidden' name='token' value='%s'/>
		      <button type='submit'> Manage Groups </button>
		      </form>", (string)$_SESSION["token"]);
  
        
        
          var_dump($story_added_bool);
        //if story was added
        if(!$story_added_bool){
            if((bool)$_POST["story_added_bool"]){

                $story_added_bool = true;
                $_POST["story_added_bool"] = false;
            }
        }
        //if a story was added
          if($story_added_bool){
            $story_title = (string)$_POST["title"];
            $story_author = $user;
            $story_body = (string)$_POST["body"];
            $story_link = (string)$_POST["link"];
            $story_group = (string)$_POST["group_selected"];

            //remove_http cited in definition.
            $story_link_filtered = remove_http($story_link);

            
            $query = "insert into stories (title, body, link, user, groupname) values ('$story_title','$story_body','$story_link_filtered', '$story_author', '$story_group')";
           
            $q_add_story = $mysqli->prepare($query);

            print("<p>your story was added: Title: $story_title Body: $story_body</p>");
        
            if(!$q_add_story){  
                printf("Query Prep Failed: %s\n", $mysqli->error);
        
                   return false;
            }

            $q_add_story->execute();
    
            $story_added_bool = false;
        }

        display_post_story();

	}

  //got this function from the following website:
  //https://www.codegrepper.com/code-examples/php/remove+http+%2F+https+from+link+php
  function remove_http($url) {
    $disallowed = array('http://', 'https://');
    foreach($disallowed as $d) {
       if(strpos($url, $d) === 0) {
          return str_replace($d, '', $url);
       }
    }
    return $url;
  }
  //end of copied code

	function display_post_story(){
		session_start();
		$users_groups = user_groups(@$_SESSION["user"]);

		$same_page = htmlentities($_SERVER['PHP_SELF']);

		$dropdown_contents = "";
		while ($a = $users_groups->fetch_assoc()){
			$groupname = $a["groupname"];
			$dropdown_contents .= "<option value='$groupname'>$groupname</option>" ;
		}
		printf("<br><form action='$same_page'  id='view' method='post'>
        <input type='hidden' name='token' value='%s'/>
				<label for='group-options'>
				Choose the group you want to add your story to:
				<select id='group-options' name='group_selected'>
					
					$dropdown_contents
				</select>
				</label>
                <br>
                Title: <input type = 'text' name = 'title'><br>
                Body: <input type = 'text' name = 'body'><br>
                Link (optional): <input type = 'text' name = 'link'><br>
                <br>
                <input type='hidden' name='story_added_bool' value=".true.">
                

				<input type='submit' value='Post Story' />
			</form>", (string)$_SESSION["token"]);
	}

    ?>
  </body>
</html>



