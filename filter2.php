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
function primer_rc($primer_array) {
	$primer_rc_array = array();
	foreach ($primer_array as $primer) {
		$primer_rev = strrev($primer[2]);
		$primer_rev_c = strtr($primer_rev, "AGCT", "TCGA");
		$primer_rc_array[$primer[2]] = $primer_rev_c;
	}
	return($primer_rc_array);
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

//Primer sequences

/**
	primer_array[plate][name][sequence][rc]

**/
$f_primers = primer_array("./forward_primers.txt");
$r_primers = primer_array("/reverse_primers.txt");
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
//print_r($plate);
//array_push ($plate_array["B"]["A1"]["H1"], "Hell0");
//print_r ($plate_array["B"]["A1"]["H1"]);
//die();

/** Searches for primers
	1. reverse primer
	2. forward primer
	3. push into array.
**/
$count = 0;
$no_count = 0;
$found = 0;
$r_found = 0;
$no_primer = array();
while ($read = $stmt->fetch()) {
	$readN = "N" . $read["sequence"] ."N";
	$read_name = '';
	$r_primer_name = '';
	$r_primer_plate = '';
	$f_primer_name = '';
	$f_primer_plate = '';
	//go through reverse primers
	foreach ($r_primers as $r_seq) {
		$result = strpos($readN, $r_seq[2]) || strpos($readN, $r_seq[3]);
		var_dump($result);
		//$result = strpos($read["sequence"], $r_seq[2]);
		if ($result !== false) {
			//echo "R ";
			$r_found++;
			//echo $r_seq[1] ."\t".$r_seq[1] ."\n";
			//go through forward primers
			foreach ($f_primers as $f_seq) {
				$f_result = strpos($readN, $f_seq[2]) || strpos($readN, $f_seq[3]);
				echo "|".$read['name'] . "|\n";
				echo $readN . "\n";
				if ($f_result !== false) {
					
					echo "F\n";
					die();
					$read_name = $read['name'];
					$found++;
					//echo  " r_seq0 = " . $r_seq[0] ." f_seq1 = " . $f_seq[1] . " r_seq1 = " . $r_seq[1] ."\n";
					array_push($plate_array[$r_seq[0]][$f_seq[1]][$r_seq[1]],  $read);
					//array_push($plate_array["B"]["A1"]["H1"],  $read);
					//die();
					continue 2;
				}
			}
				
		} else {
			//array_push ($no_primer, $read["comment"]);
			
		
		}
		$no_count ++;
	}
$count++;
}
echo "\n\n".$count ."\n\n". $found."\n\n". $r_found."\n\n" .$no_count ."\n\n";
file_put_contents("plate_array_output.bin", serialize($plate_array));
//$array = unserialize(file_get_contents('yourfile.bin'));
//print_r($r_primers_rc);

?>