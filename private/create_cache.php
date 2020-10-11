<?php

require_once __DIR__ . '/config.php';

$breakline = "\r\n";

echo 'start of create_cache.php\n' . $breakline;

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
echo 'getting apikey...' . $breakline;
$apikey = _readfile(__DIR__ . '/apikey.txt');

// getting list of currencies
echo 'getting list of currencies...' . $breakline;
$currencies = _readfile(__DIR__ . '/../currencies.json', '[]');
$currencies = json_decode($currencies, true);
echo count($currencies) . ' currencies to be loaded' . $breakline;

// load current cache
echo 'load current cache...' . $breakline;
$cachefilename = __DIR__ . '/../cache_' . Config::baseCurrency . '.json';
$cache = _readfile($cachefilename, '{}');
$cache = json_decode($cache, true);

// load current index position
echo 'load current index...' . $breakline;
$indexfilename = __DIR__ . '/index';
$index = _readfile($indexfilename, '0');
$index = intval($index);

// do
echo 'determining what currencies to be fetched...' . $breakline;
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

echo 'fetching ' . join(', ', $target) . '...' . $breakline;
for ($i = 0; $i < $numOfRequests; $i += 2) {
	$url = 'https://free.currconv.com/api/v7/convert?apiKey=' . $apikey . '&q=' .  Config::baseCurrency . '_' . $target[$i] . ',' . Config::baseCurrency . '_' . $target[$i + 1] . '&compact=ultra';

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$r = curl_exec($ch);
	if ($r === false) {
		echo 'FAILED to fetch: ' . $target[$i] . ' and ' . $target[$i + 1] . $breakline;
	}
	curl_close($ch);

	$rj = json_decode($r, true);
	$cache[$target[$i]] = $rj[Config::baseCurrency . '_' . $target[$i]];
	$cache[$target[$i + 1]] = $rj[Config::baseCurrency . '_' . $target[$i + 1]];

	echo 'Success to fetch: ' . $target[$i] . ' and ' . $target[$i + 1] . $breakline;
}

// write cache back to file
echo 'updating cache file...' . $breakline;
$cache = json_encode($cache);
$cachefile = fopen($cachefilename, 'w');
fwrite($cachefile, $cache);
fclose($cachefile);

// write index back to file
echo 'updating index file...' . $breakline;
$indexfile = fopen($indexfilename, 'w');
fwrite($indexfile, $index);
fclose($indexfile);

echo 'finished!';