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
	
	$clear_participants = mysqli_query($connect, "TRUNCATE TABLE ".$filename);
	$reset_participants = mysqli_query($connect, "ALTER TABLE ".$filename." SET AUTO_INCREMENT = 1");
	
	$clear_rounds = mysqli_query($connect, "TRUNCATE TABLE ".$filename."_rounds");
	$reset_rounds = mysqli_query($connect, "ALTER TABLE ".$filename."_rounds SET AUTO_INCREMENT = 1");
	
	$clear_results = mysqli_query($connect, "DELETE FROM tournament_results WHERE filename = '".$filename."'");
	
	$succes = "<div id='succes'>De gegevens van het toernooi zijn verwijderd</div>";
}


// Delete the entire tournament and its' results
if(isset($_POST['delete'])) {
	$filename = $_POST['filename'];

	$drop_participants = mysqli_query($connect, "DROP TABLE ".$filename);
	
	$drop_rounds = mysqli_query($connect, "DROP TABLE ".$filename."_rounds");
	
	$clear_results = mysqli_query($connect, "DELETE FROM tournament_results WHERE filename = '".$filename."'");
	
	unlink("tournaments/".$filename.".php");
	
	$succes = "<div id='succes'>Het toernooi is verwijderd</div>";
}


// Add a new tournament to the system
if(isset($_POST['add']) && !empty($_POST['name'])) {
	$year = $_POST['year'];
	$name = htmlentities($_POST['name'], ENT_QUOTES, "UTF-8");
	
	if(!file_exists("tournaments/".$name."_".$year.".php")) {	
		$file = "tournaments/tournament_year.php";
		$newfile = "tournaments/".$name."_".$year.".php";
		
		if(!copy($file, $newfile)) {
			$error = "<div id='error'>Er is iets mis gegaan, probeer het opnieuw</div>";
		} else {
			$succes = "<div id='succes'>Het toernooi is aangemaakt</div>";
		}
	} else {
		$error = "<div id='error'>Deze editie bestaat al. Deze dient eerst verwijdert te worden.</div>";
	}
} elseif(isset($_POST['add']) && empty($_POST['name'])) {
	$error = "<div id='error'>U heeft geen naam ingevuld</div>";
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
		
		
		// Retriev all the php files of the created tournaments
		foreach (glob("tournaments/*_2*.php") as $file) {
			$file = basename($file, ".php");
			
			array_push($files, $file);
		}
		?>
		<div id="wrapper">
			<div id="content">
				<h1>Toernooien</h1>
				<?php
				if(isset($succes)) {
					echo $succes;
				} elseif(isset($error)) {
					echo $error;
				}
				
				if(sizeof($files) > 0) {
					?>
					<table border="0" cellpadding="0" cellspacing="0">
						<tr>
							<th width="150">
								Jaar
							</th>
							<th width="200">
								1e Plaats
							</th>
							<th width="200">
								2e Plaats
							</th>
							<th width="75">
								&nbsp;
							</th>
							<th width="75">
								&nbsp;
							</th>
						</tr>
						<?php
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
							echo "<input type='submit' name='delete' value='Verwijderen'>\n";
							echo "</form>\n";
							echo "</td>\n";
							echo "</tr>\n";
							
							echo "</form>\n";
						}
						?>
					</table>
				<?php
				} else {
					echo "Er staan nog geen toernooien in het systeem.";
				}
				
				echo "<br><br>\n";
				echo "<form name='add' method='post' action='".$_SERVER['PHP_SELF']."'>\n";
				echo "<table border='0' cellpadding='0' cellspacing='0'>\n";
				echo "<tr><td width='210'>\n";
				if(isset($_POST['submit'])) {
					echo "<input type='text' name='name' value='".$_POST['name']."' placeholder='Naam'>\n";
				} else {
					echo "<input type='text' name='name' value='' placeholder='Naam'>\n";
				}
				echo "</td><td width='90'>\n";
				echo "<select name='year'>\n";
				echo "<option value='".date("Y")."'>".date("Y")."</option>\n";
				echo "<option value='".(date("Y")+1)."'>".(date("Y")+1)."</option>\n";
				echo "</select>\n";
				echo "</td><td>\n";
				echo "<input type='submit' name='add' value='Nieuw toernooi'>\n";
				echo "</td></tr>\n";
				echo "</table>\n";
				echo "</form>\n";
				?>
			</div>
		</div>
	</body>
</html>