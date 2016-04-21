<?php
session_start();
include("../connect.php");
include("../functions.php");

unset($_SESSION['results']);

// Get the tournament filename
$filename = basename(__FILE__, ".php");
$edition = explode("_", $filename);

// Check if the tables of the tournament exist
$table_existence = mysqli_query($connect, "SHOW TABLES LIKE '".$filename."'");
$table_exists = mysqli_num_rows($table_existence) > 0;

if($table_exists == TRUE) {
	// Get all of the participants
	$participants = mysqli_query($connect, "SELECT player_id, name FROM ".$filename);

	if(mysqli_num_rows($participants) != 0) {
		if(mysqli_num_rows($participants) % 2 != 0) {
			$number_participants = mysqli_num_rows($participants)+1;
		} else {
			$number_participants = mysqli_num_rows($participants);
		}

		// Calculate the number of rounds which have to be played
		$number_rounds = ceil(sqrt($number_participants));

		$rounds = mysqli_query($connect, "SELECT round_specifics FROM ".$filename."_rounds ORDER BY round_id DESC LIMIT 1") or die(mysqli_error($connect));
		$round = mysqli_fetch_assoc($rounds);

		if(mysqli_num_rows($rounds) != 0) {
			$round_specifics = explode("_", $round['round_specifics']);
			
			$_SESSION['round'] = $round_specifics[1]+1;
			$round = $_SESSION['round'];
		} else {
			$round = 1;
			$_SESSION['round'] = $round;
			unset($_SESSION['results']);
		}

		
		// Add al of the participants to an array
		$all_participants = array();

		while($participant = mysqli_fetch_assoc($participants)) {
			$all_participants[$participant['player_id']] = $participant['name'];
		}
	}
}

if(isset($_POST['save'])) {

	// For every match played in the round, save the results to the database
	for($i = 1; $i <= $_POST['number_matches']; $i++) {
		$M1P1 = $_POST['M'.$i.'_P1'];
		$M1P2 = $_POST['M'.$i.'_P2'];
	
		$M1PP1 = $_POST['M'.$i.'_PP1'];
		$M1PP2 = $_POST['M'.$i.'_PP2'];
		
		if($M1PP1 > $M1PP2) {
			$update_number_wins = mysqli_query($connect, "UPDATE ".$filename." SET number_wins = number_wins+1 WHERE name = '".$M1P1."'") or die(mysqli_error($connect));
		
			// IF it was the last round, get the first and second place
			if($_POST['results'] == 1 && $i == 1) {
				$_SESSION['results']['first'] = $M1P1;
				$_SESSION['results']['second'] = $M1P2;
			}
		} else {
			$update_number_wins = mysqli_query($connect, "UPDATE ".$filename." SET number_wins = number_wins+1 WHERE name = '".$M1P2."'") or die(mysqli_error($connect));
		
			if($_POST['results'] == 1 && $i == 1) {
				$_SESSION['results']['first'] = $M1P2;
				$_SESSION['results']['second'] = $M1P1;
			}
		}
		
		$update_rounds = mysqli_query($connect, "INSERT INTO ".$filename."_rounds SET round_specifics = '".$_POST['M'.$i.'_ROUND']."', player_1 = '".$M1P1."', player_2 = '".$M1P2."', result = '".$M1PP1."-".$M1PP2."'") or die(mysqli_error($connect));
	}
	
	// Increase the round
	$_SESSION['round'] += 1;
	$round = $_SESSION['round'];
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo ucfirst(str_replace("_", " ", $filename)); ?></title>
		<link rel="stylesheet" href="../style/style.css" type="text/css">
	</head>
	<body style="min-width: 100%; float: left;">
		<div id="wrapper">
			<div id="header">
				<h1><?php echo str_replace("_", " ", $filename); ?></h1>
				<div id="nav_links">
					<a href="tournament_participants.php?edition=<?php echo $filename; ?>">Deelnemersbeheer</a><br>
					<a href="../index.php">Toernooibeheer</a>
				</div>
			</div>
			<div id="content">
				<?php
				// If the tables do not exist, create them
				if($table_exists == FALSE) {
					echo "<br><br>De vereiste tabellen voor het toernooi worden aangemaakt. Een moment geduld alstublieft.";
					createNewEdition($filename);
					
					echo "<meta http-equiv='refresh' content='3' />";
				} else {
					if(mysqli_num_rows($participants) == 0) {
						echo "<br><br>De tabellen zijn aangemaakt.<br>Er zijn nog geen deelnemers toegevoegd aan het toernooi.";
					} else {				

						// Within this for-loop, every round and poule is begin created
						for($i = 1; $i <= $number_rounds; $i++) {
							if($round >= $i) {
								if($round == $i) {
									echo "<form name='wedstrijden' method='post' action='".$_SERVER['PHP_SELF']."'>\n";
								}
								
								echo "<table border='0' cellpadding='0' cellspacing='0' width='100%'>\n";
								echo "<tr>\n";
								echo "<th valign='top' width='10'>".$i."</td>\n";
								echo "<td align='center'>\n";
								
								echo "<div class='rondes' id='ronde_".$i."'>";
								
								$z = 1;
								
								// Create the poule div
								for($y = $i; $y >= 1; $y--) {
									echo "<div class='wedstrijden'>";
									
									// Current round is greater than i, so retrieve the results of the matches that have been played
									if($round > $i) {								
										$played_matches = mysqli_query($connect, "SELECT * FROM ".$filename."_rounds WHERE round_specifics = 'round_".$i."_wins_".($y-1)."'") or die(mysqli_error($connect));
										
										echo "<table border='0' cellpadding='0' cellspacing='0' width='100%'>\n";
										
										while($row = mysqli_fetch_assoc($played_matches)) {
											$match_result = explode("-", $row['result']);
											?>
											<tr>
												<td valign="middle" align="left" width="15">
													<?php echo "<b>".$z.".</b>"; ?>
												</td>
												<td valign="middle" align="left" width="">
													<?php echo $row['player_1']; ?>
												</td>
												<td valign="middle" align="left" width="15">
													-
												</td>
												<td valign="middle" align="left" width="">
													<?php echo $row['player_2']; ?>
												</td>
												<td valign="middle" align="left" width="10">
													<?php echo $match_result[0]; ?>
												</td>
												<td valign="middle" align="left" width="10">
													-
												</td>
												<td valign="middle" align="left" width="10">
													<?php echo $match_result[1]; ?>
												</td>
											</tr>
											<?php
											
											$z++;
										}
										
										echo "</table>\n";
									} else {				

										// Get the players and put them in the right poule
										$matches = mysqli_query($connect, "SELECT * FROM ".$filename." WHERE number_wins = '".($y-1)."'") or die(mysqli_error($connect));
										
										// Create an array and put the players inside it
										${"round_".$i."_wins_".$y} = array();
										
										while($row = mysqli_fetch_assoc($matches)) {
											array_push(${"round_".$i."_wins_".$y}, $row['name']);
										}
										
										// If the number of players is odd, we create an extra player, called "Bye"
										if(count(${"round_".$i."_wins_".$y}) % 2 != 0) {
											array_push(${"round_".$i."_wins_".$y}, "Bye");
										}
										
										
										// These next lines of code shift the array so that the chances of playing to the same opponent twice, decreases
										if($number_rounds/$i >= 2) {
											for($u = 1; $u < 3; $u++) {
												array_push(${"round_".$i."_wins_".$y}, ${"round_".$i."_wins_".$y}[$u-1]);
												unset(${"round_".$i."_wins_".$y}[$u-1]);
											}
										} else {
											array_push(${"round_".$i."_wins_".$y}, ${"round_".$i."_wins_".$y}[0]);
											unset(${"round_".$i."_wins_".$y}[0]);
										}
										
										${"round_".$i."_wins_".$y} = array_values(${"round_".$i."_wins_".$y});
										
										echo "<table border='0' cellpadding='0' cellspacing='0' width='100%'>\n";
										
										
										// Display the matches
										for($x = 0; $x < (count(${"round_".$i."_wins_".$y})/2); $x++) {
											$player_1 = ${"round_".$i."_wins_".$y}[$x]; 
											
											$new_x = count(${"round_".$i."_wins_".$y})-$x-1;										
											
											$player_2 = ${"round_".$i."_wins_".$y}[$new_x]; 
											
											$exisiting_match = mysqli_query($connect, "SELECT round_id FROM ".$filename."_rounds WHERE (player_1 = '".$player_1."' AND player_2 = '".$player_2."') OR (player_1 = '".$player_2."' AND player_2 = '".$player_1."')");
											?>
											<tr>
												<td valign="middle" align="left" width="15">
													<?php 
													if(mysqli_num_rows($exisiting_match) == 0) {
														echo "<b>".$z.".</b>"; 
													} else {
														echo "<font style='color: red; font-weight: bold;'>".$z.".</font>"; 
													}
													?>
												</td>
												<td valign="middle" align="left">
													<?php
													echo $player_1;
													?>
												</td>
												<td valign="middle" align="left" width="15">
													-
												</td>
												<td valign="middle" align="left">
													<?php 
													echo $player_2;
													?>
												</td>
												<td valign="middle" align="left" width="15">
													<input type="hidden" name="<?php echo "M".$z."_P1"; ?>" value="<?php echo $player_1; ?>">
													<?php 
													if($player_1 == "Bye" || $player_2 == "Bye") {
														if($player_1 == "Bye") {
															$points = 0;
														} else {
															$points = 1;
														}
														
														echo "<input type='text' disabled value='".$points."'>";
														echo "<input type='hidden' name='M".$z."_PP1' value='".$points."'>";
													} else {
														echo "<input type='text' name='M".$z."_PP1' autocomplete='off'>";
													}
													?>
												</td>
												<td valign="middle" align="center" width="10">
													-
												</td>
												<td valign="middle" align="left" width="15">
													<input type="hidden" name="<?php echo "M".$z."_P2"; ?>" value="<?php echo $player_2; ?>">
													<?php 
													if($player_1 == "Bye" || $player_2 == "Bye") {
														if($player_2 == "Bye") {
															$points = 0;
														} else {
															$points = 1;
														}
														
														echo "<input type='text' disabled value='".$points."'>";
														echo "<input type='hidden' name='M".$z."_PP2' value='".$points."'>";
													} else {
														echo "<input type='text' name='M".$z."_PP2' autocomplete='off'>";
													}
													?>
													<input type="hidden" name="<?php echo "M".$z."_ROUND"; ?>" value="<?php echo "round_".$i."_wins_".($y-1); ?>">
												</td>
											</tr>
											<?php
											
											$z++;
										}
										
										echo "</table>\n";
									}
									
									echo "</div>\n";
								}
								
								echo "</div>\n";
								
								echo "</td>\n";
								echo "</tr>\n";
								echo "</table>\n";
								
								if($round == $i) {
									if($round == $number_rounds) {
										$results = 1;
									} else {
										$results = 0;
									}
									
									echo "<input type='hidden' name='number_matches' value='".($z-1)."'>";
									echo "<input type='hidden' name='results' value='".$results."'>";
									echo "<br><br><input type='submit' name='save' value='Opslaan'>\n";
									echo "</form><br><br>\n";
								}
							}
						}
					}
				}
				
				
				// If the session with results of the tournament has been set, display the results and save them to the database
				if(isset($_SESSION['results'])) {
					$get_results = mysqli_query($connect, "SELECT * FROM tournament_results WHERE filename = '".$filename."'") or die(mysqli_error($connect));
			
					if(mysqli_num_rows($get_results) > 0) {
						$update_results = mysqli_query($connect, "UPDATE tournament_results SET first = '".$_SESSION['results']['first']."', second = '".$_SESSION['results']['second']."' WHERE filename = '".$filename."'") or die(mysqli_error($connect));
					} else {
						$insert_results = mysqli_query($connect, "INSERT INTO tournament_results SET filename = '".$filename."', first = '".$_SESSION['results']['first']."', second = '".$_SESSION['results']['second']."'") or die(mysqli_error($connect));
					}
					
					echo "<div id='overlay'>\n";
					echo "</div>\n";
					
					echo "<div id='uitslagen'>\n";
					echo "<br><br><h2>Uitslag</h2>";
					echo "<b>1.</b> ".$_SESSION['results']['first']."<br>";
					echo "<b>2.</b> ".$_SESSION['results']['second']."<br><br><br>";
					echo "</div>\n";
				}
				?>
			</div>
		</div>
	</body>
</html>