<?php

	include("../../config.php");
	try{
		$refl = new ReflectionClass('Config');
		$config = $refl->getConstants();

		$username = $config['DB_USERNAME'];
		$password = $config['DB_PASSWORD'];
		$servername = $config['DB_HOST'];
		$dbname = $config['DB_NAME'];
		$conn = new mysqli($servername, $username, $password, $dbname);

		$folder ='stored-procedure/'; // update path
		$files = scandir($folder);
		foreach($files as $file) {
			// check if file name contains .sql
			if(strpos($file, '.sql') !== false) {
				$fileName = $folder.$file;
				$sql = file_get_contents($fileName);
				if (mysqli_multi_query($conn, $sql) === TRUE) {
					echo "- The script ' ".$fileName." ' executed successfully \n";
				} else {
					echo "- The script ' ".$fileName." ' executed NOT SUCCESSFULL: ".$conn->error." \n";
				}
				mysqli_next_result($conn);
			}
		}
		$conn->close();
	}	catch (Exception $e) {
		echo $e->getMessage();
		exit();
	}
?>