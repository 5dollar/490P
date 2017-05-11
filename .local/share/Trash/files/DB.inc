<?php
require_once('path.inc');
require_once('host_info.inc');
require_once('rabbitmqlib.inc');

Class Database
{
	private $conn;
	
	public function connect()
	{
	try
	{
		$this->conn = new PDO('mysql:host=localhost; dbname= login', 'root', 'Jrosal1219');
		
		return true;
	}
	catch (PDOException $error)
	{
		$msg = 'Error: ' . $error->getMessage();
		$client = new RabbitMQClient("testRabbitMQ.ini", "testServer");
		$request = array();
		$request['type'] = "log";
		$request['message'] = $msg
		$response = $client->publish($request);
	}
	return false;
	}

	public function RetrieveUser ($username, $password)
	{
		$stmt = $this->conn->prepare('SELECT fname, lname FROM registered WHERE username = :uname AND password = :pwd');
		$stmt->bindParam(':uname', $username);
		$stmt->bindParam(':pwd', $password);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->execute();

		$result = $stmt->fetch();
		echo "User Name: " . $result['uname'];
		return $result;
	}
	public function RegisterUser($username, $password, $fname, $lname, $email)
	{
		$DB = mysql_connect ('localhost', 'root', 'Jrosal1219');
		mysql_select_db('login');
		$query = ("SELECT * FROM registered WHERE username = '$username'");
		$t = mysql_query($query);
		if (mysql_num_rows($t) > 0)
		{	
			echo "Username already in the database. Please try again";
			return false;
		}
		else
		{
			$this->conn = new PDO('mysql:host=localhost; dbname= login ', 'root', 'Jrosal1219');
			$stmt = $this->conn->prepare("INSERT INTO registered (username, password, fname, lname, email) VALUES (:uname, :pwd, :fname, :lname, :email, NULL)");
			$stmt->bindParam(':uname', $username);
	                $stmt->bindParam(':pwd', $password);
			$stmt->bindParam('fname', $fname);
			$stmt->bindParam('lname', $lname);
			$stmt->bindParam(':email', $email);
			
			return $stmt->execute();
			return true;
		}
	}

	public function __destruct()
	{
		$this->conn = null;
	}
	

}
?>
