<?php
/****  Use BLAST to find primers.    ********/

require ("./split_reads7.php");
require ("functions.php");
ini_set('memory_limit','300M');

//$primers = 'all_pacbio_barcodes.fasta';
//Open fasta DB of reads
/**
	DB array [id][comment][sequence]
**/
$db = new PDO('sqlite:../Databases/salsa_2018_2.db');
$select = "SELECT * FROM seqs";
//$select = "SELECT * FROM seqs WHERE id IN (94347, 96349, 96351, 96394)";
//$select = "SELECT * FROM seqs WHERE id =96351";
try {
	$stmt = $db->prepare($select);
	$result = $stmt->execute();
} catch (PDOException $ex) {die($ex);}
//set up insert statment for sequences with 2 primers
$insert = "INSERT INTO output (rp, fp, fasta_id, seq) VALUES (:rp, :fp, :fasta_id, :seq)";
try {
	$insert_stm = $db->prepare($insert);
} catch (PDOException $ex) {die($ex);}
//set up select statment for primer table
$primer_select = "SELECT * FROM PacBioBarcodedPrimers WHERE Name = :name";
try {
	$primer_select_stm = $db->prepare($primer_select);
} catch (PDOException $ex) {die($ex);}

/** New strategy

	1. Get read
	2. Get read num from comment
	3. run blast_read	
	4. 

**/
$GLOBALS['singleton'] = 0;
$GLOBALS['multiple'] = 0;
$GLOBALS['odds'] = 0;
$GLOBALS['found'] = 0;

$count=0;
while ($seq = $stmt->fetch()) {
	$positions = array();
	$blast_result = blast_read($seq['sequence'], $primers);
	//print_r($blast_result);	
	foreach ($blast_result as $result) {
		list($key, $pos1, $pos2, $length, $send, $sstart) = explode(",", $result);
		#echo ($key. $pos1. $pos2);
		$positions[$pos1] = array ($pos2, $key, $length, $send, $sstart);
		asort($positions);
		//print_r($positions);
		//print_r(array_keys($positions));	
	}
	//print_r($positions);
		split_read($seq, $positions, $insert_stm, $primer_select_stm);
		$count++;
		echo ($count . "\n");
} //end sequence while loop
$db = null; 		//close database connection.
echo "Total sequence count = " . $count . "\n". 
"Singles = " . $GLOBALS['singleton'] ."\n". 
"Multpiles = " . $GLOBALS['multiple'] ."\n".
"Odds = " . $GLOBALS['odds'] ."\n". 
"Total stored = " . $GLOBALS['found'] ."\n";

echo "********Finished********\n\n";
