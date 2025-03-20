<?php


include "db.php";


try{

	$changeParams = getParams(["left"=> True, "top"=> True, "width"=> True, "height"=> True, "image_type_id"=> True, "image_room_id"=> True]);
	$idParam = getParams(["id"=> True]);
	changeRecord("snip", $changeParams, $idParam["id"]);
	echo json_encode(["success" =>True, "data" => []]);
} catch(Exception $e){
	echo json_encode($e->getMessage() . " ajax_update_snip.php" . $e->getTraceAsString());
}
