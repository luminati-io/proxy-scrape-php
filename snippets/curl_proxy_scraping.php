<?php

// Bright Data proxy details
$proxyUrl = '<BRIGHTDATA_PROXY_HOST>:<BRIGHTDATA_PROXY_PORT>';
$proxyUser = '<BRIGHTDATA_PROXY_USERNAME>:<BRIGHTDATA_PROXY_PASSWORD>';

// target scraping page
$targetUrl = 'https://en.wikipedia.org/wiki/Proxy_server';

// perform a GET request to the target page
// through the Bright Data proxy
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
    // parse the HTML document returned by the server
    $dom = new DOMDocument();
    @$dom->loadHTML($response);

    // extract the text content on the page
    $content = $dom->getElementById('mw-content-text')->textContent;

    // extract the titles from the H2 headings
    $headings = [];
    $headingsNodeList = $dom->getElementsByTagName('h2');
    foreach ($headingsNodeList as $heading) {
        $headings[] = $heading->textContent;
    }

    // extract the titles from the H3 headings
    $headingsNodeList = $dom->getElementsByTagName('h3');
    foreach ($headingsNodeList as $heading) {
        $headings[] = $heading->textContent;
    }

    // print the scraped data
    echo "Content:\n";
    echo $content . "\n\n";

    echo "Headings:\n";
    foreach ($headings as $index => $heading) {
        echo ($index + 1) . ". $heading\n";
    }
}

curl_close($ch);
