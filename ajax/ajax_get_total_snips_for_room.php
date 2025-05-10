<?php

include_once "db.php";

$params = getParams(["room_id" => True]);

$sql = "SELECT image_room.id AS image_id, image_type.id AS image_type_id, COUNT(DISTINCT area.id) AS snip_count
    FROM image_room
    INNER JOIN image_type
    LEFT OUTER JOIN area ON area.image_room_id=image_room.id AND image_type.id = area.image_type_id
    WHERE image_room.room_id=?
    GROUP BY image_room.id, image_type.id";



try {

	$records = select($sql, $params['room_id']);
	$results = [];
	$curImage = null;
	$curImageId = 0;
	for ($i = 0; $i < count($records); $i++) {
		$rsImageTypeId    = $records[$i]["image_type_id"];
		$rsImageId    = $records[$i]["image_id"];
		if (!$i || $rsImageId != $curImageId) {
			if ($curImage != null)
				array_push($results, $curImage);
			$curImage = ["image_id"=>$rsImageId,
				"image_types"=>[]];
			$curImageId = $rsImageId;
		}
		if (isset($curImage["image_types"][$rsImageTypeId]))
			$curImage["image_types"][$rsImageTypeId] += $records[$i]["snip_count"];
		else
			$curImage["image_types"][$rsImageTypeId] = $records[$i]["snip_count"];
	}
	if ($curImage != null)
		array_push($results, $curImage);


	echo json_encode(["success" => True, "data" => $results]);

}
catch (Exception $e) {
	echo json_encode(["success" => False, "message" => $e->getMessage(), "trace" => $e->getTraceAsString()]);
}

