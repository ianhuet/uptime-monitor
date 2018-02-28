<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
$config = require 'config.php';


// Load .ENV configuration
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

// Create app instance
$app = new \Slim\App($config);

// DIC configuration
$container = $app->getContainer();

// Database
$container['db'] = function ($c) {
  // $pdo = new PDO('sqlite:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME'));
    // , getenv('DB_USER'), getenv('DB_PASS'));

  $db_path = getenv('DB_PATH');
  $db = new PDO("sqlite:$db_path");
  // $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  // $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  return $db;
};




// REQUEST END-POINTS
// ==========================================================================

$app->get('/check', function (Request $request, Response $response) {
  $url = getenv('UPTIME_ENDPOINT');

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_NOBODY, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_exec($ch);
  $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  $status = ($retcode === 200) ? true : false;
  $timestamp = time();
  $datetime  = gmdate("Y-M-d H:i:s");
  $sql = "INSERT INTO log (`id`, `timestamp`, `datetime`, `status`) VALUES (null, $timestamp, $datetime, $status)";
  $this->db->prepare($sql);

  $log = array(
    'timezone' => $timestamp,
    'datetime' => $datetime,
    'status'   => $status
  );
  $jsonResponse = $response->withJson($log);
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