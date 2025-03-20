<?php

/**
 * An ajax handler that retrieves details about a hotel.
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
 *    id: id of the hotel
 *    name: hotel name,
 *    address: hotel address,
 *    url: hotel url
 *    }]]
 * - if error:
 * [success: False, message: "Error message"]
 *
 * @return
 */

include_once "db.php";

$params = getParams(["hotel_id" => True]);

$sql = "SELECT hotel.id, hotel.name, hotel.address, hotel.url FROM hotel WHERE hotel.id=?";

echo selectAsJson($sql, $params['hotel_id']);