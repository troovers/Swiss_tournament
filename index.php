<?php
include("connect.php");
include("functions.php");

// Check if the database is filled
$table_existence = mysqli_query($connect, "SHOW TABLES LIKE 'tournament_results'");
$table_exists = mysqli_num_rows($table_existence) > 0;

if($table_exists == FALSE) {
	initializeDatabase();
}

// Clear the results of the tournament
if(isset($_POST['clear'])) {
	$filename = $_POST['filename'];
	
	do {
		// Clear the participants table and reset auto_increment to 1
		$clear_participants = mysqli_query($connect, "TRUNCATE TABLE ".$filename);
		
		if(!$clear_participants) {
			$response = log_mysql_error(mysqli_error($connect), __LINE__, __FILE__, true);
			
			break;
		}		
		
		// Clear the played tournament rounds table and reset auto_increment to 1
		$clear_rounds = mysqli_query($connect, "TRUNCATE TABLE ".$filename."_rounds");
		
		if(!$clear_rounds) {
			$response = log_mysql_error(mysqli_error($connect), __LINE__, __FILE__, true);
			
			break;
		}
		
		// Delete the results of the tournament, tournament was never played
		$clear_results = mysqli_query($connect, "DELETE FROM tournament_results WHERE filename = '".$filename."'");
		
		if(!$clear_results) {
			$response = log_mysql_error(mysqli_error($connect), __LINE__, __FILE__, true);
			
			break;
		}
		
		// Everything went right
		$response = "<div id='succes'>De gegevens van het toernooi zijn verwijderd</div>";
	} while(0);
}


// Delete the entire tournament and its' results
if(isset($_POST['delete'])) {
	$filename = $_POST['filename'];
	
	do {
		// Delete the entire participants table
		$drop_participants = mysqli_query($connect, "DROP TABLE ".$filename);
		
		if(!$drop_participants) {
			$response = log_mysql_error(mysqli_error($connect), __LINE__, __FILE__, true);
			
			break;
		}
		
		// Delete the entire played tournament rounds table
		$drop_rounds = mysqli_query($connect, "DROP TABLE ".$filename."_rounds");
		
		if(!$drop_rounds) {
			$response = log_mysql_error(mysqli_error($connect), __LINE__, __FILE__, true);
			
			break;
		}
		
		// Delete the results of the tournament
		$clear_results = mysqli_query($connect, "DELETE FROM tournament_results WHERE filename = '".$filename."'");
		
		if(!$clear_results) {
			$response = log_mysql_error(mysqli_error($connect), __LINE__, __FILE__, true);
			
			break;
		}
		
		// Delete the file of the tournament
		if(!unlink("tournaments/".$filename.".php")) {
			$response = "<div id='error'>Er is iets mis gegaan, probeer het opnieuw</div>\n";
			
			break;
		} 
		
		// Everything went right
		$response = "<div id='succes'>Het toernooi is verwijderd</div>";
	} while(0);
}


// Add a new tournament to the system
if(isset($_POST['add']) && !empty($_POST['name'])) {
	$year = $_POST['year'];
	$name = htmlentities($_POST['name'], ENT_QUOTES, "UTF-8");
	
	// Check if the tournament already exists as a file
	if(!file_exists("tournaments/".$name."_".$year.".php")) {	
		$file = "tournaments/tournament_year.php";
		$newfile = "tournaments/".$name."_".$year.".php";
		
		// Copy the tournament template file and rename it to be named after the tournament
		if(!copy($file, $newfile)) {
			$response = "<div id='error'>Er is iets mis gegaan, probeer het opnieuw</div>";
		} else {
			$response = "<div id='succes'>Het toernooi is aangemaakt</div>";
		}
	} else {
		$response = "<div id='error'>Deze editie bestaat al. Deze dient eerst verwijdert te worden.</div>";
	}
} elseif(isset($_POST['add']) && empty($_POST['name'])) {
	$response = "<div id='error'>U heeft geen naam ingevuld</div>";
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Toernooien</title>
		<link rel="stylesheet" href="style/participants.css" type="text/css">
		<style type="text/css">
		input[name="clear"],
		input[name="delete"],
		input[name="clear"]:hover,
		input[name="delete"]:hover {
			padding: 0;
			margin: 0;
			background-color: transparent;
			background-image: none;
			color: #007600;
			border-radius: 0;
			-moz-border-radius: 0;
			-webkit-border-radius: 0;
			font-family: Verdana;
			font-size: 12px;
			text-transform: none;
			text-decoration: none;
			display: inline;
			width: auto;
			border: 0;
		}
		
		input[name="clear"]:hover,
		input[name="delete"]:hover {
			text-decoration: underline;
		}
		</style>
	</head>
	<body>
		<?php
		$files = array();
		
		// Retrieve all the php files of the created tournaments
		foreach (glob("tournaments/*_2*.php") as $file) {
			$file = basename($file, ".php");
			
			array_push($files, $file);
		}
		
		echo "<div id='wrapper'>\n";
		echo "<div id='content'>\n";
		echo "<h1>Toernooien</h1>\n";
		
		if(isset($response)) {
			echo $response;
		} 
		
		// Initialize the form to add a new tournament
		echo "<form name='add' method='post' action='".$_SERVER['PHP_SELF']."'>\n";
		echo "<table>\n";
		echo "<tr>\n";
		echo "<td width='210'>\n";
		
		echo isset($_POST['submit']) ? "<input type='text' name='name' value='".$_POST['name']."' placeholder='Naam'>\n" : "<input type='text' name='name' value='' placeholder='Naam'>\n";
		
		echo "</td>\n";
		echo "<td width='90'>\n";
		echo "<select name='year'>\n";
		echo "<option value='".date("Y")."'>".date("Y")."</option>\n";
		echo "<option value='".(date("Y")+1)."'>".(date("Y")+1)."</option>\n";
		echo "</select>\n";
		echo "</td>\n";
		echo "<td>\n";
		echo "<input type='submit' name='add' value='Nieuw toernooi'>\n";
		echo "</td>\n";
		echo "</tr>\n";
		echo "</table>\n";
		echo "</form>\n";
		
		// If there are tournaments, as a file, display the table
		if(sizeof($files) > 0) {
			echo "<table>\n";
			echo "<tr>\n";
			echo "<th width='150'>Jaar</th>\n";
			echo "<th width='200'>1e Plaats</th>\n";
			echo "<th width='200'>2e Plaats</th>\n";
			echo "<th width='75'>&nbsp;</th>\n";
			echo "<th width='75'>&nbsp;</th>\n";
			echo "</tr>\n";
			
			// For each file in the array containing files, display a table row
			foreach($files AS $key => $filename) {
				echo "<tr>\n";
				echo "<td><a href='tournaments/".$filename.".php'>".str_replace("_", " ", $filename)."</a></td>\n";
				
				$results = mysqli_query($connect, "SELECT first, second FROM tournament_results WHERE filename = '".$filename."'");
				$result = mysqli_fetch_assoc($results);
				
				if(mysqli_num_rows($results) == 0) {
					$first = "-";
					$second = "-";
				} else {
					$first = $result['first'];
					$second = $result['second'];
				}
				
				echo "<td>".$first."</td>\n";
				echo "<td>".$second."</td>\n";
				echo "<td>\n";
				echo "<form name='clear' method='post' action='".$_SERVER['PHP_SELF']."'>\n";
				echo "<input type='hidden' name='filename' value='".$filename."'>\n";
				echo "<input type='submit' name='clear' value='Legen'>\n";
				echo "</form>\n";
				echo "</td>\n";
				echo "<td>\n";
				echo "<form name='delete' method='post' action='".$_SERVER['PHP_SELF']."'>\n";
				echo "<input type='hidden' name='filename' value='".$filename."'>\n";
				echo "<input type='submit' name='delete' onclick='return confirm(\"Weet u zeker dat u dit toernooi wilt verwijderen?\")' value='Verwijderen'>\n";
				echo "</form>\n";
				echo "</td>\n";
				echo "</tr>\n";
			}
			
			echo "</table>\n";
		} else {
			echo "Er staan nog geen toernooien in het systeem.";
		}
		
		echo "</div>\n";
		echo "</div>\n";
		?>
	</body>
</html>