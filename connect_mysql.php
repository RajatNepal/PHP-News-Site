<?php
        $mysqli = new mysqli('localhost', 'news_inst', 'news_pass', 'news_site_db');
    //print("test 4");
        if($mysqli->connect_errno) {
            printf("Connection Failed: %s\n", $mysqli->connect_error);
            exit;
        }
    

?>