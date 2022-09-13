<?php 
    header('Access-Control-Allow-Origin: *'); 

    if (isset($_SESSION["auth"]) && $_SESSION["auth"]) {
        echo "authorized";
    } else {
        echo "not authorized";
    }
?>