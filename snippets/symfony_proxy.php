<?php

// include the Composer autoload file
require './vendor/autoload.php';

// load the required load Symfony components
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

// define the proxy server and port
$proxyServer = 'http://localhost';
$proxyPort = '80';

// create an HTTP client with a proxy configuration
$client = new HttpBrowser(HttpClient::create(['proxy' => sprintf('%s:%s', $proxyServer, $proxyPort)]));

// make a GET request to the target URL
$client->request('GET', 'https://httpbin.org/get');

// get the content of the response
$content = $client->getResponse()->getContent();

// output the content
echo $content;
