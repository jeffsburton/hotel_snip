<?php


set_error_handler(function ($severity, $message, $file, $line) {
	// Throw an exception for warnings, notices, and other errors
	if (!(error_reporting() & $severity)) {
		// This error code is not included in current error_reporting
		return false;
	}
	throw new ErrorException($message, 0, $severity, $file, $line);
});

$sql  = "";

try {
	header('Content-Type: application/json');

	// Include the database connection script
	require_once 'db.php';

	// Check if the request is a POST request
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		// Open database connection
		$conn = openDatabase();

		// Ensure files were uploaded
		if (!isset($_FILES['files']) || empty($_FILES['files']['name'][0])) {
			echo json_encode(['success' => false, 'message' => 'No files were uploaded.']);
			exit();
		}

		// Get additional parameters from the request
		$hotel_id = isset($_POST['hotel_id']) ? intval($_POST['hotel_id']) : 0;
		$room_type_id = isset($_POST['room_type_id']) ? intval($_POST['room_type_id']) : 0;
		$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;

		if ($room_id == 0)
		{
			$stmt = $conn->prepare("INSERT INTO room (hotel_id, room_type_id) VALUES(?, ?)");
			$stmt->bind_param("ii", $hotel_id, $room_type_id);
			$stmt->execute();
			$room_id = $conn->insert_id;
		}

		// Prepare the SQL query for insertion
		$sql = "INSERT INTO image_room (file, room_id) VALUES (?, ?)";

		$stmt = $conn->prepare($sql);

		if (!$stmt) {
			http_response_code(500); // Internal Server Error
			echo json_encode(['success' => false, 'message' => 'Failed to prepare the SQL statement.']);
			$conn->close();
			exit();
		}

		// Process each uploaded file
		$responses = [];
		foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
			$fileTmpPath = $tmp_name;
			$fileType = $_FILES['files']['type'][$key];
			$fileName = $_FILES['files']['name'][$key];

			// Ensure the file is valid
			if (!is_uploaded_file($fileTmpPath)) {
				$responses[] = ['file' => $fileName, 'success' => false, 'message' => 'Invalid file upload.'];
				continue;
			}

			// Read the file's binary data
			$fileData = file_get_contents($fileTmpPath);
			if ($fileData === false) {
				$responses[] = ['file' => $fileName, 'success' => false, 'message' => 'Failed to read uploaded file.'];
				continue;
			}

			// Bind the parameters and execute the query
			$stmt->bind_param("bi", $fileData, $room_id);

			if ($stmt->send_long_data(0, $fileData) && $stmt->execute()) {
				$responses[] = ['file' => $fileName, 'success' => true, 'message' => 'File uploaded successfully.', 'newId' => $conn->insert_id];
			} else {
				$responses[] = ['file' => $fileName, 'success' => false, 'message' => 'Failed to upload file to database.'];
			}
		}

		// Close database resources
		$stmt->close();
		$conn->close();

		// Return a JSON response for all file uploads
		echo json_encode(['success'=> true,'roomTypeId'=>$room_type_id, 'data' => $responses]);
	} else {
		http_response_code(405); // Method Not Allowed
		echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
		exit();
	}
}
catch(Exception $e) {
	echo json_encode(['success' => false, 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'sql'=> $sql]);
}
?>