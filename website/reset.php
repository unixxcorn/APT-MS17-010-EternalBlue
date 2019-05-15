<?php
/**
 * WARNING: By executing this file means you're about to erase all DB data.
 * This file is executed by "Reset" button.
 *
 * @author 	IT60070011, IT60070096, IT60070102
 * @version	0.9.0
 * @since  	0.9.0
 */

include_once("core/core.php");

try {
	$stmt = $conn->prepare("DELETE FROM main");
	$stmt->execute();
	header("Location: ".ROOTURL."/start.php");
} catch(PDOException $e) {
	echo "Error: " . $e->getMessage();
}