<?php
// Include the database connection script
require_once 'db.php';

$params = getParams(["city_id"=> True, "search" => False]);

if (isset($_GET['search']))
	echo selectAsJson("SELECT * FROM hotel WHERE city_id=? AND LOCATE(?, name) > 0 ORDER BY name", $params['city_id'], $params['search']);
else
	echo selectAsJson("SELECT * FROM hotel WHERE city_id=? ORDER BY name", $params['city_id']);