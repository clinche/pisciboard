<?php

function logger($msg)
{
	$fileinfo = 'nofileinfo';
	$backtrace = debug_backtrace();
	if (!empty($backtrace[0]) && is_array($backtrace[0]))
		$fileinfo = $backtrace[0]['file'] . ':' . $backtrace[0]['line'];
	error_log('['.$fileinfo.']' . ' - ' . $msg);
}


function api_req($url_api)
{
	if (!isset($_SESSION['token']))
	{
		$_SESSION['error'] = "No token";
		header('Location: /token.php');
		exit();
	}

	$url = "https://api.intra.42.fr{$url_api}";

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$headers = array(
		"Authorization: Bearer ".$_SESSION['token']
		);

	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

	logger("API call: $url_api");

	$resp = curl_exec($curl);

	if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200)
	{
		$_SESSION['error'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		logger("API call error: " . curl_getinfo($curl, CURLINFO_HTTP_CODE));
		header('Location: /token.php?refresh');
		exit();
	}

	curl_close($curl);
	$data = json_decode($resp);

	return ($data);
}

function get_tokens($code)
{
	global $uid, $secret, $after_auth;
	$url = "https://api.intra.42.fr/oauth/token";
	$postParams = [
		'grant_type' => "authorization_code",
		'client_id' => $uid,
		'client_secret' => $secret,
		'code' => $code,
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
		$_SESSION['refreshtok'] = $second_token;
		$_SESSION['currreq'] = 0;
	}
	else
	{
		var_dump($data);
		die();
	}
}

function refresh_tokens()
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
}

function get_campuses()
{
	$data = api_req("/v2/campus?per_page=100");
	$campuses = [];
	foreach ($data as $campus)
	{
		array_push($campuses, ['name' => $campus->name,
							   'id' => $campus->id]);
	}
	sort($campuses);
	return ($campuses);
}

?>
