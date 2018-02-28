<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

// Load .ENV configuration
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

// Create app instance
$app = new \Slim\App();


$app->get('/check', function (Request $request, Response $response) {
  $url = getenv('UPTIME_ENDPOINT');

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_NOBODY, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_exec($ch);
  $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($retcode === 200) {
    $data = array('status' => 'online');
  } else {
    $data = array('status' => 'offline');
  }
  $jsonResponse = $response->withJson($data);
  return $jsonResponse;
});

$app->get('/', function (Request $request, Response $response) {
  // return log from the last 1 day as JSON
    // do log retrieval
    // populate $log with retrieved data

  $jsonResponse = $response->withJson(array('log' => 'no data available'));
  return $jsonResponse;
});

$app->get('/log/{days}', function (Request $request, Response $response, array $args) {
  // return log from the last {days} day as JSON
    // do log retrieval
    // populate $log with retrieved data

    // $days = $args['days'];

  $jsonResponse = $response->withJson(array('log' => 'no data available'));
  return $jsonResponse;
});

$app->run();