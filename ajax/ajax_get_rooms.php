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

$params = getParams(["hotel_id" => True, "room_type_id" => True]);

$sql = "
SELECT room.id AS room_id, MIN(image_room.id) AS image_id
 FROM room
    INNER JOIN image_room ON room.id=room_id
 WHERE room.hotel_id=? AND room.room_type_id=?
 GROUP BY room.id
ORDER by room.id
";

echo selectAsJson($sql, $params['hotel_id'], $params['room_type_id']);