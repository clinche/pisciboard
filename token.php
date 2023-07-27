<?php
	session_start();
	include_once('config.php');

	if (!isset($_GET['code']) && !isset($_GET['refresh']))
	{
		header('Location:https://api.intra.42.fr/oauth/authorize?client_id=f9c797955fcf3d904068f650778a7fb15eaf480f8b5528b636060a9ce053a175&redirect_uri=https%3A%2F%2Fphp.dournois.fr%2Fexams%2Ftoken1.php&response_type=code');
		exit();
	}
	if (isset($_GET['refresh']))
	{
		$uid = "";
		$secret = "";
		$url = "https://api.intra.42.fr/oauth/token";
		$after_auth = "https://php.dournois.fr/exams/token1.php";
		$postParams = [
			'grant_type' => "refresh_token",
			'client_id' => $uid,
			'client_secret' => $secret,
			'refresh_token' => $_SESSION['refreshtoks'][0],
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
		error_log("token1.php:39 tried refresh and got a new token: {$data->access_token}");
		$_SESSION['tokens'] = array($token);
		$_SESSION['refreshtokstmp'] = array($second_token);
		header('Location:/exams/token2.php?refresh');
		exit();
	}
	
	if (isset($_GET['code']))
	{
		$uid = "";
		$secret = "";
		$url = "https://api.intra.42.fr/oauth/token";
		$after_auth = "https://php.dournois.fr/exams/token1.php";
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
			$_SESSION['currtoken'] = $token;
			$_SESSION['currtokennb'] = 0;
			$_SESSION['tokens'] = array($token);
			$_SESSION['refreshtoks'] = array($second_token);
			$_SESSION['currreq'] = 0;
		}
		else
		{
			var_dump($data);
			die();
		}
		header('Location:/exams/token2.php');
		exit();
	}
?>
