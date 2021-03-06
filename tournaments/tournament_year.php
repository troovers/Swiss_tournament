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
		$player_1 = explode("|", $_POST['M'.$i.'_P1']);
		$player_1_id = $player_1[0];
		$player_1_name = htmlentities($player_1[1], ENT_QUOTES, "UTF-8");
		
		$player_2 = explode("|", $_POST['M'.$i.'_P2']);
		$player_2_id = $player_2[0];
		$player_2_name = htmlentities($player_2[1], ENT_QUOTES, "UTF-8");
	
		$M1PP1 = $_POST['M'.$i.'_PP1'];
		$M1PP2 = $_POST['M'.$i.'_PP2'];
		
		if($M1PP1 > $M1PP2) {
			$update_number_wins = mysqli_query($connect, "UPDATE ".$filename." SET number_wins = number_wins+1 WHERE player_id = '".$player_1_id."'") or die(mysqli_error($connect));
		
			// If it was the last round, get the first and second place
			if($_POST['results'] == 1 && $i == 1) {
				$_SESSION['results']['first'] = $player_1_name;
				$_SESSION['results']['second'] = $player_2_name;
			}
		} else {
			$update_number_wins = mysqli_query($connect, "UPDATE ".$filename." SET number_wins = number_wins+1 WHERE player_id = '".$player_2_id."'") or die(mysqli_error($connect));
		
			if($_POST['results'] == 1 && $i == 1) {
				$_SESSION['results']['first'] = $player_2_name;
				$_SESSION['results']['second'] = $player_1_name;
			}
		}
		
		$update_rounds = mysqli_query($connect, "INSERT INTO ".$filename."_rounds SET round_specifics = '".$_POST['M'.$i.'_ROUND']."', player_1 = '".$player_1_name."', player_2 = '".$player_2_name."', result = '".$M1PP1."-".$M1PP2."'") or die(mysqli_error($connect));
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
		
		<script type="text/javascript" src="../js/jquery-1.12.3.min.js"></script>
		<script type="text/javascript" src="../js/jquery-ui.min.js"></script>
		<link rel="stylesheet" href="../style/jquery-ui.min.css" type="text/css">
		
		<script type="text/javascript" src="../js/hoverIntent.js"></script>
		<script type="text/javascript" src="../js/functions.js"></script>
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
				echo "<div id='response'></div>\n";
				
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
								echo "<th valign='top' align='right' width='20'>".$i."</td>\n";
								echo "<td align='center'>\n";
								
								echo "<div class='rondes' id='ronde_".$i."'>";
								
								$z = 1;
								
								// Create the poule div
								for($y = $i; $y >= 1; $y--) {
									echo "<div class='wedstrijden'>";
									
									// Current round is greater than i, so retrieve the results of the matches that have been played
									if($round > $i) {								
										$played_matches = mysqli_query($connect, "SELECT * FROM ".$filename."_rounds WHERE round_specifics = 'round_".$i."_wins_".($y-1)."'");
										
										if(!$played_matches) {
											echo log_mysql_error(mysqli_error($connect), __LINE__, __FILE__, true);
			
											break;
										} else {										
											echo "<table border='0' cellpadding='0' cellspacing='0' width='100%'>\n";
											
											while($row = mysqli_fetch_assoc($played_matches)) {
												$match_result = explode("-", $row['result']);
												
												echo "<tr>\n";
												echo "<td valign='middle' align='left' width='15'><b>".$z.".</b></td>\n";
												echo "<td valign='middle' align='left'>\n";
												
												$result = $match_result[0] > $match_result[1] ? "win" : "loss"; 
												echo "<span class='".$result."'>".$row['player_1']."</span>\n";
												
												echo "</td>\n";
												echo "<td valign='middle' align='left' width='15'>-</td>\n";
												echo "<td valign='middle' align='left'>\n";
												
												$result = $match_result[1] > $match_result[0] ? "win" : "loss"; 
												echo "<span class='".$result."'>".$row['player_2']."</span>\n";
												
												echo "</td>\n";
												echo "<td valign='middle' align='left' width='10'>".$match_result[0]."</td>\n";
												echo "<td valign='middle' align='left' width='10'>-</td>\n";
												echo "<td valign='middle' align='left' width='10'>".$match_result[1]."</td>\n";
												echo "</tr>\n";
												
												$z++;
											}
											
											echo "</table>\n";
										}
									} else {	
										// Get the players and put them in the right poule
										$matches = mysqli_query($connect, "SELECT * FROM ".$filename." WHERE number_wins = '".($y-1)."'");
										
										if(!$matches) {
											echo log_mysql_error(mysqli_error($connect), __LINE__, __FILE__, true);
			
											break;
										} else {	
											// Create an array and put the players inside it
											${"round_".$i."_wins_".$y} = array();
											
											while($row = mysqli_fetch_assoc($matches)) {
												array_push(${"round_".$i."_wins_".$y}, array($row['player_id'], $row['name']));
											}
											
											// If the number of players is odd, we create an extra player, called "Bye"
											if(count(${"round_".$i."_wins_".$y}) % 2 != 0) {
												array_push(${"round_".$i."_wins_".$y}, array(0, "Bye"));
											}
											
											// These next lines of code shift the array so that the chances of playing to the same opponent twice, decrease
											if($i >= 2) {
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
												$player_1 = ${"round_".$i."_wins_".$y}[$x][1]; 
												$player_1_id = ${"round_".$i."_wins_".$y}[$x][0]; 
												
												$new_x = count(${"round_".$i."_wins_".$y})-$x-1;										
												
												$player_2 = ${"round_".$i."_wins_".$y}[$new_x][1]; 
												$player_2_id = ${"round_".$i."_wins_".$y}[$new_x][0];
												
												// Check if the players have already played against each other
												$exisiting_match = mysqli_query($connect, "SELECT round_id FROM ".$filename."_rounds WHERE (player_1 = '".$player_1."' AND player_2 = '".$player_2."') OR (player_1 = '".$player_2."' AND player_2 = '".$player_1."')");
												
												echo "<tr>\n";
												
												// Show the match number
												echo "<td valign='middle' align='left' width='15'>\n";
												echo mysqli_num_rows($exisiting_match) == 0 ? "<b>".$z.".</b>\n" : "<font style='color: red; font-weight: bold;'>".$z.".</font>\n";
												echo "</td>\n";
												
												// Next three columns show: Player 1 - Player 2
												echo "<td valign='middle' align='left'>".$player_1."</td>\n";
												echo "<td valign='middle' align='left' width='15'>-</td>\n";
												echo "<td valign='middle' align='left'>".$player_2."</td>\n";
												
												// Show a textbox for the amount of won games for player 1 & fill a hidden textbox with the player as value
												echo "<td valign='middle' align='left' width='15'>\n";

												echo "<input type='hidden' name='M".$z."_P1' value='".$player_1_id."|".$player_1."'>\n";
												
												// If this match contains a player named Bye, and player 1 is Bye, then give him 0 points, and give the other player 1 point
												if($player_1 == "Bye" || $player_2 == "Bye") {
													$points = $player_1 == "Bye" ? 0 : 1;
													
													echo "<input type='text' disabled value='".$points."'>";
													echo "<input type='hidden' name='M".$z."_PP1' value='".$points."'>";
												} else {
													echo "<input type='text' name='M".$z."_PP1' autocomplete='off'>";
												}
												
												echo "</td>\n";
												
												echo "<td valign='middle' align='center' width='10'>-</td>\n";
												
												// Show a textbox for the amount of won games for player 2 & fill a hidden textbox with the player as value
												echo "<td valign='middle' align='left' width='15'>\n";

												echo "<input type='hidden' name='M".$z."_P2' value='".$player_2_id."|".$player_2."'>\n";
												
												// If this match contains a player named Bye, and player 2 is Bye, then give him 0 points, and give the other player 1 point
												if($player_1 == "Bye" || $player_2 == "Bye") {
													$points = $player_2 == "Bye" ? 0 : 1;
													
													echo "<input type='text' disabled value='".$points."'>";
													echo "<input type='hidden' name='M".$z."_PP2' value='".$points."'>";
												} else {
													echo "<input type='text' name='M".$z."_PP2' autocomplete='off'>";
												}
												
												echo "<input type='hidden' name='M".$z."_ROUND' value='round_".$i."_wins_".($y-1)."'>\n";
												echo "</td>\n";
												echo "</tr>\n";
												
												$z++;
											}
											
											echo "</table>\n";
										}
									}
									
									echo "</div>\n";
								}
								
								echo "</div>\n";
								
								echo "</td>\n";
								echo "<th valign='top' align='left' width='20'>".$i."</td>\n";
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