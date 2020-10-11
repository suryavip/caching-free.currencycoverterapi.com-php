<?php

require_once __DIR__ . '/config.php';

// getting apikey
$apikeyfilename = __DIR__ . '/apikey.txt';
$apikeyfile = fopen($apikeyfilename, 'r') or die('Unable to open apikey.txt file!');
$apikey = fread($apikeyfile, filesize($apikeyfilename));
fclose($apikeyfile);

// getting list of currencies
$currenciesfilename = __DIR__ . '/../currencies.json';
$currenciesfile = fopen($currenciesfilename, 'r') or die('Unable to open ../currencies.json file!');
$currencies = fread($currenciesfile, filesize($currenciesfilename));
fclose($currenciesfile);
$currencies = json_decode($currencies);

echo join('-', $currencies);

