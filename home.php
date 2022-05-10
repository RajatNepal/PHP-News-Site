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

	//Necessary on home.php because this page might be accessed by guests and we don't want to throw the error for them
    if (@$_SESSION["user"]!= null){
      check_csrf();
  	}

	$linux_user = "antond";
	$session_active = false;
	main(); 

	function main(){
		global $linux_user;
        global $mysqli;
		global $session_active;
		global $new_comment;
		global $edit_comment;
		global $delete_comment;
		global $edit_story;
		global $delete_story;

		//If someone tries to access user_dash with no active session through the url
		//and there is no POST request (not being accessed from the login page)
		//They'll get a login error. This needs to come before all other code.

		session_start();


		$user = check_session(); //False if no user, returns username otherwise
		$name = $user;
		if (!$user){
			display_login_button();
		}
		else {
			$session_active = true;
			print("<h1>$user</h1>");


			/*
			We need to check at the very top to see if anything has changed (new/edit/delete comment or edit/delete story)
			this is because we are relaoading the page everytime there is a change
			since we want the page to have sql stuff updated, we do the changes in updates before displaying stories
			the actual forms for making changes are in their respective places throuhgout display_stories(). 
			*/
			//if a new comment is made
			//
			//
			//
			//
		
			if(!$new_comment){
				if((bool)$_POST["new_comment_bool"]){
					//print("<p>there was a new comment test</p>");
					$new_comment = true;
					$_POST["new_comment_bool"] = false;
				}
			}

			if($new_comment){
				new_comment($user);
				$new_comment = false;
			}

			
			
			//if a comment is edited

			if(!$edit_comment){
				if((bool)$_POST["edit_comment_bool"]){
					//print("<p>there was a new comment test</p>");
					$edit_comment = true;
					$_POST["edit_comment_bool"] = false;
				}
			}
			if($edit_comment){
				edit_comment($user);
				$edit_comment = false;
			}

			//if a comment is deleted
			//
			//
			//
			//
			//if not deleted
			if(!$delete_comment){
				if((bool)$_POST["delete_comment_bool"]){
					//print("<p>there was a new comment test</p>");
					$delete_comment = true;
					$_POST["delete_comment_bool"] = false;
				}
			}

			//if comment deletion is made
			if($delete_comment){
				delete_comment($user);
				$delete_comment = false;
			}

			

			//if a story is edited
			//
			//
			//
			//
			if(!$edit_story){
				if((bool)$_POST["edit_story_bool"]){
					//print("<p>there was a new comment test</p>");
					$edit_story = true;
					$_POST["edit_story_bool"] = false;
				}
			}
			if($edit_story){
				edit_story($user);
				$edit_story = false;
			}
			
			//if a story is deleted
			//
			//
			//
			//
			//if not deleted
			if(!$delete_story){
				if((bool)$_POST["delete_story_bool"]){
					
					$delete_story = true;
					$_POST["delete_story_bool"] = false;
				}
			}

			//if story deletion is made
			if($delete_story){
				delete_story($user);
				$delete_story = false;
			}

			display_add_story_button();
			display_logout_button();
			display_user_groups();
			display_view_options();
			display_manage_groups_button();

		}

		$stories_displayed = true;
		
		$group_selected = (string)$_POST["group_selected"];
		$view_selected = (string)$_POST["view_selected"];


		if ($group_selected != null){

			$stories_displayed = display_stories($group_selected, $view_selected);
		}else {
			
			$stories_displayed = display_stories();
		}

        if (!$stories_displayed){
            print("<p> We had a problem displaying the stories and comments!</p>");
        }
		
		
	}

	function delete_story($user){
		global $mysqli;
		$delete_story_id = (int)$_POST["delete_story_id"];
		print("<p>Your story has been deleted: story id: $delete_story_id</p>");

		//here we are deleting the comments associated with the story

		$query1 = "Delete from comments where story_id = $delete_story_id";
		$q_delete_comments = $mysqli->prepare($query1);

		
		if(!$q_delete_comments){  
			printf("Query Prep Failed: %s\n", $mysqli->error);
	
			   return false;
		}

		$q_delete_comments->execute();

		//deleting the user stories vote linker table
		$query2 = "Delete from users_stories_votes where story_id = $delete_story_id";
		$q_delete_story_votes = $mysqli->prepare($query2);

		
		if(!$q_delete_story_votes){  
			printf("Query Prep Failed: %s\n", $mysqli->error);
	
			   return false;
		}

		$q_delete_story_votes->execute();
		
	
		//we can only delete a story once its comments and the user_stories_votes gets deleted 
		//here we are deleting the story
		$query3 = "Delete from stories where story_id = $delete_story_id";
		$q_delete_story = $mysqli->prepare($query3);

		
		if(!$q_delete_story){  
			printf("Query Prep Failed: %s\n", $mysqli->error);
	
			   return false;
		}
	

		$q_delete_story->execute();
	}

	function edit_story($user){
		global $mysqli;
		$edit_story_body = (string)$_POST["edit_story_body"];
		$edit_story_title = (string)$_POST["edit_story_title"];
		$edit_story_link = (string)$_POST["edit_story_link"];
		$edit_story_user = $user;
		$edit_story_id = (int)$_POST["edit_story_id"];
		print("<p>Your Story has been edited</p>");
			
		$query = "update stories set title = '$edit_story_title', body = '$edit_story_body', link = '$edit_story_link' where story_id = $edit_story_id";
		$q_edit_story = $mysqli->prepare($query);

		print("<p>Your story was edited. Updated story title: '$edit_story_title'  </p>");
		print("<p>story number: '$edit_story_id' <p>");
    	if(!$q_edit_story){  
    		printf("Query Prep Failed: %s\n", $mysqli->error);
            
    		return false;
		}
		$q_edit_story->execute();
	}

	function new_comment($user){
		global $mysqli;
		$new_comment_body = (string)$_POST["new_comment_body"];
		$new_comment_user = $user;
		$new_comment_story_id = (int)$_POST["new_comment_story_id"];
				
		$query = "insert into comments (body, story_id, user) values ('$new_comment_body','$new_comment_story_id','$new_comment_user')";
		$q_new_comment = $mysqli->prepare($query);

		print("<p>your comment was added: '$new_comment_body' on  story number: '$new_comment_story_id'</p>");
			
        if(!$q_new_comment){  
            printf("Query Prep Failed: %s\n", $mysqli->error);
            
           	return false;
		}
		$q_new_comment->execute();
	}

	function edit_comment($user){
		global $mysqli;
		$edit_comment_body = (string)$_POST["edit_comment_body"];
		$edit_comment_user = $user;
		$edit_comment_id = (int)$_POST["edit_comment_id"];
		print("<p>Your comment has been edited</p>");
	
		$query = "update comments set body = '$edit_comment_body' where comment_id = $edit_comment_id";
		$q_edit_comment = $mysqli->prepare($query);

		print("<p>Your comment was edited. Updated comment: '$edit_comment_body'  </p>");
		print("<p>comment number: '$edit_comment_id' <p>");
		if(!$q_edit_comment){  
			printf("Query Prep Failed: %s\n", $mysqli->error);
	
			   return false; //Not sure if we let this return out of main too.
		}
		$q_edit_comment->execute();
	}

	function delete_comment($user){
		global $mysqli;
		$delete_comment_id = (int)$_POST["delete_comment_id"];
		print("<p>Your comment has been deleted: comment id: $delete_comment_id</p>");
	
		$query = "Delete from comments where comment_id = $delete_comment_id";
		$q_delete_comment = $mysqli->prepare($query);

		
		if(!$q_delete_comment){  
			printf("Query Prep Failed: %s\n", $mysqli->error);
	
			   return false;
		}

		$q_delete_comment->execute();
	}
	


    function display_stories($group = 'All', $view_selected = 'Recent'){ //Group is all by default
        global $mysqli;
		global $session_active;
		global $linux_user;

        //Ordered in descending order by comment_id and story_id
        //Ordering by timestamp is a bit hard because its a string
        //I'm assuming that the higher id will always be the most recent post.

        print("<h2> Stories </h2>");
        print("<hr>");
		$q_stories;
		$query="";

		//Inserts desired group into the query
		if ($group == 'All'){
			$query .= "select * from stories ";
		}
		else {
			$query .= "select * from stories where groupname='$group' ";
		}

		//Inserts sort type into the query
		if ($view_selected == 'Recent'){
			$query.="order by story_id desc";
		}
		else if ($view_selected == 'Most Popular'){

			$query.="order by score desc";
		}
		else if ($view_selected == 'Least Popular'){

			$query.="order by score asc";
		}

		//$q_stories = $mysqli->prepare("select * from stories where groupname='$group' order by story_id desc");
		$q_stories = $mysqli->prepare($query);

        if(!$q_stories){  
            printf("Query Prep Failed: %s\n", $mysqli->error);
            
           return false;
        }
        $q_stories->execute();

        //For adding data
        //Timestamp does use integer
        //$q->bind_param('isbsis', $story_id, $title, $body, $user, $timestamp, $link);

        //Since we need to also query the comments for each story
        //immediatley after the story is shown. And we can't have
        //two statements at once.
        //Its better to just store all contents of stories in an array
        //close the statement
        //and iterate over the array.
        
        //$q_stories->bind_result( $story_id, $title, $body, $user, $timestamp, $link);
        $result = $q_stories->get_result();
        //Returns a map of the column names to the specific row value on fetch_assoc()
		
        while($row = $result->fetch_assoc()){
            
        
            $story_id = (int)$row["story_id"];
            
            $title = htmlentities($row["title"]);
            
            $body = htmlentities($row["body"]);
            
            $author = htmlentities($row["user"]);
            
            $timestamp = htmlentities($row["timestamp"]);

			$score = (int)$row["score"];

			$link = htmlentities($row["link"]);
            

			if ($session_active){
				$user_has_voted = (int)has_voted_story(@$_SESSION["user"], $story_id);
				$same_page = htmlentities($_SERVER['PHP_SELF']);


				if ($user_has_voted == 1){
					// print("
					// <div id='story-$story_id' class='story'>
					// 	<h2>$title</h2>
					// 	<label Score: $score</h4>
					// 	<form action=$same_page method='post'>
					// 		<input type='hidden' name='token' value=''/>
					// 		<label> $score <button type='submit' name='vote' value='s_-1_$story_id'> - </button></label>
					// 	</form>
					// 	<p>$body</p>
					// 	<p> Posted by: $author </p>
					// 	<p> on $timestamp. </p>
					// </div>
					// ");

					printf("
					<div id='story-$story_id' class='story'>
						<h2>$title</h2>
						<h4> Score: $score</h4>
						<form action='/~$linux_user/m3_group/redirects/vote_successful.php' method='post'>
							<input type='hidden' name='token' value='%s' />
							<label> $score <button type='submit' name='vote' value='s_-1_$story_id'> - </button></label>
						</form>
						<p>$body</p>
						<p>Link: <a href ='http://".$link."' target = '_blank' >$link</a></p>
						<p> Posted by: $author </p>
						<p> on $timestamp. </p>
					</div>
					", (string)$_SESSION['token']);
				}
				else if ($user_has_voted == -1){
					printf("
					<div id='story-$story_id' class='story'>
						<h2>$title</h2>
						<h4> Score: $score</h4>
						<form action='/~$linux_user/m3_group/redirects/vote_successful.php' method='post'>
							<input type='hidden' name='token' value='%s' />
							<label> $score <button type='submit' name='vote' value='s_1_$story_id'> + </button></label>
						</form>
						<p>$body</p>
						<p>Link: <a href ='http://".$link."' target = '_blank' >$link</a></p>
						<p> Posted by: $author </p>
						<p> on $timestamp. </p>
					</div>
					", (string)$_SESSION['token']);
				}
				else {
					printf("
					<div id='story-$story_id' class='story'>
						<h2>$title</h2>
						<h4> Score: $score</h4>
						<form action='/~$linux_user/m3_group/redirects/vote_successful.php' method='post'>
						
							<input type='hidden' name='token' value='%s' />
							<label> $score 
							<button type='submit' name='vote' value='s_1_$story_id'> + </button>
							<button type='submit' name='vote' value='s_-1_$story_id'> - </button></label>
						</form>
						<p>$body</p>
						<p>Link: <a href ='http://".$link."' target = '_blank' >$link</a></p>
						<p> Posted by: $author </p>
						<p> on $timestamp. </p>
					</div>
					", (string)$_SESSION['token']);
					
				}
				//The upvote downvote buttons are a form that sends the vote and the story_id together in a single string
				//This post value is entered into the database when the page reloads

			}
			else {
				print("
				<div id='story-$story_id' class='story'>
					<h2>$title</h2>
					<h4>Upvotes: $score</h4>
					<p>$body</p>
					<p>Link: <a href ='http://".$link."' target = '_blank' >$link</a></p>
					<p> Posted by: $author </p>
					<p> on $timestamp. </p>
				</div>
				");
			}
            
            
            //Can't have two queries prepared at the same time
            //Running get_result for stories, running bind_result for comments

            $q_comments = $mysqli->prepare("select * from comments where (story_id = $story_id) order by comment_id desc");
            if(!$q_comments){
                printf("Query Prep Failed: %s\n", $mysqli->error);
                
               return false;
            }
            $q_comments->execute();
            $q_comments->bind_result( $comment_id, $comment_timestamp, $comment_body, $comment_story_id, $comment_user, $comment_score);
			
			//checking to see if there is a user logged in
			if ($session_active){

				$username = @$_SESSION["user"];
				//this if statement checks to see if the logged on user is the same as the author of the story
				//if so, it creates forms that the author can delete or edit the story
				if($author == $username){
					//editing story

					printf( "<form  method='post'>
				
					<label>
					&emsp;Edit Story Title:
					<input type='text'  name='edit_story_title' value = '$title' />
					   </label>
					<br>
					<label>
					&emsp;Edit Story Body:
					<input type='text'  name='edit_story_body' value = '$body'/>
					</label>
					<br>
					<label>
					&emsp;Edit Story Link:
					<input type='text'  name='edit_story_link' value = '$link' />
					</label>
					   
					<input type='hidden' name='token' value=".(string)$_SESSION["token"]."/>
					  <input type='hidden' name='edit_story_id' value=".(int)$story_id.">
					<input type='hidden' name='edit_story_bool' value=".true.">
					  <input type='submit' value='Submit' />
						</form><br>") ;

						//deleting story
					echo '<form  method="post">
						<input type="hidden" name="token" value="'.(string)$_SESSION["token"].'"/>
						<input type="hidden" name="delete_story_id" value="'.(int)$story_id.'">
						<input type="hidden" name="delete_story_bool" value="'.true.'">
					  	&emsp;<input type="submit" value="Delete story" />
						</form><br><br>';
				}

				
				//this is making the form for adding new comment
				//this happens before the existing comments are displayed
				echo '<form  method="post">
				
            	<label>
        		Add Comment:
        		<input type="text" name="new_comment_body" />
 			     </label>
				  <input type="hidden" name="token" value="'.(string)$_SESSION["token"].'"/>
				  <input type="hidden" name="new_comment_user" value="'.$user.'">
				  <input type="hidden" name="new_comment_story_id" value="'.(int)$story_id.'">
				  <input type="hidden" name="new_comment_bool" value="'.true.'">
      			<input type="submit" value="Post" />
   				 </form><br><br>';

			  
			}
		

            while($q_comments->fetch()){

					$comment_body_filtered = htmlentities($comment_body);
					print("
					<div id='comment-$comment_id' class='comment'>
						
						<h4>$comment_user on $comment_timestamp. </h4>
						<p>$comment_body_filtered</p>
					</div>
					");

					//for editing and deleting comments
					$username = @$_SESSION["user"];


					//here we are checking if the logged on user is the same as the commenting user
					//if so, we are making forms they can use to delete or edit comments
					if($comment_user == $username){

						//NOTE: Deleted the action = "" tags here because it was causing w3c validation to fail
						//editing comment
						echo '<form  method="post">
            			<label>
        				&emsp;Edit Comment:
        				<input type="text"  name="edit_comment_body" />
 			  		    </label>
						<input type="hidden" name="token" value="'.(string)$_SESSION["token"].'"/>
					  	<input type="hidden" name="edit_comment_id" value="'.(int)$comment_id.'">
						<input type="hidden" name="edit_comment_bool" value="'.true.'">
      					<input type="submit" value="Submit" />
   				 		</form>';


						//deleting comment
						echo '<form  method="post">
						<input type="hidden" name="token" value="'.(string)$_SESSION["token"].'"/>
					  	<input type="hidden" name="delete_comment_id" value="'.(int)$comment_id.'">
						<input type="hidden" name="delete_comment_bool" value="'.true.'">
						&emsp;<input type="submit" value="Delete comment" />
   				 		</form><br>';
					}
				
                
            }
			
            print("<hr>");
			
            $q_comments->close();
        }
		

        $q_stories->close();
        return true;
    }

	function display_user_groups(){
		global $mysqli;
		global $session_active;
		global $linux_user;
        print("<h4> Your Groups </h4>");
		$username = @$_SESSION["user"];
        $q_user_groups = $mysqli->prepare("select groupname from users_groups_junction where username='$username'");

        if(!$q_user_groups){  
            printf("Query Prep Failed: %s\n", $mysqli->error);
           return false;
        }
        $q_user_groups->execute(); 
    

        $result = $q_user_groups->get_result();
 
     
        //Returns a map of the column names to the specific row value on fetch_assoc()
		
		$list_user_groups = "";

        while($row = $result->fetch_assoc()){
        
            $groupname = $row["groupname"];

			$list_user_groups .= "$groupname, ";
            
        }
		print("
				<div id='grouplist' class='groups'>
					<p>$list_user_groups</p>
				</div>
				");
        $q_user_groups->close();
	}

	function display_view_options(){
		session_start();
		$users_groups = user_groups(@$_SESSION["user"]);

		$same_page = htmlentities($_SERVER['PHP_SELF']);

		$dropdown_contents = "";
		while ($a = $users_groups->fetch_assoc()){
			$groupname = $a["groupname"];
			$dropdown_contents .= "<option value='$groupname'>$groupname</option>" ;
		}
		printf("<form action='$same_page'  id='view' method='post'>
				<label for='group-options'>
				Choose the group you want to see stories from:
				<select id='group-options' name='group_selected'>
					<option>
					All
					</option>
					$dropdown_contents
				</select>
				</label>
				<label for='view-options'>
				Sort by:
				<select id='view-options' name='view_selected'>
					<option  selected='selected'>
					Recent
					</option>
					<option>
					Most Popular
					</option>
					<option>
					Least Popular
					</option>
				</select>
				</label>
				
				<input type='hidden' name='token' value='%s'/>
				<input type='submit' value='View' />
			</form>", (string)$_SESSION["token"]);
	}

	function display_manage_groups_button(){
		global $linux_user;
		printf("<form action='/~$linux_user/m3_group/groups.php' method='post'>
		<input type='hidden' name='token' value='%s'/>
		<button type='submit'> Manage Groups </button>
		</form>", (string)$_SESSION["token"]);
	}
	function display_add_story_button(){
		global $linux_user;
		printf("<form action='/~$linux_user/m3_group/add_story.php' method='post'>
		<input type='hidden' name='token' value='%s'/>
		<button type='submit'> Post a new story </button>
		</form>", (string)$_SESSION["token"]);
	}

	function display_login_button(){
		global $linux_user;
		printf("<form action='/~$linux_user/m3_group/login.php' method='post'>
		<button type='submit'> Log in </button>
		<input type='hidden' name='token' value='%s'/>
		</form>", (string)$_SESSION["token"]);
	}

	function display_logout_button(){
		global $linux_user;
		printf("<form action='/~$linux_user/m3_group/redirects/logout_successful.php' method='post'>
		<button type='submit'> Log out </button>
		<input type='hidden' name='token' value='%s'/>
		</form>", (string)$_SESSION["token"]);
	}


	function check_session(){
		//Returns false if there's no valid session
		//Returns the user name if there is a valid sesison
		//Returns "" if theres a bug lol
		session_start();
		if (@$_SESSION["user"] != null){
			
			if (@$_SESSION["token"] != null){
				$user = @$_SESSION["user"]; //This user has already been filtered
				return $user;
			}
		}
		return false;
	}
	
    ?>
  </body>
</html>
