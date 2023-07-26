<?php

    //No banco de dados, guardar senha como VARCHAR(255)

    $host = "localhost";
    $user = "root";
    $pass = "";
    $bd = "upload";

    $mysqli = new mysqli($host, $user, $pass, $bd);
    
    if ($mysqli->connect_errno) {
        echo "Connect failed: " . $mysqli->connect_error;
        exit();
    }