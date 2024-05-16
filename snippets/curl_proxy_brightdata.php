<?php

$proxyUrl = '<BRIGHTDATA_PROXY_HOST>:<BRIGHTDATA_PROXY_PORT>';
$proxyUser = '<BRIGHTDATA_PROXY_USERNAME>:<BRIGHTDATA_PROXY_PASSWORD>';

$targetUrl = 'http://lumtest.com/myip.json';

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $targetUrl);
curl_setopt($ch, CURLOPT_PROXY, $proxyUrl);
curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyUser);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
} else {
    echo $response;
}

curl_close($ch);
