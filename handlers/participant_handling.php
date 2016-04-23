<?php
include("../connect.php");
include("../functions.php");

if(isset($_POST['name'])) {
	$errors = array();
	
	if(!preg_match("/^[\p{L}- ]*$/u", $_POST['name'])) {
		$errors[] = "De naam mag enkel letters bevatten";
	}
	
	if(empty($errors)) {	
		$name = htmlentities($_POST['name'], ENT_QUOTES, "UTF-8");
		
		$get_participant = mysqli_query($connect, "SELECT player_id FROM ".$_POST['filename']." WHERE name = '".$name."'");
		
		if(!$get_participant) {
			log_mysql_error(mysqli_error($connect), __LINE__, __FILE__, false);
			
			echo json_encode(array("status" => "error", "message" => "Er is iets mis gegaan, probeer het opnieuw"));
		} else {	
			if(mysqli_num_rows($get_participant) == 0) {	
				$add_participant = mysqli_query($connect, "INSERT INTO ".$_POST['filename']." SET name = '".$name."'");
				
				if(!$add_participant) {
					log_mysql_error(mysqli_error($connect), __LINE__, __FILE__, false);
					
					echo json_encode(array("status" => "error", "message" => "Er is iets mis gegaan, probeer het opnieuw"));
				} else {
					echo json_encode(array("status" => "succes", "message" => "De deelnemer is toegevoegd"));
				}
			} else {
				echo json_encode(array("status" => "error", "message" => "Deze deelnemer staat al in de lijst"));
			}
		}
	} else {
		echo json_encode(array("status" => "error", "message" => "De naam bevat niet toegestane tekens"));
	}
}
?>