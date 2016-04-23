<?php
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

function log_mysql_error($message, $line, $file, $output = false) {
	//error_log(date("d-m-Y H:i:s")." : ".$message, 3, "/var/logs/php_errors.log");
	file_put_contents("C:/xampp/htdocs/Swiss_tournament/logs/php_error.log", date("d-m-Y H:i:s")." MYSQL ERROR: ".$message." NEAR Line: ".$line." OF File: ".$file."\n", FILE_APPEND | LOCK_EX);
	
	if($output == true) {
		return "<div id='error'>Er is iets mis gegaan, probeer het opnieuw</div>";
	}
}

function log_php_error($errno, $errstr, $errfile, $errline) {
	// you'd have to import or set up the connection here 
	file_put_contents("C:/xampp/htdocs/Swiss_tournament/logs/php_error.log", date("d-m-Y H:i:s")." PHP ERROR: (".$errno.") ".$errstr." NEAR Line: ".$errline." OF File: ".$errfile."\n", FILE_APPEND | LOCK_EX);

	/* Don't execute PHP internal error handler */
	return true;
}

$old_error_handler = set_error_handler("log_php_error");
?>