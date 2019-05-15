<?php
/**
 * This file is used by AJAX to fetch data from the database.
 *
 * @author 	IT60070011, IT60070096, IT60070102
 * @version	0.9.0
 * @since  	0.9.0
 */

include_once("core.php");

try {
	$stmt = $conn->prepare("SELECT * FROM main");
	$stmt->execute();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$json = json_encode($result);
	echo $json;
}
catch(PDOException $e) {
	echo "Error: " . $e->getMessage();
}