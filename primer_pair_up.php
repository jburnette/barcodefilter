<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require("functions.php");
$f_primers = primer_array("./forward_primers_long.txt");
$r_primers = primer_array("./reverse_primers_long.txt");


//pop off reverse primer
while ($rev_primer = array_shift($r_primers)) {
	echo $rev_primer[0] . "\n";

	$col_num ='A';
	$plate_num_array = array();
	while ($col_num < "I") { 
		$row_letter = '1';
		
		while ($row_letter < "13") {
		



		$row_letter ++;
		}
	$col_num ++;

	} #column num while
}	//end plate while.


?>