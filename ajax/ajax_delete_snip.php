<?php


include "db.php";


try {

	$params = getParams(["id" => True]);

	deleteRecord("snip", $params["id"]);
	echo json_encode(["success" =>True]);
} catch (Exception $e) {
	echo json_encode($e->getMessage() . " " . $_SERVER["PHP_SELF"] . $e->getTraceAsString());
}
