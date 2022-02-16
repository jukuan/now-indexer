<?php

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

$indexNowKey = getenv('INDEX_NOW_KEY');
$linksList = getenv('LINKS_LIST');

if (false === strpos($linksList, '/') && false === strpos($linksList, '\\')) {
    $linksList = __DIR__ . '/' . $linksList;
}


if (!file_exists($linksList) || !is_readable($linksList) || '' === $indexNowKey) {
    die('Environment variables are not set');
}

$content = file_get_contents($linksList);
$links = explode("\n", $content);
$links = array_map('trim', $links);

$client = new GuzzleHttp\Client();

foreach ($links as $link) {
    $url = prepareLink($link, $indexNowKey);

    if ('' === $url) {
        continue;
    }

    try {
        $res = $client->request('GET', $url);
        $code = $res->getStatusCode();
        echo sprintf("%s: %s\n", $link, $code);
    } catch (\GuzzleHttp\Exception\GuzzleException $e) {
    }
}

echo 'Done.' . "\n";

function prepareLink(string $link, string $key): string{
    if ('' === $link || '' === $key) {
        return '';
    }

    return sprintf('https://api.indexnow.org/indexnow?url=%s&key=%s', $link, $key);
}
