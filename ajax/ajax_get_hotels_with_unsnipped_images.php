<?php

include_once "db.php";

$params = getParams(["image_type_id" => True]);

$sql = "
SELECT hotel.id, hotel.name FROM hotel
    INNER JOIN room ON hotel.id = room.hotel_id
    INNER JOIN image_room ON room.id = image_room.room_id
    WHERE (SELECT COUNT(*) FROM snip WHERE snip.image_room_id=image_room.id AND snip.image_type_id=?) = 0
GROUP BY hotel.id, hotel.name
ORDER BY hotel.name
";

echo selectAsJson($sql, $params['image_type_id']);