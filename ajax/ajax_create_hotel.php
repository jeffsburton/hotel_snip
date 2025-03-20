<?php


include "db.php";


try {

	$params = getParams(["name" => True, "address" => True, "url" => True, "city_id" => True]);

	$newId = insertRecord("hotel", $params);
	echo json_encode(["success" =>True, "data" => $newId]);
} catch (Exception $e) {
	echo json_encode($e->getMessage() . " ajax_create_hotel.php" . $e->getTraceAsString());
}
