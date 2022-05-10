<?php

    require 'connect_mysql.php';

    function has_voted_story($username, $story_id){

        //Returns false if the user hasn't voted on this story
        //Returns +1 if the user upvoted
        //Returns -1 if the user downvoted

        global $mysqli;
        $q_check = $mysqli->prepare("select vote from users_stories_votes where (username='$username' and story_id=$story_id)");
        //REMEMBER TO ADD SINGLE QUOTES AROUND STRINGS
        if(!$q_check){
            printf("Query Prep Failed: %s\n", $mysqli->error);
           return false;
        }
        $q_check->execute();
        $result = $q_check->get_result();
        $row = $result->fetch_assoc();

        if ($row == null){ //Remember when using arrays you have to use _
            return false; //User doesn't exist yet
        }
        else {
            return $row["vote"];
        }
    }

    function handle_group_post($post){
        //The explode() method was referenced from the PHP manual
        //https://www.php.net/manual/en/function.explode.php
        $group_post_array = explode( "_",$post); 


        $type = (string)$group_post_array[0]; //s for stories or c for comments;
        $group = (string)$group_post_array[1];

        session_start();
        $user = @$_SESSION["user"];

        if ($type == 'l'){ //l for leave j for join
            remove_user_group($user,$group);
        }
        else if ($type =='j'){
            add_user_group($user,$group);
        }
    }

    function add_user_group($username, $groupname){
        //Returns false if unable to add
        //Returns true if able to add

        global $mysqli;
        $add = $mysqli->prepare("insert into users_groups_junction (groupname, username) values ('$groupname','$username')");

        //REMEMBER TO ADD SINGLE QUOTES AROUND STRINGS
        if(!$add){
            printf("Query Prep Failed: %s\n", $mysqli->error);
           return false;
        }
        $add->execute();
        
        $add->close();

        return true;
    }

    function remove_user_group($username, $groupname){
        //Returns false if unable to add
        //Returns true if able to add

        global $mysqli;
        $remove = $mysqli->prepare("delete from users_groups_junction where username='$username' and groupname='$groupname'");

        //REMEMBER TO ADD SINGLE QUOTES AROUND STRINGS
        if(!$remove){
            printf("Query Prep Failed: %s\n", $mysqli->error);
           return false;
        }
        $remove->execute();
        
        $remove->close();

        return true;
    }

    
    function has_voted_comment($username, $comment_id){
        //Returns false if the user hasn't voted on this story
        //Returns +1 if the user upvoted
        //Returns -1 if the user downvoted

        global $mysqli;
        $q_check = $mysqli->prepare("select vote from users_comments_votes where (username='$username' and comment_id=$comment_id)");
        //REMEMBER TO ADD SINGLE QUOTES AROUND STRINGS
        if(!$q_check){
            printf("Query Prep Failed: %s\n", $mysqli->error);
           return false;
        }
        $q_check->execute();
        $result = $q_check->get_result();
        $row = $result->fetch_assoc();

        if ($row == null){ //Remember when using arrays you have to use _
            return false; //User doesn't exist yet
        }
        else {
            return $row["vote"];
        }
    }

    function record_vote($vote_post){
        global $mysqli;
        
        //The explode() method was referenced from the PHP manual
        //https://www.php.net/manual/en/function.explode.php
        $vote_post_array = explode( "_",$vote_post); //HOPEFULLY NO STORIES WILL HAVE UNDERSCORES IN THEM]
        //var_dump($vote_post_array);

        $type = (string)$vote_post_array[0]; //s for stories or c for comments;
        $vote = (int)$vote_post_array[1];

        session_start();
        $user = @$_SESSION["user"];

        if ($type == 's'){
            
            $story_id = (int)$vote_post_array[2];
            //var_dump($story_id);
            if (!has_voted_story($user,$story_id)){ //If the user hasn't voted we need to make a new entry
                //Adding a vote record into the database
    
                $a_vote = $mysqli->prepare("insert into users_stories_votes (username,story_id,vote) values (?,?,?)");
                if(!$a_vote){
                    printf("Query Prep Failed: %s\n", $mysqli->error);
                    exit;
                }
                $a_vote->bind_param('sii', $user, $story_id, $vote);
                // printf("Query Prep Failed: %s\n", $mysqli->error);
                //REMEMBER TO ADD SINGLE QUOTES AROUND STRINGS
    
                $a_vote->execute();
                // printf("Query Prep Failed: %s\n", $mysqli->error);
                $a_vote->close();
    
            }
            else { //If the user has voted we change the existing entry. This prevents filling the table up with entries each time you change.
    
                $a_vote = $mysqli->prepare("update users_stories_votes set vote=$vote where story_id=$story_id");
                if(!$a_vote){
                    printf("Query Prep Failed: %s\n", $mysqli->error);
                    exit;
                }
                $a_vote->execute();
    
                $a_vote->close();
    
            }
    
            if ($vote < 0){
                $a_vote = $mysqli->prepare("update stories set score=score-1 where story_id=$story_id");
    
            }
            else if ($vote > 0){
                $a_vote = $mysqli->prepare("update stories set score=score+1 where story_id=$story_id");
    
            }
    
            $a_vote->execute();
            $a_vote->close();
        }

        else if ($type == 'c'){
            
            $comment_id = (int)$vote_post_array[2];   
            if (!has_voted_comment($user,$comment_id)){ //If the user hasn't voted we need to make a new entry
                //Adding a vote record into the database
    
                $a_vote = $mysqli->prepare("insert into users_comments_votes (username,comment_id,vote) values (?,?,?)");
                if(!$a_vote){
                    printf("Query Prep Failed: %s\n", $mysqli->error);
                    exit;
                }
                $a_vote->bind_param('sii', $user, $comment_id, $vote);
                // printf("Query Prep Failed: %s\n", $mysqli->error);
                //REMEMBER TO ADD SINGLE QUOTES AROUND STRINGS
    
                $a_vote->execute();
                // printf("Query Prep Failed: %s\n", $mysqli->error);
                $a_vote->close();
    
            }
            else { //If the user has voted we change the existing entry. This prevents filling the table up with entries each time you change.
    
                $a_vote = $mysqli->prepare("update users_comments_votes set vote=$vote where comment_id=$comment_id");
                if(!$a_vote){
                    printf("Query Prep Failed: %s\n", $mysqli->error);
                    exit;
                }
                $a_vote->execute();
    
                $a_vote->close();
    
            }
    
            if ($vote < 0){
                $a_vote = $mysqli->prepare("update comments set score=score-1 where comment_id=$comment_id");
    
            }
            else if ($vote > 0){
                $a_vote = $mysqli->prepare("update comments set score=score+1 where comment_id=$comment_id");
    
            }
    
            $a_vote->execute();
            $a_vote->close();
        }
        
            
    }

    function user_groups($username){
        global $mysqli;
        //Returns an array of all of the groups this user is in.
        //Done because again we can't prepare a query while also printing out the groups
        $q_u_groups = $mysqli->prepare("select groupname from users_groups_junction where username='$username'");
        if(!$q_u_groups){
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $q_u_groups->execute();
        $result = $q_u_groups->get_result();

        $q_u_groups->close();
        return $result;

    }

?>