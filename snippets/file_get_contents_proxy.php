<?php

// define the proxy server to be used for HTTP/HTTPS requests
$options = [
    'http' => [
        'proxy' => 'tcp://localhost:80',
        // force the use of the full URI when making the request
        'request_fulluri' => true,
    ],
];
// create a stream context with the defined options
$context = stream_context_create($options);

// the URL of the target site
$url = 'https://httpbin.org/get';
// perform an HTTP request with the defined context
$response = file_get_contents($url, false, $context);

// if the response was not successful
if ($response === false) {
    echo "Failed to retrieve data from $url";
} else {
    echo $response;
}
