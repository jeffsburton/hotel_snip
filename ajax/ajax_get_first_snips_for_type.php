<?php
// Include the database connection script
require_once 'db.php';

// Check if the request is an AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	// Open a database connection using the provided function

	echo selectAsJson("SELECT id FROM image WHERE `first`=1 AND image_type_id = ? AND hotel_id=?", $_GET['image_type_id'], $_GET['hotel_id']);

} else {
	// Invalid request
	echo json_encode(['success' => false, 'message' => 'Invalid request.']);
	exit();
}