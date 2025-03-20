<?php


include "db.php";


try{

	$params = getParams(["coordinates"=> True, "image_type_id"=> True, "image_room_id"=> True]);

	$newId = insertRecord("area", $params);
	echo json_encode(["success" =>True, "data" => $newId]);
} catch(Exception $e){
	echo json_encode($e->getMessage() . " " . $_SERVER["PHP_SELF"] . $e->getTraceAsString());
}
