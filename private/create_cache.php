<?php

require_once __DIR__ . '/config.php';

$breakline = "\r\n";

function _readfile(string $filename, string $default='')
{
	if (!file_exists($filename)) {
		// create the file
		$file = fopen($filename, 'w');
		fwrite($file, $default, strlen($default));
		fclose($file);
		return $default;
	}
	$file = fopen($filename, 'r');
	$read = fread($file, filesize($filename));
	fclose($file);
	return $read;
}

// getting apikey
$apikey = _readfile(__DIR__ . '/apikey.txt');

// getting list of currencies
$currencies = _readfile(__DIR__ . '/../currencies.json', '[]');
$currencies = json_decode($currencies, true);

// load current cache
$cachefilename = __DIR__ . '/../cache_' . Config::baseCurrency . '.json';
$cache = _readfile($cachefilename, '{}');
$cache = json_decode($cache, true);

// load current index position
$indexfilename = __DIR__ . '/index';
$index = _readfile($indexfilename, '0');
$index = intval($index);

// do
$numOfRequests = Config::numberOfRequests * 2;
if ($numOfRequests > count($currencies)) {
	$numOfRequests = count($currencies);
}
$target = [];
while (count($target) < $numOfRequests) {
	if ($index >= count($currencies)) {
		$index = 0;
	}
	if ($currencies[$index] != Config::baseCurrency) {
		array_push($target, $currencies[$index]);
	}
	$index++;
}

for ($i = 0; $i < $numOfRequests; $i += 2) {
	if ($i > 0) sleep(Config::waitBetweenRequest);

	$subKey1 = $target[$i];
	$subKey2 = $target[$i + 1];
	$key1 = Config::baseCurrency . '_' . $subKey1;
	$key2 = Config::baseCurrency . '_' . $subKey2;

	$url = 'https://free.currconv.com/api/v7/convert?apiKey=' . $apikey . '&q=' .  $key1 . ',' . $key2 . '&compact=ultra';

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$r = curl_exec($ch);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if ($r === false) {
		echo 'FAILED: ' . $subKey1 . ', ' . $subKey2 . $breakline;
		continue;
	}
	if ($http_code != 200) {
		echo 'FAILED (' . $http_code . '): ' . $subKey1 . ', ' . $subKey2 . $breakline;
		continue;
	}

	$rj = json_decode($r, true);

	if (array_key_exists($key1, $rj)) $cache[$subKey1] = '' . $rj[$key1];
	else echo 'FAILED (key_not_found): ' . $subKey1 . $breakline;

	if (array_key_exists($key2, $rj)) $cache[$subKey2] = '' . $rj[$key2];
	else echo 'FAILED (key_not_found): ' . $subKey2 . $breakline;
}

// write cache back to file
$cache = json_encode($cache);
$cachefile = fopen($cachefilename, 'w');
fwrite($cachefile, $cache);
fclose($cachefile);

// write index back to file
$indexfile = fopen($indexfilename, 'w');
fwrite($indexfile, $index);
fclose($indexfile);
