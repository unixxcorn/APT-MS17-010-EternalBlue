<?php
/**
 * This file contains core functions and needs to be included.
 *
 * @author 	IT60070011, IT60070096, IT60070102
 * @version	0.8.0
 * @since  	0.8.0
 */

define("ROOTURL", "https://edu.bstudio.click/pentesting");

// Connect to the database
$host = "localhost";
$username = "admin_pentesting";
$password = "Network#61";
$dbname = "admin_pentesting";
try {
	$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
	$text = $e->getMessage();
	$var_str = var_export($text, true);
	file_put_contents('error.log', $var_str);
}

// Get now timestamp - Return string
function now(){
	$date = new DateTime();
	$datetime = $date->format('Y-m-d H:i:s');
	return $datetime;
}