<?php
session_start();
include_once("config.php");
include_once("api42.php");

if (!isset($_SESSION['token']))
	header('Location: /token.php');
$campuses = get_campuses();
?>

<!DOCTYPE html>
<html>
	<head>
	<title>42 Racing</title>
		<link rel="stylesheet" href="style.css">
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
	</head>
	<body>
		<div id="leaderboard">
			<div class="ladder-title">
				<h1>Standings</h1>
				<select id="month-select">
					<option value="january">January</option>
					<option value="february">February</option>
					<option value="march">March</option>
					<option value="april">April</option>
					<option value="june">June</option>
					<option value="july" selected>July</option>
					<option value="august">August</option>
					<option value="september">September</option>
					<option value="october">October</option>
					<option value="november">November</option>
					<option value="december">December</option>
				</select>
				<select id="year-select">
					<option value="2019">2019</option>
					<option value="2020">2020</option>
					<option value="2021">2021</option>
					<option value="2022">2022</option>
					<option value="2023" selected>2023</option>
					<option value="2024">2024</option>
				</select>
				<select id="exam-select">
					<option value="c-piscine-exam-00">Exam 00</option>
					<option value="c-piscine-exam-01">Exam 01</option>
					<option value="c-piscine-exam-02">Exam 02</option>
					<option value="c-piscine-final-exam" selected>Exam Final</option>
				</select>
				<select id="campus-select">
				<?php foreach($campuses as $campus) { ?>
					<option value="<?php echo $campus['id'].'"'; if ($campus['name'] == "Angouleme") echo "selected"; echo '>' ?> <?php echo $campus['name'];} ?></option>
				</select>
				<button id="letsgo" onclick="main()">Let's goooooooo</button>
				<button id="stop" onclick="letsnotgooooo()" disabled style="display: none">Let's not go :(</button>
				<p id="notice" disabled></p>
			</div>
			<table id="rankings" class="leaderboard-results" width="100%">
				<thead>
					<tr>
						<th>Rank</th>
						<th>Score</th>
						<th>Photo</th>
						<th>Login</th>
						<th>Infos</th>
						<th>Odds</th>
						<th>Gone</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
		<footer>
			<p> dguet / rlaforge / ssubielo / ldournoi -  <a href="https://github.com/clinche/pisciboard"> <img style='max-width:16px;' src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAXCAQAAAC7KEemAAAAIGNIUk0AAHomAACAhAAA+gAAAIDoAAB1MAAA6mAAADqYAAAXcJy6UTwAAAACYktHRAD/h4/MvwAAAAlwSFlzAAA3XQAAN10BGYBGXQAAAAd0SU1FB+cHHgshAqu35ggAAAHaSURBVDjLfZNPSFRRFMa/eW6KQiFhFGmCsVErWrhwFglSgi2FiVZDuktoqS5aBm2G1oJtIhA3QpvWEtoiY8RWLcQh0CnMMYc3YeiIf2J+Ld6Z63sxznc2777z++7lnnNuTJIkZOrQPQ3pjtol+drQJ+W1F6RiciKIOJOsUSWsKl+YIh4QUXyYz1ykFYadxfAsuzTTLlmzIMQoZeCEg4bwAcdAmdG6oYd1AOYZ5AXfqPKTAgW2qbLJSwZ5DcA6PUh4zNhOzxAixQNSdNLBTe5zCyGeUANgBk8MsGOGaXRBPDVihwFPGXVJkk7lB9WOhiTpt44kSV16JPLm/sDVUK2jPbrEe6Pynnot81GHkW4q1N9jLdmy11ObffpqpopqkqQ2z23a2tTQKi840NMf+3VXMTW8g2UD7YtVu853bkdGLDxnSQpGrYqcG4F3dDbsQjtzjsmJNCVgkWX+ssI4qaC8CHGFbrIsW5+hRFq0MAsskuYtUKPEhDOM8YOz0BjO0iJEHwVgniQL/OIr/c7QTyWEb9BXH+8MFY54zGWSxIk5Q4Kiw30y5+9BjFHG5w3PmeQa9Qol2DJ8j/H/39yITVWJbme4QRGokedho1d9nRxltkmGTiji84rEOf4P/jNMd2ufPVAAAAAldEVYdGRhdGU6Y3JlYXRlADIwMjMtMDctMzBUMTE6MzI6NTMrMDA6MDCIxoFLAAAAJXRFWHRkYXRlOm1vZGlmeQAyMDIzLTA3LTMwVDExOjMyOjUzKzAwOjAw+Zs59wAAACh0RVh0ZGF0ZTp0aW1lc3RhbXAAMjAyMy0wNy0zMFQxMTozMzowMiswMDowMK/bdsYAAAAASUVORK5CYII="/></a></p>
		</footer>
		<script type="text/javascript" src="./script.js"></script>
	</body>
</html> 
