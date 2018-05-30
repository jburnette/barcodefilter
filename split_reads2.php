<?php
function split_read ($read, $positions, $insert_q) {
	asort($positions);			//ensure array is sorted.
	$count = count($positions);
	//echo "Count: " . $count . "\n";
	switch($count) {
		case 1: 
			//ignore, don't have both primers
			break;
		case 2: 
			//get F or R for each primer
			$primers = array_keys($positions);
			$first = substr($primers[0], 0, 1);
			$second = substr($primers[1], 0, 1);
			//test to see if F and R not RR or FF
			if ($first != $second) {
			
				//check location of first primer. If <50 probably at the start and not middle.
				if ($positions[$primers[0]] < 100) {
					//store read full length in output table.
					//$sequence = get_clean_seq($read['sequence']);
					$read_num = get_read_num($read['name']);
					$insert_params = array(
						"plate_num" => substr($primers[0], 1, 1),
						"rp" => substr($primers[0], 2),
						"fp" => substr($primers[1], 2),
						"fasta_id" => $read_num,
						"seq" => $read['sequence']
					);
					try {
						$result = $insert_q->execute($insert_params);
						//print_r($insert_params);
					} catch  (PDOException $ex) {die($ex);}
					//echo "Stored single read! \n";
				}
			} 
		break;    //end singletons.
		default:
			if ($count%2 != 0) {break;} //not an even list, ignore
			$primers = array_keys($positions);
			$count_seq = 1;
			
			while ($primers) {
				//create "singleton" array 
				$primers1 =  array_shift($primers);
				$primers2 =  array_shift($primers);
				$first = substr($primers1, 0, 1);
				$second = substr($primers2, 0, 1);
				//echo "First = " .$first. " Second = " . $second ."\n";
				//test to see if F and R not RR or FF
				if ($first != $second) {
					//check location of first primer. If <50 probably at the start and not middle.
					//store read full length in output table.
					$sequence = substr($read['sequence'], $positions[$primers1], $positions[$primers2]);				
					//$sequence = get_clean_seq($read['sequence']);
					$read_num = get_read_num($read['name']);
					$insert_params = array(
						"plate_num" => substr($primers1, 1, 1),
						"rp" => substr($primers1, 2),
						"fp" => substr($primers2, 2),
						"fasta_id" => $read_num . $count_seq,
						"seq" => $sequence,
					);
					try {
						$result = $insert_q->execute($insert_params);
					} catch  (PDOException $ex) {die($ex);}
					//echo "Stored read! \n";	
					//echo($read['name']);
					//die();
					
				} 
				$count_seq++;
			} //end while.
			break;
	}//end switch
	//no return value.
}//end function 

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
		return (substr("$sequence", $f_pos++, $length));
		//echo (substr("$sequence", $f_pos++, $length));
	
	} else {
		$length = $f_pos - $r_pos +1;
		return (substr("$sequence", $r_pos++, $length));
		//echo (substr("$sequence", $r_pos++, $length));
	}
	


}

?>

