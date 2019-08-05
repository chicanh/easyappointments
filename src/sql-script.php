<?php
	try{
		$username ='ea';
		$password ='L3Tr4u4N';
		$servername = 'localhost';
		$dbname = 'easyappt';
		$conn = new mysqli($servername, $username, $password, $dbname);

		$folder ='assets/sql/stored-procedure/';
		$files = scandir($folder);
		foreach($files as $file) {
			if(strpos($file, '.sql') !== false){
				$fileName = $folder.$file;
				$sql = file_get_contents($fileName);
				if (mysqli_multi_query($conn, $sql) === TRUE) {
					echo "- The script ' <b>".$fileName."</b> ' executed <b>successfully</b> <br/>";
				} else {
					echo "- The script ' <b>".$fileName."</b> ' executed <b>NOT SUCCESSFULL</b>: ".$conn->error." <br/>";
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