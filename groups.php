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
    
    $group_added_bool = false;
	main(); 

	function main(){
		global $linux_user;
        global $mysqli;
		global $session_active;
        global $group_added_bool;
        session_start();

        printf("
          <form action='/~$linux_user/m3_group/home.php' method='post'>
          <input type='hidden' name='token' value='%s'/>
            <button type='submit'> Return Home </button>
          </form>
          ", (string)$_SESSION["token"]);

		session_start();

        if ($_POST["action_group"]!=null){

            handle_group_post((string)$_POST["action_group"]); //Explodes post string and handles request
        }

        $user = @$_SESSION["user"];
        
	    print("<h1>$user</h1>");

        $q_groups = $mysqli->prepare("select * from groups");

        if(!$q_groups){  
            printf("Query Prep Failed: %s\n", $mysqli->error);
           return false;
        }
        $q_groups->execute();
        //$q_stories->bind_result( $story_id, $title, $body, $user, $timestamp, $link);
        $result = $q_groups->get_result();
        //Returns a map of the column names to the specific row value on fetch_assoc()
        $same_page = htmlentities($_SERVER['PHP_SELF']);

        while($row = $result->fetch_assoc()){
            $groupname = $row["groupname"];
            
            $description = $row["description"];
            
            $founder = $row["founder"];
            print("<hr>");

            
            //Absolutely hideous solution but works for now
            $user_groups = user_groups($user);
            $in_group = false;
            while ($a = $user_groups->fetch_assoc()){
                $s_groupname = $a["groupname"];
                if ($groupname == $s_groupname){
                    $in_group = true;
                }
            }
            //Was trying to call user groups once and then just rewind the iterator but oh well I'm too lazy
            //$a.rewind();

            //The str_replace() method was referenced from the PHP manual
            //https://www.php.net/manual/en/function.str-replace
            $div_name = str_replace(" ","",$groupname);
            if ($in_group){
                printf("
                <div id='group-$div_name' class='group'>
                    <h2>$groupname</h2>
                    <form action=$same_page method='post'>
                         <input type='hidden' name='token' value='%s'/>
                        <label> $groupname <button type='submit' name='action_group' value='l_$groupname'> Leave </button></label>
                    </form>
                    <p>$description</p>
                    <p> Founder: $founder </p>
                </div>
                ", (string)$_SESSION["token"]);
            }
            else {
                printf("
                <div id='group-$div_name' class='group'>
                <h2>$groupname</h2>
                <form action=$same_page method='post'>
				    <input type='hidden' name='token' value='%s'/>
                    <label> $groupname <button type='submit' name='action_group' value='j_$groupname'> Join </button></label>
                </form>
                <p>$description</p>
                <p> Founder: $founder </p>
                </div>
                ", (string)$_SESSION["token"]);
            }
        }
		$q_groups->close();


        print("<hr>");
        print("<h1>Create a Group:</h1>");

          display_create_group();



        //if story was added
        if(!$group_added_bool){
            if((bool)$_POST["group_added_bool"]){
                
                $group_added_bool = true;
                $_POST["group_added_bool"] = false;
            }
        }
        //if a story was added
          if($group_added_bool){
            $group_name = (string)$_POST["group_name"];

            $group_founder = $user;
            $group_description = (string)$_POST["group_description"];



            $query = "insert into groups (groupname, description, founder) values ('$group_name','$group_description','$group_founder')";
            //$query = "insert into stories (title, body, link, user, groupname) values ('$story_title','$story_body','$story_link_filtered', '$story_author', '$story_group')";
           
            $q_add_group = $mysqli->prepare($query);

            print("<p>your group was added: Name: $group_name</p>");
        
            if(!$q_add_group){  
                printf("Query Prep Failed: %s\n", $mysqli->error);
                   return false;
            }

            $q_add_group->execute();

                printf(" %s\n", $mysqli->error);
 

    
            $group_added_bool = false;
        }
	}

    function display_create_group(){
        global $mysqli;
		session_start();
		$users_groups = user_groups(@$_SESSION["user"]);

		$same_page = htmlentities($_SERVER['PHP_SELF']);

		$dropdown_contents = "";

		printf("<br><form action='$same_page'  id='view' method='post'>
        <input type='hidden' name='token' value='%s'/>
                <br>
                Name: <input type = 'text' name = 'group_name'><br>
                Description: <input type = 'text' name = 'group_description'><br>
                <br>
                <input type='hidden' name='group_added_bool' value=".true.">
                
				<input type='submit' value='Create Group' />
			</form>", (string)$_SESSION["token"]);
	}
	

    ?>
  </body>
</html>



