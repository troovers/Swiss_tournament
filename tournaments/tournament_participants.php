<?php
session_start();
include("../connect.php");
include("../functions.php");


// Get the tournament name
if(isset($_GET['edition'])) {
	$filename = $_GET['edition'];
} elseif(isset($_POST['add'])) {
	$filename = $_POST['filename'];
} elseif(isset($_POST['delete'])) {
	$filename = $_POST['filename'];
} else {
	header("Location: index.php");
	exit();
}

// Check whether the tournament is finished and has started yet
$finished = mysqli_query($connect, "SELECT first, second FROM tournament_results WHERE filename = '".$filename."'");
$started = mysqli_query($connect, "SELECT round_id FROM ".$filename."_rounds");

if(mysqli_num_rows($finished) > 0) {
	$is_finished = TRUE;
} else {
	$is_finished = FALSE;
}

if(mysqli_num_rows($started) > 0) {
	$is_started = TRUE;
} else {
	$is_started = FALSE;
}


// Add a participant to the tournament
if(isset($_POST['add']) && !empty($_POST['name'])) {
	$name = htmlentities($_POST['name'], ENT_QUOTES, "UTF-8");
	
	$get_participant = mysqli_query($connect, "SELECT player_id FROM ".$filename." WHERE name = '".$name."'");
	
	if(mysqli_num_rows($get_participant) == 0) {	
		$add_participant = mysqli_query($connect, "INSERT INTO ".$filename." SET name = '".$name."'");
		
		unset($_POST);
		
		$succes = "<div id='succes'>De deelnemer is toegevoegd</div>";
	} else {
		$error = "<div id='error'>Deze deelnemer staat al in de lijst</div>";
	}
} elseif(isset($_POST['add']) && empty($_POST['name'])) {
	$error = "<div id='error'>U heeft geen naam ingevuld</div>";
}


// Delete a participant
if(isset($_POST['delete'])) {
	$player_id = $_POST['id'];
	
	$remove_participant = mysqli_query($connect, "DELETE FROM ".$filename." WHERE player_id = '".$player_id."'");
	
	$succes = "<div id='succes'>De deelnemer is verwijderd</div>";
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo str_replace("_", " ", $filename); ?> - Deelnemers</title>
		<link rel="stylesheet" href="../style/participants.css" type="text/css">
	</head>
	<body>
		<div id="wrapper">
			<div id="content">
				<h1><?php echo str_replace("_", " ", $filename); ?> - Deelnemers</h1>
				<?php
				
				// If the table with participants doesn't exist, show an error message
				$table_existence = mysqli_query($connect, "SHOW TABLES LIKE '".$filename."'");
				$table_exists = mysqli_num_rows($table_existence) > 0;

				if($table_exists != TRUE) {
					echo "De tabel met deelnemers bestaat (nog) niet. Maak deze eerst aan door een nieuwe editie aan te maken.";
					exit();
				}
				
				if(isset($succes)) {
					echo $succes;
				} elseif(isset($error)) {
					echo $error;
				}
				
				
				// If the tournament hasn't started yet, participants can be added
				if($is_finished == FALSE) {
					?>
					<form name="add" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">	
						<table border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td width="200">
									<input type="text" name="name" value="<?php if(isset($_POST['add'])) { echo $_POST['name']; } ?>" placeholder="Naam">
								</td>
								<td width="100">
									<input type="hidden" name="filename" value="<?php echo $filename; ?>">
									<input type="submit" name="add" value="Toevoegen">
								</td>
							</tr>
						</table>
					</form>
					<br><br>
					<?php		
				}
				
				$participants = mysqli_query($connect, "SELECT player_id, name, number_wins FROM ".$filename) or die(mysqli_error($connect));

				if(mysqli_num_rows($participants) == 0) {
					echo "Er zijn nog geen deelnemers toegevoegd.";
				} else {
					echo "<table class='data'>\n";
					echo "<tr>\n";
					echo "<th width='30'>#</th>\n";
					echo "<th width='300'>Naam</th>\n";
					echo "<th width='40'>Winst</th>\n";
					
					// If the tournament is not finished or has started yet, display the delete column
					if($is_finished == FALSE && $is_started == FALSE) {
						echo "<th width='30'>&nbsp;</th>\n";
					}
					
					echo "</tr>\n";
					
					$i = 1;
					
					while($row = mysqli_fetch_assoc($participants)) {
						echo "<tr>\n";
						echo "<td>".$i++."</td>\n";
						echo "<td>".$row['name']."</td>\n";
						echo "<td>".$row['number_wins']."</td>\n";
						
						
						// If the tournament is not finished or has started yet, display the delete button
						if($is_finished == FALSE && $is_started == FALSE) {
							echo "<td>\n";
							echo "<form method='post' action='".$_SERVER['PHP_SELF']."'>\n";
							echo "<input type='hidden' name='id' value='".$row['player_id']."'>\n";
							echo "<input type='hidden' name='filename' value='".$filename."'>\n";
							echo "<input type='submit' name='delete' value=''>\n";
							echo "</form>\n";
							echo "</td>\n";
						}
						
						echo "</tr>\n";
					}
					
					echo "</table>\n";
				}
				
				echo "<br><br><a href='".$filename.".php'>Terug naar het toernooi</a>\n";
				?>
			</div>
		</div>
	</body>
</html>