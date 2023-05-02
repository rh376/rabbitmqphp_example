#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');



function registerUser($name, $username, $password)
{
    // connect to MySQL database
    $db = mysqli_connect("localhost", "user490", "it490", "userData");

    // check connection
    if (!$db) {
        die("Connection failed: " . mysqli_connect_error());
    }

    echo "DB Connected Successfully";

    // prepare query
    $query = "INSERT INTO users (id, name, username, password) VALUES ('1', '$name', '$username', '$password')";

    // execute query
    if (mysqli_query($db, $query)) {
        return true;
    } else {
      echo "query failed";  
      return false;
    }

    // close database connection
    mysqli_close($db);
}

function requestProcessor($request)
{
    if (!isset($request['type'])) {
        return "ERROR: unsupported message type";
    }

    switch ($request['type']) {
        case "Registration":
          echo "received registration request\n\n";
          passthru("python3 logging_producer.py "."'User data sent to the database'");
          return registerUser($request['name'], $request['username'], $request['password']);
          break;
        
        case "Login":
          echo "received login request\n\n";
          return;
    }

    return array("returnCode" => '0', 'message' => "Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini", "testServer");

echo "testRabbitMQServer BEGIN" . PHP_EOL;
passthru("python3 logging_producer.py "."'Rabbit MQ Server Side Started'");
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END" . PHP_EOL;
exit();

?>
