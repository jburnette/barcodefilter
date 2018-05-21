<?php
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
$db = new PDO('sqlite:fasta.sqlite');
$select = "SELECT * FROM seqs";
try {
	$stmt = $db->prepare($select);
	$result = $stmt->execute();
} catch (PDOException $ex) {die($ex);}
//set up insert statment for sequences with 2 primers
$insert = "INSERT INTO output (plate_num, rp, fp, fasta_id) VALUES (:plate_num, :rp, :fp, :fasta_id)";
try {
	$insert_stm = $db->prepare($insert);
} catch (PDOException $ex) {die($ex);}
//set up insert statement for sequences with one or no primers
$insert2 = "INSERT INTO not_found (fasta_id, primer_missing) VALUES (:fasta_id, :primer)";
try {
	$insert_no_stm = $db->prepare($insert2);
} catch (PDOException $ex) {die($ex);}

/**		Primer sequences
	primer_array[plate][name][sequence][rc]

**/
$f_primers = primer_array("./forward_primers_short.txt");
$r_primers = primer_array("/reverse_primers_short.txt");
//print_r($f_primers);
//die();

/**loop over reads 
	0. Build plate arrays 
	1. get a read 
	2. Do all reverse (plate column)
	3. Do all forward (plate row)
	4. 

**/

/**	0. Plate array
**/

$num_plates = 2;
global $plate_array;
$plate_array = array();

/**
	plate_array[plate letter]->array[F][R]

**/
//make base plate array array[R][F]
$r_alpha = "A";
$r_num = 1; 
$f_alpha = "A";
$f_num = 1;
$plate = array();

//one column of plate
for ($j="A"; $j<="H"; $j++) {		
	for ($y=1; $y<=12; $y++) { 
		$plate[$f_alpha.$y][$j.$r_num] = array();
	}
}


//number of plates
$letter = 'A';
for($i=0;$i<$num_plates;$i++) {
	$plate_array[$letter] = $plate;
	$letter ++;
}


/** Searches for primers
	1. reverse primer
	2. forward primer
	3. push into array.
**/
$count = 0;
$no_count = 0;
$r_no_count = 0;
$found = 0;
$r_found = 0;
$no_primer = array();
while ($read = $stmt->fetch()) {
	$readN = "N" . $read["sequence"] ."N";  //Pad the sequence with N so that the first nt is pos 1 not 0
	//go through reverse primers
	$r_loop_count = 0;
	foreach ($r_primers as $r_seq) {
		$result = strpos($readN, $r_seq[2]) || strpos($readN, $r_seq[3]);
		$r_loop_count++;
		if ($result !== false) {
			$r_found++;
			//go through forward primers
			$f_loop_count =0;
			foreach ($f_primers as $f_seq) {
				$f_result = strpos($readN, $f_seq[2]) || strpos($readN, $f_seq[3]);
				$f_loop_count++;
				if ($f_result !== false) {
					//both primers found.
					$found++;
					//echo  " r_seq0 = " . $r_seq[0] ." f_seq1 = " . $f_seq[1] . " r_seq1 = " . $r_seq[1] ."\n";
					//array_push($plate_array[$r_seq[0]][$f_seq[1]][$r_seq[1]],  $read);
					
					/**		Insert sequence in table	**/
					$insert_params = array(
						"plate_num" => $r_seq[0],
						"rp" => $r_seq[1],
						"fp" => $f_seq[1],
						"fasta_id" => $read[0]
					);
					try {
						$result = $insert_stm->execute($insert_params);
					} catch  (PDOException $ex) {die($ex);}
					echo $count . "\n";
					$r_loop_count =0;
					continue 2;
				} elseif ($f_loop_count >= 24) {
					//has RP but no FP
					$no_count ++;
					$r_loop_count =0;
					//continue 2;
					
					/**		Insert into not_found table	**/
					$insert_params = array(
						":fasta_id" => $read[0],
						":primer" => "F"  
					);
					try {
						$result = $insert_no_stm->execute($insert_params);
					} catch  (PDOException $ex) {die($ex);}
				}
			}
				
		}
		if ($r_loop_count == 16) {
			//array_push ($no_primer, $read["comment"]);
			$r_no_count++;
			//echo " r_no_count=  ".$r_no_count ."\n";
			$r_loop_count =0;
			/**		Insert into not_found table	**/
			$insert_params = array(
						":fasta_id" => $read[0],
						":primer" => "R"  
					);
					try {
						$result = $insert_no_stm->execute($insert_params);
					} catch  (PDOException $ex) {die($ex);}
		}
		
	}
$count++;
}
echo "\n\n".$count ."\n\n". $found."\n\n". $r_found."\n\n" .$no_count ."\n\n" . $r_no_count . "\n\n"; 
echo "********Finished\n\n********";

?>