<?php

// getting apikey
$apikeyfilename = __DIR__ . '/private/apikey.txt';
$apikeyfile = fopen($apikeyfilename, 'r') or die('Unable to open file!');
$apikey = fread($apikeyfile, filesize($apikeyfilename));
fclose($apikeyfile);

// getting list of currencies
$url = 'https://free.currconv.com/api/v7/currencies?apiKey=' . $apikey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
$r = curl_exec($ch);
if ($r === false) {
	die('Failed to fetch!');
}
curl_close($ch);

$rj = json_decode($r, true);
$currencies = array_keys($rj['results']);
$currencies = json_encode($currencies);

// storing on currencies.json
$currenciesFile = fopen(__DIR__ . '/currencies.json', 'w');
fwrite($currenciesFile, $currencies);
fclose($currenciesFile);

echo 'Done!';