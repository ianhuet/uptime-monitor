<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

$app = new \Slim\App;

$app->get('/', function (Request $request, Response $response) {
  $url = "http://ianhuet.quickconnect.to";

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_NOBODY, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_exec($ch);
  $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if (200==$retcode) {
    $response->getBody()->write("Online");
  } else {
    $response->getBody()->write("Offline");
  }

  return $response;
});

$app->get('/check', function (Request $request, Response $response) {
  $url = "http://ianhuet.quickconnect.to";

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_NOBODY, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_exec($ch);
  $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if (200==$retcode) {
    $response->getBody()->write("Online");
  } else {
    $response->getBody()->write("Offline");
  }

  return $response;
});

// $app->get('/data/{name}', function (Request $request, Response $response, array $args) {
//   $name = $args['name'];
//   $response->getBody()->write("Hello, $name");

//   return $response;
// });

$app->run();