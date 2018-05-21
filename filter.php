<?php
function primer_array($file_name) {
 	$file = fopen("./$file_name", "r") or die ("Cannot open primer file");
	$primer_array = array();
	while ($row = fgets($file)) {
		$tmp_array = explode("\t", $row);
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

//Primer sequences

/**
	primer_array[plate][name][sequence]

**/
$f_primers = primer_array("./forward_primers.txt");
$r_primers = primer_array("/reverse_primers.txt");

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
$found = 0;
$r_found = 0;
while ($read = $stmt->fetch()) {
	$read_name = '';
	$r_primer_name = '';
	$r_primer_plate = '';
	$f_primer_name = '';
	$f_primer_plate = '';
	//go through reverse primers
	foreach ($r_primers as $r_seq) {
		$result = strpos($read["sequence"], $r_seq[2]);
		if ($result !== false) {
			$r_found++;
			//go through forward primers
			foreach ($f_primers as $f_seq) {
				$f_result = strpos($read["sequence"], $f_seq[2]);
				if ($result !== false) {
					$read_name = $read['name'];
					$found++;
					//echo "r_seq0 = " . $r_seq[0] ."   f_seq1 = " . $f_seq[1] . "   r_seq1 = " . $r_seq[1];
					array_push($plate_array[$r_seq[0]][$f_seq[1]][$r_seq[1]],  $read);
				}
			}		
		}
	}
$count++;
}
//echo "\n\n".$count ."\n\n". $found."\n\n". $r_found."\n\n";

print_r($r_primers);


?>