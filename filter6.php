<?php
require ("./split_reads.php");
ini_set('memory_limit','300M');
function primer_array($file_name) {
 	$file = fopen("./$file_name", "r") or die ("Cannot open primer file");
	$primer_array = array();
	while ($row = fgets($file)) {
		$row = trim($row);
		//string to array 
		$tmp_array = explode("\t", $row);
		//reverse complement primer 
		$primer_rev = strrev(trim($tmp_array[2]));
		$primer_rev_c = strtr($primer_rev, "AGCT", "TCGA");
		$tmp_array[3] = $primer_rev_c;
		array_push($primer_array, $tmp_array);
	}
	fclose($file);
	return $primer_array;
}


//Open fasta DB of reads
/**
	DB array [id][comment][sequence]
**/
$db = new PDO('sqlite:run5-4-2.sqlite');
$select = "SELECT * FROM seqs";
try {
	$stmt = $db->prepare($select);
	$result = $stmt->execute();
} catch (PDOException $ex) {die($ex);}
//set up insert statment for sequences with 2 primers
$insert = "INSERT INTO output (plate_num, rp, fp, fasta_id, seq) VALUES (:plate_num, :rp, :fp, :fasta_id, :seq)";
try {
	$insert_stm = $db->prepare($insert);
} catch (PDOException $ex) {die($ex);}
//Primer sequences

/**
	primer_array[plate][name][sequence][rc]

**/
$f_primers = primer_array("./forward_primers_shorter.txt");
$r_primers = primer_array("/reverse_primers_shorter.txt");


/** New strategy

	1. Search all RPs store posiitons 
	2. Search all FPs store positions 
	
	found[primer name] = location
	
	3. sort on values using asort 
	4. take pairs -- should  be RP and FP.
	5. take substring for each pair 
	6. modify name for new sequence
	7. store in database  

**/
$found = 0;
$r_found = 0;
$f_found = 0;
$count=0;
while ($seq = $stmt->fetch()) {
	//$seq[name]
	//$seq[sequence]
	$primer_pos = array();		//$primer_array[primer name] = pos 
	//search all RPs
	$num_rps_found = 0;		//store number of primers found
	$num_fps_found = 0;
	$r_exists = false;
	$f_exists = false;
	$r_plate = '';
	foreach ($r_primers as $rp) {
		for ($i=2; $i<4; $i++) {  //do both primers rc
			$position = strpos($seq["sequence"], $rp[$i]);
			if ($position !== false) {
				$primer_name = "R".$rp[0].$rp[1];
				$primer_pos[$primer_name] = $position;
				$num_rps_found++;
				$r_found++;
				$r_exists = true;
				$r_plate = $rp[0];
			} // end if
		} //end for loop
	}
	//search all FPs
	foreach ($f_primers as $fp) {
		//if ($fp[0] != $r_plate) {continue;}
		for ($i=2; $i<4; $i++) {  //do both primers rc
			$position = strpos($seq["sequence"], $fp[$i]);
			if ($position !== false) {
				$primer_name = "F".$fp[0].$fp[1];
				$primer_pos[$primer_name] = $position;
				$num_fps_found++;
				$f_found++;
				$f_exists = true;
			} // end if
		} //end for loop
	}
	asort($primer_pos);
	//print_r($primer_pos);
	if ($r_exists & $f_exists) {$found++;}
	//echo "Name " . $seq['name'] ."\n";
	split_read($seq, $primer_pos, $insert_stm);
	$count++;
	echo "sequence count = " . $count . "\n";
	//echo "Loop count = " . $count . " FP=" . $num_fps_found .  "RP=" . $num_rps_found . " Found= ". $found . "\n";
	
} //end sequence while loop

echo "sequence count = " . $count . "\nRP = " . $r_found . "\nFP = " . $f_found . " " . "\nTotal pairs = " . $found . "\n";

echo "********Finished********\n\n";
