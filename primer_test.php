<?php
function primer_array($file_name) {
 	$file = fopen("./$file_name", "r") or die ("Cannot open primer file");
	$primer_array = array();
	while ($row = fgets($file)) {
		//string to array 
		$tmp_array = explode("\t", $row);
		//reverse complement primer 
		$primer_rev = strrev($tmp_array[2]);
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
	primer_array[plate][name][sequence]

**/
$f_primers = primer_array("./forward_primers.txt");
$r_primers = primer_array("/reverse_primers.txt");
//$f_primers_rc = primer_rc($f_primers);
//$r_primers_rc = primer_rc($r_primers);
print_r($f_primers);
