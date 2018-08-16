<?php
function split_read ($read, $positions, $insert_q, $primer_sel_stm, $odd_insert_stm) {
	//asort($positions);			//ensure array is sorted.
	$count = count($positions);
	switch($count) {
	
		case 1: 
			//ignore, don't have both primers
			$GLOBALS['odds1']++;
			$temp = array_shift($positions);
			$values['primer'] = $temp[1];
			$values['seq'] = $read['sequence'];
			$values['name'] = $read['name'];
			$values['which'] = 1;
			$values['location'] = $temp[0];
			//print_r($values);
			
			try {
				$result = $odd_insert_stm->execute($values);
			} catch (PDOException $ex){die ($ex);}
			
			break;
		case 2: 
			$primer_pair_info = get_primer_info($positions, $primer_sel_stm);
			
			//test to see if F and R not RR or FF
			if ($primer_pair_info["first_dir"] != $primer_pair_info["sec_dir"]) {
				if ($primer_pair_info["lengthF"] >= 10 and $primer_pair_info["lengthR"] >= 10 ) { //match high enough.
				//check location of first primer. If <50 probably at the start and not middle.
				if ($primer_pair_info["first_loc"] < 100) {
				
					$length = $primer_pair_info["sec_loc"] - $primer_pair_info["first_loc"] +1;
					
					$read_num = get_read_num ($read['name']);
					$seq = substr($read['sequence'], $primer_pair_info["first_loc"], $length);
					//store read full length in output table.
					$insert_params = array(
						"rp" => $primer_pair_info["R"],
						"fp" => $primer_pair_info["F"],
						"fasta_id" => $read_num,
						"seq" => $seq
					);
					//print_r($insert_params);
					//die();
					try {
					
						$result = $insert_q->execute($insert_params);
						$GLOBALS['found']++;
						//echo "Singleton found!\n";
						$GLOBALS['singleton']++;
					} catch  (PDOException $ex) {die($ex);}
					//echo "Stored read! \n";
				}
			}
			}
		break;    //end singletons.
		default:
		
			/**if ($count%2 != 0) {
				$GLOBALS['odds']++;
				print_r($positions);
				die();
				break;
			} //not an even list, ignore **/
			$count_loop = 1;
			$chunked_array = array_chunk($positions, 2, true);
			//print_r ($chunked_array);
			//die;
			foreach ($chunked_array as $primer_info) {
			//print_r($primer_info); die();
				if (count($primer_info)%2 != 0) {
					//echo (count($primer_info));
					$GLOBALS['odds2']++;
					$temp = array_shift($primer_info);
					$values['primer'] = $temp[1];
					$values['seq'] = $read['sequence'];
					$values['name'] = $read['name'];
					$values['which'] = 2;
					$values['location'] = $temp[0];
			
			
				try {
					$result = $odd_insert_stm->execute($values);
				} catch (PDOException $ex){die ($ex);}
			
					break;
				}
				//create "singleton" array 
				//$primer_info1 =  array_shift($positions);
				//$primer_info2 =  array_shift($positions);
				//$primer_info = array_merge($primer_info1,$primer_info2);
				//print_r($primer_info);
				//die;
				//echo "First = " .$first. " Second = " . $second ."\n";
				$primer_pair_info = get_primer_info($primer_info, $primer_sel_stm);
				//print_r($primer_pair_info); 
				//test to see if F and R not RR or FF
				//echo $primer_pair_info["lengthF"] . " primerpair " . $primer_pair_info["lengthR"] . "\n";
			if ($primer_pair_info["first_dir"] != $primer_pair_info["sec_dir"]) {
				//check location of first primer. If <50 probably at the start and not middle.
				//echo $primer_pair_info["lengthF"] . " primerpair " . $primer_pair_info["lengthR"] . "\n";
					if ($primer_pair_info["lengthF"] >= 1 and $primer_pair_info["lengthR"] >= 1 ) { //high enough
					$length = $primer_pair_info["sec_loc"] - $primer_pair_info["first_loc"] +1;
					$read_num = get_read_num ($read['name']);
					$seq = substr($read['sequence'], $primer_pair_info["first_loc"], $length);
					//store read full length in output table.
					$insert_params = array(
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
						$GLOBALS['found']++;
						$GLOBALS['multiple']++;
						//echo "multiple found!\n";
					} catch  (PDOException $ex) {die($ex);}
					//echo "Stored read! \n";
				}
				}
			
				$count_loop++;
			} //end while.
			//die();
			break;
	}//end switch
	//no return value.
}//end function 

function get_primer_info ($primer_pair, $primer_sel_stm) {
//print_r($primer_pair);
$all_primers = $primer_pair;
$primers = array(
		"first_dir" => "",		//Primer direction
		"sec_dir" => "",			//Primer direction
		"first_loc" => "",		//Ending primer 1 location +1
		"sec_loc" => "",			//Beginning primer 2 location -1
		"F" => "",				//F Primer name
		"R" => "",				//R Primer name
		"lengthF" =>"",
		"lengthR" =>""
	);
	list($loc1, $loc2) = array_keys($primer_pair);
	//echo $loc1 . "loc1";
	//$primers["first_loc"] = $primer_pair[$loc1][0] + 1; 	//offset 1 for first nt of PCR product
	//$primers["sec_loc"] = $loc2 - 1;				// offet -1 for first nt of PCR product
	//print_r($primer1);
	//die();
	/****** REMOVE THIS!!!!**********/
	$primer1 = array_shift($primer_pair);
	$primer2 = array_shift($primer_pair);
	try {
		$primer1_result=$primer_sel_stm->execute(array(':name' => $primer1[1]));
		
	
	//echo ($primer2[1]);
	$first_primer = $primer_sel_stm->fetch();
	} catch  (PDOException $ex) {die($ex);}
	try {
		$primer2_result=$primer_sel_stm->execute(array(':name' => $primer2[1]));
		
	} catch  (PDOException $ex) {die($ex);}
	$second_primer = $primer_sel_stm->fetch();
	
	//$primers["first_dir"] = substr($primer1[1], 0,1);
	$primers['first_dir']=$first_primer['Direction'];
	$primers['sec_dir']=$second_primer['Direction'];
	//need array with primer names and direction and plate number
	
	//print_r($primer1);
	if ($primers['first_dir'] == $primers['sec_dir']) {return;}
	//echo $primer1[3] . "   ";
	if ($primers["first_dir"] == "F") {
		$primers["F"] = $first_primer['Name'];
		$primers["lengthF"] = $primer1[3];
		//echo $primers["lengthF"];
		$extra_seq = 16 - $primer1[3];  //if not full length match to subject
		//echo "loc1 " . $loc1;
		//print_r($all_primers);
		$primers["first_loc"] = ($all_primers[$loc1][0] + 17 + $extra_seq);	//add 16 for M13F and +1 to move to next nt
		//echo "lengthF ". $primers["lengthF"] . " ";
	} else {
		//$primers["R"] = substr($primer1[1], 2);
		$primers["R"] = $first_primer['Name'];
		$primers["lengthR"] = $primer1[3];
		$extra_seq = 16 - $primer1[4];  //if not full length match to subject
		$primers["sec_loc"] = $loc2 - 18 -$extra_seq;					//add 17 for M13R and -1 to move to next nt
		//echo $primers["lengthR"] . " ";

	}
	//need array with primer names and plate
//print_r($primer2);
	//die();
	
	//echo $primer2[4] . "\n";
	if ($primers["sec_dir"] == "F") {
		$primers["F"] = $second_primer['Name'];
		$primers["lengthF"] = $primer2[3];
		$extra_seq = 16 - $primer2[3];  //if not full length match to subject
		$primers["first_loc"] = $all_primers[$loc1][0] + 17  + $extra_seq;	//add 16 for M13F and +1 to move to next nt
	} else {
		$primers["R"] = $second_primer['Name'];
		$primers["lengthR"] = $primer2[3];
		$extra_seq = 16 - $primer2[3];  //if not full length match to subject
		$primers["sec_loc"] = $loc2 - 18 -$extra_seq;					//add 17 for M13R and -1 to move to next nt
	}
	//print_r($primers);
	return $primers;

}



?>

