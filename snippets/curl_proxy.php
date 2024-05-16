<?php

// the URL of the proxy server
$proxyUrl = 'http://localhost:80';
// the URL of the target site
$targetUrl = 'https://httpbin.org/get';

// initialize a cURL session
$ch = curl_init();

// set the target URL
curl_setopt($ch, CURLOPT_URL, $targetUrl);
// set the proxy server to be used for routing the request
curl_setopt($ch, CURLOPT_PROXY, $proxyUrl);
// return the response as a string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// disable SSL certificate verification to avoid certificate errors
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// execute the cURL request
$response = curl_exec($ch);

// if the response is not successful
if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
} else {
    echo $response;
}

// close the cURL session
curl_close($ch);
