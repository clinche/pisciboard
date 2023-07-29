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
					<option value="<?php echo $campus['id']; ?>"><?php echo $campus['name']; } ?></option>
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
