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

  try {
    $dayAgo = time() - (24 * 60 * 60);
    $sql = "SELECT * FROM log WHERE `timestamp` >= $dayAgo";
    $sth = $this->db->query($sql);
  }
  catch(PDOException $e) {
    error_log($e->getMessage(), 0);
  }

  // return $response->getBody()->write(json_encode($sth->fetchAll(PDO::FETCH_CLASS)));

  $jsonResponse = $response->withJson(json_encode($sth->fetchAll(PDO::FETCH_CLASS)));
  return $jsonResponse;
});

// $app->get('/log/{days}', function (Request $request, Response $response, array $args) {
//   // return log from the last {days} day as JSON
//     // do log retrieval
//     // populate $log with retrieved data

//     // $days = $args['days'];

//   $jsonResponse = $response->withJson(array('log' => 'no data available'));
//   return $jsonResponse;
// });

$app->run();