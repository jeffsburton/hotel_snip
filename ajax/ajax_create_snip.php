<?php


include "db.php";


try{

	$params = getParams(["left"=> True, "top"=> True, "width"=> True, "height"=> True, "image_type_id"=> True, "image_room_id"=> True]);

	$newId = insertRecord("snip", $params);
	echo json_encode(["success" =>True, "data" => $newId]);
} catch(Exception $e){
	echo json_encode($e->getMessage() . " ajax_create_snip.php" . $e->getTraceAsString());
}
