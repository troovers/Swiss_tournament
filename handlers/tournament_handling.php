<?php
include("../connect.php");
include("../functions.php");

if(isset($_POST['name'])) {
	$errors = array();
	
	if(!preg_match("/^[\p{L}- ]*$/u", $_POST['name'])) {
		$errors[] = "De naam mag enkel letters bevatten";
	}
	
	if(empty($errors)) {	
		$year = $_POST['year'];
		$name = htmlentities($_POST['name'], ENT_QUOTES, "UTF-8");
		
		if(!file_exists("../tournaments/".$name."_".$year.".php")) {	
			$file = "../tournaments/tournament_year.php";
			$newfile = "../tournaments/".$name."_".$year.".php";
			
			// Copy the tournament template file and rename it to be named after the tournament
			if(!copy($file, $newfile)) {
				echo json_encode(array("status" => "error", "message" => "Er is iets mis gegaan, probeer het opnieuw"));
			} else {
				echo json_encode(array("status" => "succes", "message" => "Het toernooi is aangemaakt"));
			}
		} else {
			echo json_encode(array("status" => "error", "message" => "Deze editie bestaat reeds"));
		}
	} else {
		echo json_encode(array("status" => "error", "message" => "De naam mag enkel letters bevatten"));
	}
}
?>