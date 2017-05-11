#!/usr/bin/php
<?php
require_once('path.inc');
require_once('host_info.inc');
require_once('rabbitmqlib.inc');
require_once('DB.inc');
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
$ip_addr = '192.168.2.4';
$connection = new AMQPStreamConnection($ip_addr, 5672, 'admin', 'password');
$channel = $connection->channel();
$channel->queue_declare('rpc_queue', false, false, false, false);



function doLogin($username,$password)
{
	$DB = new Database();
	
	if (!$DB->connect());
	{
		return array("returnCode" => '1', 'message'=>"Connection to Server Failed!");
	}

	$userinfo = $DB->RetrieveUser($username, $password);

	if ($userinfo)
	{
		return (array('returnCode' => '0', 'message'=>'Server received the information!') + $userinfo);
	}
	else
	{
		return array('returnCode' => '1', 'message'=>'Login Failed! Try Again');
	}

}





function doRegister($request)
{
	$DB = new Database();

	if ($DB->RegisterUser($request['username'], $request['password'], $request['fname'], $request['lname'], $request['email']))
	{
		return array("returnCode" => '1', 'message' => "Registered Successfully!");
	}
	else
	{	
		return array("returnCode" => '0', 'message' => "Registration Failed!<br>User already in database!");
	}
)

function logError($request)
{
	$log = fopen("logerror.txt", "a");

	fwrite($log, $request['message'] . '\n\n');

	return true;
}




function requestProcessor($request)
{ 
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  { 
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "Register":
      return doRegister($request); 
    case "Login":
      return doLogin($request['username'],$request['password']);
    case "Log":
      return logError($request);
    case "validate_session":
      return doValidate($request['sessionId']);
  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

$server->process_requests('requestProcessor');

exit();
$channel->basic_qos(null, 1, null);
$channel->basic_consume('rpc_queue', '', false, false, false, false, $callback);
while(count($channel->callbacks)) {
    $channel->wait();
}
$channel->close();
$connection->close();
?>
?>

