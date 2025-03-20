<?php


include "db.php";


try{

	$changeParams = getParams(["room_id"=> True]);
	$idParam = getParams(["id"=> True]);

	// get the old room id
	$records = select("SELECT room_id FROM image_room WHERE id = ?", $idParam["id"]);
	$oldRoomId = $records[0]["room_id"];


	changeRecord("image_room", $changeParams, $idParam["id"]);

	// see if the room only has one image left.
	$records = select("SELECT id FROM image_room WHERE room_id = ?", $oldRoomId);
	if (count($records) == 0)
		deleteRecord("room", $oldRoomId  );

	echo json_encode(["success" =>True, "data" => []]);
} catch(Exception $e){
	echo json_encode($e->getMessage() . " " . $_SERVER["PHP_SELF"] . $e->getTraceAsString());
}
