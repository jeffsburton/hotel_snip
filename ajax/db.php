<?php

function openDatabase()
{
	$prod   = str_contains($_SERVER['HTTP_HOST'], "hotel-id.com");
	// Database connection credentials
	$host = 'localhost'; // Replace with your database host

	$userName   = !$prod ? "root" : "hotel-id";
	$password   = !$prod ? "1234" : "TJ9dMJB^e%G9v$5a";
	$dbname = !$prod ? 'hotel-id' : 'hotel-id'; // Replace with your database name
	$port = 3306;

	// Create a connection to the database
	$conn = new mysqli($host, $userName, $password, $dbname, $port);

	// Check if the connection was successful
	if ($conn->connect_error) {
	die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
	}
	return $conn;
}

function select($sql)
{

	// Open a database connection using the provided function
	$conn = openDatabase();

	$args = func_get_args();
	$types = "";
	$boundArgs = [];
	for ($i = 1; $i < count($args); $i++)
		if (gettype($args[$i]) == "integer") {
			$boundArgs[] = $args[$i];
			$types .= "i";
		} else {
			$boundArgs[] = strval($args[$i]);
			$types .= "s";
		}

	$stmt = $conn->prepare($sql);
	if (strlen($types) > 0)
		$stmt->bind_param($types, ...$boundArgs);

	$stmt->execute();
	$result = $stmt->get_result();

	// Check if the query was successful
	$records = [];
	if ($result) {
		while ($row = $result->fetch_assoc()) {
			$records[] = $row;
		}
		// Close the database connection
		$conn->close();
		return $records;

	}
}

function selectAsJson($sql)
{
	$json = ['success' => false, 'message' => 'generic'];

	try {
		$records = select(...func_get_args());

		// Return the sorted data as JSON
		$json = json_encode(['success' => true, 'data' => $records]);
	}
	catch (Exception $e) {
		$json = json_encode(['success' => false, 'message' => $e->getMessage()]);
	}

	return $json;
}
function changeRecord(string $table, array $fields, int $id): int
{

	$types = str_repeat("s", count($fields));

	$args   = [];

	if ($id > 0)
	{
		$sql    = "UPDATE " . $table . " SET ";
		$first  = true;
		$types .= "i";
		foreach ($fields as $key => $value)
		{
			$sql .= ($first ? "" : ", ") . "`" . $key . "`=?";
			$first = false;
			array_push($args, $value);
		}
		$sql .= " WHERE id=?";
		array_push($args, $id);
	}
	else
	{
		$sql = "INSERT INTO " . $table . " (";
		$first  = true;
		foreach ($fields as $key => $value)
		{
			$sql .= ($first ? "" : ", ") . "`" . $key . "`";
			$first = false;
			array_push($args, $value);
		}
		$sql .= ") VALUES (" . substr(str_repeat(", ?", count($args)), 2) . ");";
	}

	/*echo "types: " . $types . "<br>";
	echo "sql: " . $sql . "<br>";
	echo "# args: " . count($args) . "<br>";
	foreach ($args as $key => $value)
		 echo $key . " " . $value . "<br>";*/

	$conn = openDatabase();

	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		http_response_code(500); // Internal Server Error
		echo json_encode(['success' => false, 'message' => 'Failed to prepare the SQL statement: ' . $sql]);
		$conn->close();
		exit();
	}
	$stmt->bind_param($types, ...$args);

	if (!$stmt->execute()) {
		http_response_code(500); // Internal Server Error
		echo json_encode(['success' => false, 'message' => 'Failed to execute the SQL statement: ' . $sql]);
		$conn->close();
		exit();
	}

	$id   = $id == 0 ? $stmt->insert_id : $id;
	$stmt->close();
	$conn->close();
	return $id;
}

function insertRecord(string $table, array $fields): int
{
	return changeRecord($table, $fields, 0);
}



function deleteRecord(string $table, int $id): int
{

	$sql = "DELETE FROM " . $table . " WHERE id=?";

	$conn = openDatabase();

	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		http_response_code(500); // Internal Server Error
		echo json_encode(['success' => false, 'message' => 'Failed to prepare the SQL statement: ' . $sql]);
		$conn->close();
		exit();
	}
	$stmt->bind_param("i", $id);

	if (!$stmt->execute()) {
		http_response_code(500); // Internal Server Error
		echo json_encode(['success' => false, 'message' => 'Failed to execute the SQL statement: ' . $sql]);
		$conn->close();
		exit();
	}
	$stmt->close();
	$conn->close();
	return 0;
}

function getParams($params)
{

	$result  = [];
	$errors  = "";

	// Iterate through each key-value pair
	foreach ($params as $name => $required) {
		if (isset($_REQUEST[$name])) {
			$result[$name] = (str_contains($_REQUEST[$name], "_id") ? intval($_REQUEST[$name]) : $_REQUEST[$name]);
		}
		else if ($required) {
			$errors .= (strlen($errors) > 0 ? ", " : "") . $name;
		}
	}
if (strlen($errors) > 0) {
	echo json_encode(['success' => false, 'message' => 'Missing required fields: ' . $errors]);
	exit();
	}

return $result;
}