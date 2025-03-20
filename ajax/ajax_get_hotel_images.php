<?php
// Include the database connection script
require_once 'db.php';

// Check if the request is an AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	// Open a database connection using the provided function


	$sql = "SELECT image_room.id AS image_id, image_room.room_id AS room_id, COUNT(*) AS snip_count
    FROM image_room
        INNER JOIN room ON room.id=room_id
        LEFT OUTER JOIN snip ON snip.image_room_id=room.id
    WHERE room.hotel_id=?
    GROUP BY image_room.id
    ORDER BY image_room.id
	";

	echo selectAsJson($sql, $_GET['hotel_id']);

} else {
	// Invalid request
	echo json_encode(['success' => false, 'message' => 'Invalid request.']);
	exit();
}