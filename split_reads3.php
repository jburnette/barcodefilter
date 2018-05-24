<?php
function split_read ($read, $positions, $insert_q) {
	//asort($positions);			//ensure array is sorted.
	$count = count($positions);
	switch($count) {
		case 1: 
			//ignore, don't have both primers
			$GLOBALS['odds']++;
			break;
		case 2: 
			$primer_pair_info = get_primer_info($positions);
	
			//test to see if F and R not RR or FF
			if ($primer_pair_info["first_dir"] != $primer_pair_info["sec_dir"]) {
				//check location of first primer. If <50 probably at the start and not middle.
				if ($primer_pair_info["first_loc"] < 100) {
					
					$length = $primer_pair_info["sec_loc"] - $primer_pair_info["first_loc"] +1;
					$read_num = get_read_num ($read['name']);
					$seq = substr($read['sequence'], $primer_pair_info["first_loc"], $length);
					//store read full length in output table.
					$insert_params = array(
						"plate_num" => $primer_pair_info["plate_letter"],
						"rp" => $primer_pair_info["R"],
						"fp" => $primer_pair_info["F"],
						"fasta_id" => $read_num,
						"seq" => $seq
					);
					//print_r($insert_params);
					//die();
					try {
						$result = $insert_q->execute($insert_params);
						//print_r($insert_params);
					} catch  (PDOException $ex) {die($ex);}
					//echo "Stored read! \n";
				}
			}
		$GLOBALS['found']++;
		$GLOBALS['singleton']++;	 
		break;    //end singletons.
		default:
		
			if ($count%2 != 0) {
				$GLOBALS['odds']++;
				break;
			} //not an even list, ignore
			$count_loop = 1;
			print_r($positions);
			while ($positions) {
				//create "singleton" array 
				$primer_info[0] =  array_shift($positions);
				$primer_info[1] =  array_shift($positions);
				$primer_pair_info = get_primer_info($primer_info);
				//echo "First = " .$first. " Second = " . $second ."\n";
				//test to see if F and R not RR or FF
			if ($primer_pair_info["first_dir"] != $primer_pair_info["sec_dir"]) {
				//check location of first primer. If <50 probably at the start and not middle.
				
					
					$length = $primer_pair_info["sec_loc"] - $primer_pair_info["first_loc"] +1;
					$read_num = get_read_num ($read['name']);
					$seq = substr($read['sequence'], $primer_pair_info["first_loc"], $length);
					//store read full length in output table.
					$insert_params = array(
						"plate_num" => $primer_pair_info["plate_letter"],
						"rp" => $primer_pair_info["R"],
						"fp" => $primer_pair_info["F"],
						"fasta_id" => $read_num . "_". $count_loop,
						"seq" => $seq
					);
					//print_r($insert_params);
					//die();
					try {
						$result = $insert_q->execute($insert_params);
						//print_r($insert_params);
					} catch  (PDOException $ex) {die($ex);}
					//echo "Stored read! \n";
				}
			
				$count_loop++;
				$GLOBALS['found']++;
			} //end while.
			$GLOBALS['multiple']++;
		
			break;
	}//end switch
	//no return value.
}//end function 

function get_primer_info ($primer_pair) {
$primers = array(
		"first_dir" => "",		//Primer direction
		"sec_dir" => "",			//Primer direction
		"first_loc" => "",		//Ending primer 1 location +1
		"sec_loc" => "",			//Beginning primer 2 location -1
		"F" => "",				//F Primer name
		"R" => "",				//R Primer name
		"plate_letter" => "",
	);
	list($loc1, $loc2) = array_keys($primer_pair);
	
	$primers["first_loc"] = $primer_pair[$loc1][0] + 1; 	//offset 1 for first nt of PCR product
	$primers["sec_loc"] = $loc2 - 1;				// offet -1 for first nt of PCR product
	
	$primer1 = array_shift($primer_pair);
	
	$primers["first_dir"] = substr($primer1[1], 0,1);
	if ($primers["first_dir"] == "F") {
		$primers["F"] = substr($primer1[1], 2);
	} else {
		$primers["R"] = substr($primer1[1], 2);
	}
	$primers["plate_letter"] = substr($primer1[1], 1,1);
	$primer2 = array_shift($primer_pair);
	$primers["sec_dir"] = substr($primer2[1], 0,1);
	if ($primers["sec_dir"] == "F") {
		$primers["F"] = substr($primer2[1], 2);
	} else {
		$primers["R"] = substr($primer2[1], 2);
	}
	return $primers;

}



?>

