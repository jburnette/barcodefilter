<?php
/** load fasta file into sqlite3 table.
**/

$db = new PDO('sqlite:../Databases/salsa_2018_1.db');
$db->exec("CREATE TABLE  seqs (id INTEGER PRIMARY KEY, name TEXT, sequence TEXT)");

$file_name = "../spring_2018/salsa_2018_1_smrt1.fasta";
$file = fopen($file_name, "r");
$stmt='';
$insert_stm = "INSERT INTO seqs (name, sequence) VALUES (:comment, :seq)";
try {
	$stmt = $db->prepare($insert_stm);
} catch (PDOException $ex){
	die("Failed to run query: " . $ex->getMessage()); 
}
	
while ($line = fgets($file)) {	

	
	//echo $line;
		if (preg_match("/>/", $line)) {
				try {
			$result = $stmt->execute($insert_array);
		} catch (PDOException $ex) {
			die("something went wrong");
		
		}
			$comment ='';
	$seq = '';
	$insert_array = array();
			$comment = trim($line);
			echo $line;
			$insert_array[":comment"] = $comment;
		} else if (preg_match("/[A-Z,a-z]/", $line)) {
			$line = strtoupper(trim($line));
			$insert_array[":seq"] .= $line;
			
		} else{;}

	
}


	







?>