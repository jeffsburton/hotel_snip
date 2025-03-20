<?php
// Include the database connection script
require_once 'db.php';

$params = getParams(["image_id"=> True]);

$sql = "SELECT * FROM snip WHERE image_room_id=?;";

echo selectAsJson($sql, $params["image_id"]);
