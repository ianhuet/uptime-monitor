<?php


error_reporting(E_ALL);


// require './vendor/autoload.php';
require '/var/www/vhosts/123/120154/webspace/httpdocs/uptime.huet.info/vendor/autoload.php';


// Load .ENV configuration
$dotenv = new Dotenv\Dotenv('/var/www/vhosts/123/120154/webspace/httpdocs/', '.env.uptime-monitor');
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
  $qry->bindParam(1, $timestamp, PDO::PARAM_INT);
  $qry->bindParam(2, $datetime,  PDO::PARAM_STR);
  $qry->bindParam(3, $status,    PDO::PARAM_INT);
  $result = $qry->execute([ $timestamp, $datetime, $status ]);
}
catch(PDOException $e) {
  error_log($e->getMessage(), 0);
}

echo ($status ? "Online" : "Offline") . " @ $datetime (db logged: $result)", PHP_EOL;