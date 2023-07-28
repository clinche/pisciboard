<?php
session_start();
include_once("config.php");

if (!isset($_SESSION['token']))
	header('Location: /token.php');
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
					<option value="july">July</option>
					<option value="august">August</option>
					<option value="september">September</option>
				</select>
				<select id="year-select">
					<option value="2022">2022</option>
					<option value="2023">2023</option>
					<option value="2024">2024</option>
				</select>
				<select id="exam-select">
					<option value="c-piscine-exam-00">Exam 00</option>
					<option value="c-piscine-exam-01">Exam 01</option>
					<option value="c-piscine-exam-02">Exam 02</option>
					<option value="c-piscine-final-exam">Exam Final</option>
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
			<p>📝 Frontend : dguet / rlaforge / ssubielo / ldournoi - 🛠️ Backend : ldournoi</p>
		</footer>
		<script type="text/javascript" src="./script.js"></script>
	</body>
</html> 
