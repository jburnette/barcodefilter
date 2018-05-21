<?php
function split_read ($read, $positions, $insert_q) {
	asort($positions);			//ensure array is sorted.
	$count = count($positions);
	switch($count) {
		case 1: 
			//store as is
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
					$insert_params = array(
						"plate_num" => substr($primers[0], 1, 1),
						"rp" => substr($primers[0], 2),
						"fp" => substr($primers[1], 2),
						"fasta_id" => $read['name'],
						"seq" => $read['sequence']
					);
					try {
						$result = $insert_q->execute($insert_params);
						//print_r($insert_params);
					} catch  (PDOException $ex) {die($ex);}
					//echo "Stored read! \n";
				}
			} 
		break;    //end singletons.
		default:
		break;
			if ($count%2 != 0) {break;} //not an even list, ignore
			$primers = array_keys($positions);
			$count = 1;
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
					$insert_params = array(
						"plate_num" => substr($primers1, 1, 1),
						"rp" => substr($primers1, 2),
						"fp" => substr($primers2, 2),
						"fasta_id" => $read['name'] . $count,
						"seq" => $sequence
					);
					try {
						$result = $insert_q->execute($insert_params);
					} catch  (PDOException $ex) {die($ex);}
					//echo "Stored read! \n";	
				} 
				$count++;
			} //end while.
			break;
	}//end switch
	//no return value.
}//end function 



?>

