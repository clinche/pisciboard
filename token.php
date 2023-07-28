<?php
	session_start();
	include_once('config.php');
	include_once('secret.php');

	if (!isset($_GET['code']) && !isset($_GET['refresh']))
	{
		header("Location:https://api.intra.42.fr/oauth/authorize?client_id={$uid}&redirect_uri={$after_auth}&response_type=code");
		exit();
	}
	if (isset($_GET['refresh']))
	{
		global $uid, $secret, $after_auth;
		$url = "https://api.intra.42.fr/oauth/token";
		$postParams = [
			'grant_type' => "refresh_token",
			'client_id' => $uid,
			'client_secret' => $secret,
			'refresh_token' => $_SESSION['refreshtok'],
			'redirect_uri'=> $after_auth
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		$reponse = curl_exec($ch);
		curl_close($ch);

		$data = json_decode($reponse);
		if (isset($data->access_token))
		{
			$token = $data->access_token;
			$second_token = $data->refresh_token;
		}
		$_SESSION['token'] = $token;
		$_SESSION['refreshtok'] = $second_token;
		header('Location: /index.php');
	}
	
	if (isset($_GET['code']))
	{
		global $uid, $secret, $after_auth;
		$url = "https://api.intra.42.fr/oauth/token";
		$postParams = [
			'grant_type' => "authorization_code",
			'client_id' => $uid,
			'client_secret' => $secret,
			'code' => $_GET['code'],
			'redirect_uri'=> $after_auth
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
		$reponse = curl_exec($ch);
		curl_close($ch);

		$data = json_decode($reponse);
		if (isset($data->access_token))
		{
			$token = $data->access_token;
			$second_token = $data->refresh_token;
			$_SESSION['token'] = $token;
			$_SESSION['currtokennb'] = 0;
			$_SESSION['currreq'] = 0;
			header('Location: /index.php');
		}
		else
		{
			var_dump($data);
			die();
		}
	}
?>
