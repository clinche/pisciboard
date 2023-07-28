<?php
include_once('config.php');
session_start();

$curl = curl_init();
$token = $_SESSION['token'];
$url = "https://api.intra.42.fr/v2/me";

curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$headers = array(
   "Authorization: Bearer ".$token
);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$resp = curl_exec($curl);
curl_close($curl);
echo curl_getinfo($curl, CURLINFO_HTTP_CODE);
$data = json_decode($resp);
view_data($data);

