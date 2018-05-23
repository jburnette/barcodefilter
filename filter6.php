<?php
/****  Use BLAST to find primers.    ********/

require ("./split_reads.php");
require ("functions.php");
ini_set('memory_limit','300M');

$primers = 'plate_primers.fasta';
//Open fasta DB of reads
/**
	DB array [id][comment][sequence]
**/
$db = new PDO('sqlite:../Databases/run5-4-2.sqlite');
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



/** New strategy

	1. Get read
	2. Get read num from comment
	3. run blast_read	array[0][0]->first hit, etc.
	4. 

**/
$found = 0;
$r_found = 0;
$f_found = 0;
$count=0;
while ($seq = $stmt->fetch()) {

	$blast_result = blast_read($seq['sequence'], $primers);
	print_r($blast_result);	
	
	
	

	
} //end sequence while loop

echo "sequence count = " . $count . "\nRP = " . $r_found . "\nFP = " . $f_found . " " . "\nTotal pairs = " . $found . "\n";

echo "********Finished********\n\n";
