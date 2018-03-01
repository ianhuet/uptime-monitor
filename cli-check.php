<?php

require './vendor/autoload.php';


// Load .ENV configuration
$dotenv = new Dotenv\Dotenv(__DIR__, '../.env.uptime-monitor');
$dotenv->load();

// Create app instance
$app = new \Slim\App();

// DIC configuration
$container = $app->getContainer();

// Database
$container['db'] = function () {
  $db_path = getenv('DB_PATH');
  $db = new PDO("sqlite:$db_path");
  return $db;
};


$curl = new Curl\Curl();
$url = getenv('UPTIME_ENDPOINT');
$curl->get($url);
if ($curl->error) {
  error_log('Error: ' . $curl->errorCode . ': ' . $curl->errorMessage, 0);
  $status = false;
} else {
  $status = ($curl->http_status_code === 200) ? true : false;
}


try {
  $sql = "INSERT INTO log (`timestamp`, `datetime`, `status`) VALUES (?, ?, ?)";
  $timestamp = time();
  $datetime  = gmdate("Y-M-d H:i:s");
  $qry = $container['db']->prepare($sql);
  $qry->execute([ $timestamp, $datetime, $status ]);
}
catch(PDOException $e) {
  error_log($e->getMessage(), 0);
}

echo ($status ? "Online" : "Offline") . " @ $datetime", PHP_EOL;