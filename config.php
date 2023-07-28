<?php
	session_start();
	
	function api_req($url_api)
	{
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
		$resp = curl_exec($curl);
		if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200)
		{
			$_SESSION['error'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			header('Location: /token.php?refresh');
			exit();
		}
		curl_close($curl);
		$data = json_decode($resp);

		return ($data);
	}

	function view_data($data)
	{
		echo "<pre>";
		echo json_encode($data, JSON_PRETTY_PRINT);
		echo "</pre>";
	}
?>
