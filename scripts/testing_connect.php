<?php
require_once('../classes/class.db_connect.php');

try {
	for ($i = 0; $i <= 80000; $i++) {
		$DBConnObject = new DBConnect();
		echo 'Connection: '.$i.'<br>';
	}
} catch (Exception $e) {
	echo 'Error: ' . $e->getTraceAsString();
}
