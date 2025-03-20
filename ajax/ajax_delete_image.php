<?php


include "db.php";


try {

	$params = getParams(["id" => True]);

	$record = select("SELECT * FROM image_room WHERE id = ?", $params["id"]);
	$roomId = $record[0]["room_id"];

	deleteRecord("image_room", $params["id"]);

	// now see if the room is empty.
	$record = select("SELECT * FROM image_room WHERE room_id = ?", $roomId);
	$deletedRoom = False;
	if (count($record) == 0)
	{
		$deletedRoom = True;
		deleteRecord("room", $roomId);
	}

	echo json_encode(["success" =>True, "data" => ["deletedRoom" => $deletedRoom]]);
} catch (Exception $e) {
	echo json_encode($e->getMessage() . " " . $_SERVER["PHP_SELF"] . $e->getTraceAsString());
}
