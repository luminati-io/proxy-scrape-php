# PHP Proxy Server: How to Set Up Proxies in PHP

Learn how to set up a proxy in PHP using cURL, `file_get_contents()`, and Symfony. You'll also see how to use Bright Data's [residential proxies](https://brightdata.com/proxy-types/residential-proxies) in PHP for web scraping and IP rotation. This guide is also available on the [Bright Data blog](https://brightdata.com/blog/how-tos/php-proxy-servers).

- [Requirements](#requirenments)
  - [Setting Up a Local Proxy Server in Apache](#setting-up-a-local-proxy-server-in-apache)
- [Using Proxies in PHP](#using-proxies-in-php)
  - [Proxy Integration With cURL](#proxy-integration-with-curl)
  - [Proxy Integration Using `file_get_contents()`](#proxy-integration-using-file_get_contents)
  - [Proxy Integration in Sympfony](#proxy-integration-in-symfony)
- [Testing Proxy Integration in PHP](#testing-proxy-integration-in-php)
- [Bright Data Proxy Integration in PHP](#bright-data-proxy-integration-in-php)
  - [Residential Proxy Setup](#residential-proxy-setup)
  - [Web Scraping Example Through an Authenticated Proxy](#web-scraping-example-through-an-authenticated-proxy)
  - [Testing IP Rotation](#testing-ip-rotation)

## Requirenments

Verify that you have [PHP 8+](https://www.php.net/downloads.php), [Composer](https://getcomposer.org/download/), and [Apache](https://httpd.apache.org/download.cgi) installed on your machine. Otherwise, download the installers by clicking on the previous links, launch them, and follow the instructions.

Make sure the Apache service is up and running.

Create a folder for your PHP project, enter it, and initialize a new Composer application inside it:

```bash
mkdir <PHP_PROJECT_FOLDER_NAME>
cd <PHP_PROJECT_FOLDER_NAME>
composer init
```

**Note**: On Windows, we recommend using WSL ([Windows Subsystem for Linux](https://learn.microsoft.com/en-us/windows/wsl/install)).

### Setting Up a Local Proxy Server in Apache

Configure your Apache local web server to operate as a forward proxy server.

First, enable the [`mod_proxy`](https://httpd.apache.org/docs/2.4/mod/mod_proxy.html), [`mod_proxy_http`](https://httpd.apache.org/docs/2.4/mod/mod_proxy_http.html), and [`mod_proxy_connect`](https://httpd.apache.org/docs/2.4/mod/mod_proxy_connect.html) modules with these commands:

```bash
sudo a2enmod proxy
sudo a2enmod proxy_http
sudo a2enmod proxy_connect
```

Then, create a new [virtual host configuration file](https://httpd.apache.org/docs/2.4/vhosts/) called `proxy.conf` inside `/etc/apache2/sites-available/` as a copy of the default virtual host configuration file `000-default.conf`:

```bash
cd /etc/apache2/sites-available/
sudo cp 000-default.conf proxy.conf
```

Initialize `proxy.conf` with the proxy definition logic below:

```
<VirtualHost *:80>
    # set the server name to localhost
    ServerName localhost
    # set the server admini email to admin@localhost
    ServerAdmin admin@localhost

    # if the SSL module is enabled
    <IfModule mod_ssl.c>
        # disable SSL to avoid certificate errors
        SSLEngine off
    </IfModule>

    # specify the error log file location
    ErrorLog ${APACHE_LOG_DIR}/error.log
    # specify the access log file location and format
    CustomLog ${APACHE_LOG_DIR}/access.log combined

    # enable proxy logic
    ProxyRequests On
    ProxyVia On

    # define a proxy for all requests
    <Proxy *>
        Order deny,allow
        Allow from all
    </Proxy>
</VirtualHost>
```

Register the new Apache virtual host with:

```bash
sudo a2ensite proxy.conf
```

Lastly, reload the Apache server:

```bash
service apache2 reload
```

You now have a local proxy server listening on `http://localhost:80`.

## Using Proxies in PHP

See how to integrate a proxy in PHP into the following technologies:

- [cURL](https://www.php.net/manual/en/book.curl.php)
- [`file_get_contents()`](https://www.php.net/manual/en/function.file-get-contents.php)
- [Symfony](https://symfony.com/)

### Proxy Integration With cURL

Use the [`CURLOPT_PROXY`](https://curl.se/libcurl/c/CURLOPT_PROXY.html) option to specify a proxy server in PHP using the cURL library, as in the `curl_proxy.php` snippet:

```php
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
```

### Proxy Integration Using `file_get_contents()`

Use the `proxy` option in `file_get_contents()` to set a proxy server, as in the `file_get_contents_proxy.php` snippet below:

```php
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
```

**Note**: The protocol of the proxy server in the `proxy` option needs to be `tcp` and not `http`.

### Proxy Integration in Symfony

Install the [`BrowserKit`](https://symfony.com/doc/current/components/browser_kit.html) and [`HTTP Client`](https://symfony.com/doc/current/http_client.html) Symfony components:

```bash
composer require symfony/browser-kit symfony/http-client
```

Specify a proxy server through the [`proxy`](https://symfony.com/doc/current/http_client.html#http-proxies) option in `HttpClient` when making an HTTP request using `HttpBrowser`, as in the `symfony_proxy.php` snippet:

```php
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
```

## Testing Proxy Integration in PHP

Use the command below to launch any of the three PHP proxy integration scripts:

```bash
php <PHP_SCRIPT_NAME>
```

Regardless of the script you execute, the result will be something like this:

```json
{
  "args": {},
  "headers": {
    "Accept": "*/*",
    "Host": "httpbin.org",
    "X-Amzn-Trace-Id": "Root=1-661ab837-40de4746307643415ec9c659"
  },
  "origin": "XX.YY.ZZ.AA",
  "url": "https://httpbin.org/get"
}
```

Take a look at `access.log`, the Apache log file that keeps track of the requests made by the proxy:

```
tail -n 50 /var/log/apache2/access.log
```

The last line indicates that the request was successfully proxied to `httpbin.org` and the response status code was `200`:

```
::1 - - [13/Apr/2024:18:53:22 +0200] "CONNECT httpbin.org:443 HTTP/1.0" 200 6138 "-" "-"
```

## Bright Data Proxy Integration in PHP

Bright Data provides [premium proxies](https://brightdata.com/proxy-types) that automatically rotate the exit IP for you. Let's see how to use them for web scraping in a PHP script using cURL.

### Residential Proxy Setup

[Sign up for Bright Data](https://brightdata.com/cp/start) to start a free trial. Navigate to the "Proxies & Scraping Infrastructure" dashboard and click "Get Started" on the "Residential Proxy" card.

Follow the procedure, set up a residential proxy, and retrieve the following credentials:

- `<BRIGHTDATA_PROXY_HOST>`
- `<BRIGHTDATA_PROXY_PORT>`
- `<BRIGHTDATA_PROXY_USERNAME>`
- `<BRIGHTDATA_PROXY_PASSWORD>`

### Web Scraping Example Through an Authenticated Proxy

Use the Bright Data authenticated residential proxy to connect to the ["Proxy server" Wikipedia page](https://en.wikipedia.org/wiki/Proxy_server) and scrape data from it with [`DOMDocument`](https://www.php.net/manual/en/class.domdocument.php), as in the `curl_proxy_scraping.php` snippet:

```php
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
```

The output will be:

```
Content:
Computer server that makes and receives requests on behalf of a user
.mw-parser-output .hatnote{font-style:italic}.mw-parser-output div.hatnote{padding-left:1.6em;margin-bottom:0.5em}.mw-parser-output .hatnote i{font-style:normal}.mw-parser-output .hatnote+link+.hatnote{margin-top:-0.5em}For Wikipedia's policy on editing from open proxies, please see Wikipedia:Open proxies. For other uses, see Proxy.


Communication between two computers connected through a third computer acting as a proxy server.
// omitted for brevity...

Headings:
1. Contents
2. Types[edit]
3. Uses[edit]
// omitted for brevity...
```

### Testing IP Rotation

Run the PHP proxy script `curl_proxy_brightdata.php` that targets [`http://lumtest.com/myip.json`](http://lumtest.com/myip.json), a special endpoint that returns information about your IP:

```php
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
```

Execute the script several times. Each time, you'll see different IPs from different locations.
