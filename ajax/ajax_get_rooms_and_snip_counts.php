<?php

/**
 * An ajax handler that retrieves a list of rooms for a given hotel.
 *
 * Usage:
 * Should be accessed via post or get, with parameters passed on the url
 * or form encoded.
 *
 * @param hotel_id The id of the hotel
 *
 * @return Outputs a JSON response:
 * - if successful:
 * [success: True, data: [{
 *    image_id: id of first image in the room (image_room table),
 *    room_id: id from room table,
 *    image_count: number of images
 *    }]]
 * - if error:
 * [success: False, message: "Error message"]
 *
 * @return
 */


// Include the database connection script
require_once 'db.php';

$params = getParams(["hotel_id" => True]);

$sql = "
SELECT room.id AS room_id,image_room.id AS image_id,image_type.id AS image_type_id,COUNT(area.id) AS snip_count
 FROM room
    INNER JOIN image_room ON room.id=room_id
    INNER JOIN image_type
    LEFT OUTER JOIN area ON area.image_room_id=image_room.id AND area.image_type_id=image_type.id
 WHERE room.hotel_id=? AND room.room_type_id=2
 GROUP BY room.id, image_room.id, image_type.id
ORDER by room.id, image_room.id, image_type.id
";

try {
	$records = select($sql, $params['hotel_id']);
	$results = [];
	$curRoom = null;
	$curRoomId = 0;
	$curImageId = 0;
	for ($i = 0; $i < count($records); $i++) {
		$rsRoomId    = $records[$i]["room_id"];
		$rsImageTypeId    = $records[$i]["image_type_id"];
		$rsImageId    = $records[$i]["image_id"];
		if (!$i || $rsRoomId != $curRoomId) {
			if ($curRoom != null)
				array_push($results, $curRoom);
			$curRoom = ["room_id"=>$rsRoomId, "image_id"=>$rsImageId, "image_count" => 0,
				"image_types"=>[]];
			$curRoomId = $rsRoomId;
		}
		if ($curImageId != $rsImageId) {
			$curRoom["image_count"]++;
			$curImageId = $rsImageId;
		}
		if (isset($curRoom["image_types"][$rsImageTypeId]))
			$curRoom["image_types"][$rsImageTypeId] += $records[$i]["snip_count"];
		else
			$curRoom["image_types"][$rsImageTypeId] = $records[$i]["snip_count"];

	}
	if ($curRoom != null)
		array_push($results, $curRoom);


	echo json_encode(["success" => True, "data" => $results]);
}
catch (Exception $e) {
	echo json_encode(["success" => False, "message" => $e->getMessage(), "trace" => $e->getTraceAsString()]);
}
