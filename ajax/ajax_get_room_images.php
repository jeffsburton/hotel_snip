
<?php
include_once "db.php";

$params = getParams(["room_id" => True]);

$sql = "SELECT image_room.id as image_id FROM image_room WHERE room_id=?";

echo selectAsJson($sql, $params['room_id']);