<?php
/**
 * This file waits for JSONs from the another application by POST method.
 * Then save the decoded data into the database.
 * This file doesn't contain any GUI.
 *
 * @author 	IT60070011, IT60070096, IT60070102
 * @version	0.9.1
 * @since  	0.8.0
 */

include_once("./core/core.php");

// Get JSON from the another application
$entityBody = file_get_contents('php://input');
$decoded = json_decode($entityBody);

// Assign values
try {
	$stmt = $conn->prepare("INSERT INTO main (ip, port, os, arch, state, parent, updated_time) 
	VALUES (:ip, :port, :os, :arch, :state, :parent, now())");

	$ip = $decoded->ip;
	$port = $decoded->port;
	$state = $decoded->state;
	$os = $decoded->os;
	$arch = $decoded->arch;
	$parent = $decoded->parent;

	$stmt->bindParam(':ip', $ip);
	$stmt->bindParam(':port', $port);
	$stmt->bindParam(':state', $state);
	$stmt->bindParam(':os', $os);
	$stmt->bindParam(':arch', $arch);
	$stmt->bindParam(':parent', $parent);
	$stmt->execute();

} catch(PDOException $e) {
	$text = $e->getMessage();
	$var_str = var_export($text, true);
	file_put_contents('error.log', now()." $var_str \n", FILE_APPEND);
}

$conn = null;
