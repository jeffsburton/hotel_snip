<?php
// Include the database connection script
require_once 'db.php';

$sql = "SELECT * FROM city";

echo selectAsJson($sql);
