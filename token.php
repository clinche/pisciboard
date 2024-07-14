<?php
	session_start();
	require_once('secret.php');
	require_once('api42.php');

	if (!isset($_GET['code']) && !isset($_GET['refresh']))
	{
		header("Location:https://api.intra.42.fr/oauth/authorize?client_id={$uid}&redirect_uri={$after_auth}&response_type=code");
		exit();
	}

	if (isset($_GET['refresh']))
	{
		refresh_tokens();
		header('Location: /index.php');
		exit();
	}
	
	if (isset($_GET['code']))
	{
		get_tokens($_GET['code']);
		header('Location: /index.php');
		exit();
	}
?>
