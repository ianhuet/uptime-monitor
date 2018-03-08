<?php

require 'vendor/autoload.php';
$config = require 'config.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


// Load .ENV configuration
$dotenv = new Dotenv\Dotenv(__DIR__, '../.env.uptime-monitor');
$dotenv->load();

// Create app instance
$app = new \Slim\App($config);

// DIC configuration
$container = $app->getContainer();

// Database
$container['db'] = function () {
  $db_path = getenv('DB_PATH');
  $db = new PDO("sqlite:$db_path");
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  return $db;
};


// REQUEST END-POINTS
// ==========================================================================

$app->get('/check', function (Request $request, Response $response) {
  // $curl = new Curl\Curl();
  // $url = getenv('UPTIME_ENDPOINT');
  // $curl->get($url);
  // $status = ($curl->http_status_code === 200) ? true : false;

  ob_start();
  include './cli-check.php';
  $result = ob_get_clean();
  $status = (strstr($result,'Online')) ? true : false;

  $jsonResponse = $response->withJson(array(
    'status' => $status
  ));
  return $jsonResponse;
});


// return log from the last 1 day as JSON
$app->get('/', function (Request $request, Response $response) {
  try {
    $dayAgo = time() - (24 * 60 * 60);
    $sql = "SELECT * FROM log WHERE `timestamp` >= $dayAgo";
    $sth = $this->db->query($sql);
  }
  catch(PDOException $e) {
    error_log($e->getMessage(), 0);
  }

  $jsonResponse = $response->withJson(json_encode($sth->fetchAll(PDO::FETCH_CLASS)));
  return $jsonResponse;
});

// return log from the last {days} day as JSON
// $app->get('/log/{days}', function (Request $request, Response $response, array $args) {
//     // do log retrieval
//     // populate $log with retrieved data

//     // $days = $args['days'];

//   $jsonResponse = $response->withJson(array('log' => 'no data available'));
//   return $jsonResponse;
// });

$app->run();
