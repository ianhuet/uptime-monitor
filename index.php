<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
$config = require 'config.php';


// Load .ENV configuration
$dotenv = new Dotenv\Dotenv(__DIR__, '../.env.uptime-monitor');
$dotenv->load();

// Create app instance
$app = new \Slim\App($config);

// DIC configuration
$container = $app->getContainer();

// Database
$container['db'] = function ($c) {
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

  $timestamp = time();
  $datetime  = gmdate("Y-M-d H:i:s");
  $status = ($retcode === 200) ? true : false;
  
  try {
    $sql = "INSERT INTO log (`timestamp`, `datetime`, `status`) VALUES (?, ?, ?)";
    $qry = $this->db->prepare($sql);
    $qry->execute([
      $timestamp,
      $datetime,
      $status
    ]);
  }
  catch(PDOException $e) {
    error_log($e->getMessage(), 0);
  }

  $jsonResponse = $response->withJson(array('status' => $status));
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
