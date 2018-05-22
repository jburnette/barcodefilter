<?php

$seq = "GAGAGCGCTGACTCTGCAGGAAACAGCTATGACATGATGCACAAGTTGCAATACCTACAATCAAATCAGTAAAATCAGCAAACACCAAATATATATCACCTATTTAAATGTTTCAAATTTCATGCGTAGATCTGTTACTACTCTGTTAACTTATCAAGACTTATCGGATGTATACTACATTATTCTGTTTCTTTTATTGACATCATATCTTAGGTCATACTGAAAATTGATGATGACGAAAATTGACGATGACTGATAATTTAAGTTGTGAGCAATGATCTGGTACTGACAATCTAGATGTAACAGTAACTTACCACACATGTTCTTCCCCATCTCCATCTTAAAGTAGCCATGATCGCCCCAGTTCTCTCCCCAAGAGTTCTTAATCAGCCAATATGGAACACCATCTTCAACTCCGTACCCAACAGCAACGACGGCATGGTTCACATCCTGCCATAAAATCCCGAAAGTTAGAAATGCAATAAATGCATCGGATGAGTGGTTAATGCTTGGGTGATAATTCCAAGCGAAGGATCATTTACACAACGTCTACATTAAGCATTCCTTGCGTGAGTAACATGTTGACAAACTGAGAAGTTCAGAAAAGAAAATGAGAAATGTTGTTTGGAGAGTTTGGACCATAATGTTAGGCATTTATTTGTTTTGCAAAACTTCAAAACTTGAATGCCCTGTTAAATTTTGATTTTGAACATAAATCAAAACATTTTTTAACAGATTTGCATTCAATGCCTATCAAAATAATGTATCGTTTTCCAAGTCAATAAGACTCACCATGGGAGTATTTCCACATTTAGTGCTGCTGTAAACTCCACTCTTGTAAAATCGGAATCCATCTACTGCTTCAAATGCCACACTCACAGGTCGAACAAGACCAACTGCATGCTGTAATTCATCTTCAGCACCCTGCATTGTCTTACAAGTCATGAAACTTGAAACATATACATATCCAGAGTTTCTAGATTATGATTGATTTATTTATGGTTGATTTTCATTTGTTTGGCATGTAGTGAGTCCAGCACTGCAGACTCTACTCTCATTTAAAATGAAAATTCAGCATAAAACTCACCAAGGTAATGTTGACTGAGTCCAGGCTGGCCGTCGTTTTACATGACGCATCGTCTG";
		
get_clean_seq ($seq);


function get_clean_seq ($sequence) {
	$m13F = 'GTAAAACGACGGCCAG';
	$m13F_RC = 'CTGGCCGTCGTTTTAC';
	$m13R = 'AGGAAACAGCTATGAC';
	$m13R_RC = 'GTCATAGCTGTTTTCCT';
	$f_pos;
	$r_pos;
	if ($f_pos = strpos($sequence, $m13F)) {
		;
	} else {
		$f_pos = strpos($sequence, $m13F_RC);
	}
	
	if ($r_pos = strpos($sequence, $m13R)) {
		;
	} else {
		$r_pos = strpos($sequence, $m13R_RC);
	}
	if ($f_pos < $r_pos) {
		$length = $r_pos - $f_pos +1;
		//return (substr("$sequence", $f_pos++, $length));
		echo (substr("$sequence", $f_pos++, $length));
	
	} else {
		$length = $f_pos - $r_pos +1;
		//return (substr("$sequence", $r_pos++, $length));
		echo (substr("$sequence", $r_pos++, $length));
	}
	


	 

}



?>