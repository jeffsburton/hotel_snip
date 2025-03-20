<?php
// Include the database connection script
require_once 'db.php';

$sql = "SELECT * FROM image_type";

echo selectAsJson($sql);