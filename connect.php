<?php
$username = "root";
$password = "";
$host = "localhost";
$database = "swiss_tournament";

$connect = @mysqli_connect($host, $username, $password, $database) or die ('Could not connect to database: ' . mysqli_connect_error());
?>