<?php

    function check_csrf(){
        session_start();
        if(!hash_equals($_SESSION['token'], $_POST['token'])){
            die("Request forgery detected. This hurts our feelings, what the heck bro why would you do that!!!");
        }
    }

    function user_exists($username){

        global $mysqli;
        $q_user = $mysqli->prepare("select * from users where (username='$username')");
        //REMEMBER TO ADD SINGLE QUOTES AROUND STRINGS
        if(!$q_user){
            printf("Query Prep Failed: %s\n", $mysqli->error);
           return false;
        }
        
        $q_user->execute();
        $result = $q_user->get_result();
        $row = $result->fetch_assoc();
        //var_dump($row);
        if (!$row){ //Remember when using arrays you have to use _
            return false; //User doesn't exist yet
        }
        return true; //User does exist
    }

    function filter_username($username) {
        //Filter user
		if( !preg_match('/^[\w_\-]+$/', $username) ){

			return false;
		}
        return true;
    }

?>