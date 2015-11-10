<?php
$username = "root";
$password = "";
$host = "localhost";
$database = "swiss_tournament";

$connect = @mysqli_connect($host, $username, $password, $database) or die ('Could not connect to database: ' . mysqli_connect_error());

function initializeDatabase() {
	global $connect; 
	
	mysqli_query($connect, "CREATE TABLE IF NOT EXISTS `tournament_results` (
		`filename` char(100) NOT NULL,
		`first` char(100) NOT NULL,
		`second` char(100) NOT NULL,
		PRIMARY KEY(`filename`)
	)");
}


function createNewEdition($filename) {
	global $connect; 
	
	// Create participants table
	mysqli_query($connect, "CREATE TABLE IF NOT EXISTS `".$filename."` (
		`player_id` int(2) NOT NULL AUTO_INCREMENT,
		`name` char(55) NOT NULL,
		`number_wins` int(2) NOT NULL DEFAULT '0',
		PRIMARY KEY(`player_id`)
	)");
	
	// Create_rounds_table
	mysqli_query($connect, "CREATE TABLE IF NOT EXISTS `".$filename."_rounds` (
		`round_id` int(3) NOT NULL AUTO_INCREMENT,
		`round_specifics` char(25) NOT NULL,
		`player_1` char(100) NOT NULL,
		`player_2` char(100) NOT NULL,
		`result` char(3) NOT NULL,
		PRIMARY KEY(`round_id`)
	)");
}
?>