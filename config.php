<?php
	session_start();
	
	function api_req($url_api)
	{
		/*$_SESSION['currreq'] += 1;
		if ($_SESSION['currreq'] >= 1100)
		{
			$_SESSION['currreq'] = 0;
			choose_token();
			usleep(500000);
		}*/
		$url = "https://api.intra.42.fr{$url_api}";
		
			
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$headers = array(
		   "Authorization: Bearer ".$_SESSION['currtoken']
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$resp = curl_exec($curl);
		if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200)
		{
			error_log("config.php:26 failed api_req to url {$url_api} with token index {$_SESSION['currtokennb']}");
			error_log("config.php:26 got !=200 http response. entering choose_token function");
			choose_token();
			usleep(300000);
			return (api_req($url_api));
		}
		curl_close($curl);
		$data = json_decode($resp);
		return ($data);
	}

	function choose_token()
	{
		$url = "https://api.intra.42.fr/v2/me";
		$bool = 1;
		while ($bool)
		{
			usleep(500000);
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			
			$currtoken = $_SESSION['currtokennb'];
			$headers = array(
			   "Authorization: Bearer {$_SESSION['tokens'][$currtoken]}"
			);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			$resp = curl_exec($curl);
			curl_close($curl);
			$data = json_decode($resp);
			
			if (isset($data->login) && ($data->login == 'ldournoi' || $data->login == 'johrober'))
			{
				error_log("config.php:59 choose_token(): found a valid token. index: {$currtoken}");
				$_SESSION['currtokennb'] = $currtoken;
				$_SESSION['currtoken'] = $_SESSION['tokens'][$currtoken];
				return ($_SESSION['currtoken']);
			}
			else if (isset($data->message) && $data->message == 'The access token expired')
			{
					error_log("config.php:66 choose_token(): tokens expired. calling renew_tokens()");
					renew_tokens();
					continue ;
			}
			else if (!isset($data->login))
			{
				error_log("config.php:72 choose_token(): token number {$currtoken} probably rate limited. trying another");
				if ($_SESSION['currtokennb'] == 4)	
					$_SESSION['currtokennb'] = 0;
				else
					$_SESSION['currtokennb'] += 1;
			}
		}
		return ($_SESSION['tokens'][$_SESSION['currtokennb']]);
	}

	function renew_tokens()
	{
		header('Location:/exams/token1.php?refresh');
		exit();
	}

	function view_data($data)
	{
		echo "<pre>";
		echo json_encode($data, JSON_PRETTY_PRINT);
		echo "</pre>";
	}
?>
