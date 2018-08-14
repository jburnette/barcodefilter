<?php
ini_set('memory_limit','300M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$collapser_path = "./bin/fastx_collapser";
$database = "salsa_2018_2.db";
require ("functions.php");
$db = new PDO('sqlite:../databases/'.$database);
//Get plate names
$select_plates = "SELECT DISTINCT(plate_name) FROM primers WHERE 1 ORDER BY plate_name";
try {
	$select_plates_stmt = $db->prepare($select_plates);
	$result = $select_plates_stmt->execute();
}   catch (PODExecption $ex) {
	die ($ex);
}
$primer_stmt = "SELECT primer_name FROM primers WHERE plate_name = :plate AND direction = :dir";
try {
	$primer_stmt_h = $db->prepare($primer_stmt);
} catch (PDOExecption $ex) {
	die ($ex);
}
$select = "SELECT output.fasta_id, output.seq FROM output WHERE output.rp = :rp AND output.fp = :fp ";
try {
	$select_h = $db->prepare($select);
} catch (PDOExecption $ex) {
	die ($ex);
}
$insert_stmt = "INSERT INTO collapsed (rp, fp, fasta_id, sequence) VALUES (:rp, :fp, :fasta_id, :seq)";
try {
	$insert_h = $db->prepare($insert_stmt);
} catch (PDOExecption $ex) {
	die ($ex);
}
$grand_count = 0;

while ($plate_fetch = $select_plates_stmt->fetch()) {
	$plate = $plate_fetch['plate_name'];
	echo "plate = " . $plate ."\n";
	$params = array(":plate"=>$plate, ":dir"=>"F");
	try {
		$result = $primer_stmt_h->execute($params);	
	} catch (PDOExecption $ex) {
		die ($ex);
	}
	$for_p = $primer_stmt_h->fetchAll();
	
	
		while ($fp = array_shift($for_p)) {
			//echo "fp = " . $fp['primer_name'] . "\n";
			$params = array(":plate"=>$plate, ":dir"=>"R");
			try {
				$result = $primer_stmt_h->execute($params);	
			} catch (PDOExecption $ex) {
					die ($ex);
			}
			$rev_p = $primer_stmt_h->fetchAll();
			while ($rp = array_shift($rev_p)) {
				//echo "rp = " . $rp['primer_name'] . "\n";
				//get sequences
				$params = array(
					":rp" => $rp['primer_name'],
					":fp" => $fp['primer_name']
				);
				//print_r($params);
				try {
					$result = $select_h->execute($params);
				} catch (PODExecption $ex) {
					die ($ex);
				}
				$fasta ='';
				$seqs = $select_h->fetchAll();
				
				$num_results = count($seqs);
				//echo ($num_results . "\n");
				if ($num_results== 0) {break;}
				
				while($rows = array_shift($seqs)) {
				//print_r($rows);
				
					$fasta .= ">". $rows['fasta_id'] . "\n" .$rows['seq'] . "\n";
				}
				//echo $fasta;
				file_put_contents("/var/tmp/temp_out.fasta", $fasta);
				$command = $collapser_path .  ' -i /var/tmp/temp_out.fasta';
				exec($command, $result);
				//print_r($result);
				//store in database table
				$chunked_array = array_chunk($result, 2);
				foreach ($chunked_array as $collapsed) {
					$params = array(
						":rp" => $rp['primer_name'],
						":fp" => $fp['primer_name'],
						":fasta_id" => $collapsed[0],
						":seq" => $collapsed[1],
					);
					$grand_count++;
					echo "+"; 
					try {
						$result = $insert_h->execute($params);
					} catch (PDOExecption $ex) {
						die ($ex);
					}
				} //ends foreach
			} // closes rp while
			
		}  // closes fp while
}  //ends while loop
echo "\n grand count= ". $grand_count . "\n";
echo "++++++++++++FINISHED++++++++++++\n";

?>