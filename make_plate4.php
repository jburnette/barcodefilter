<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$database = "salsa_2018_1.db";
require ("functions.php");
$db = new PDO('sqlite:../databases/'.$database);
//Get plate names
$select_plates = "SELECT DISTINCT(plate_name) FROM primers WHERE 1 ORDER BY plate_name";
try {
	$select_plates_stmt = $db->prepare($select_plates);
	$result = $select_plates_stmt->execute();
}   catch (PODExecption $ex) {
	die ($ex);
}
//prepare statement to count number of sequences
$select_num_seqs = "SELECT COUNT(*) FROM output WHERE output.rp = :rp AND output.fp = :fp";

try {
	$select_num_seqs_stmt = $db->prepare($select_num_seqs);
} catch (PODExecption $ex) {
	die ($ex);
}
//prepare select statemnt for primer names.
$primer_stmt = "SELECT primer_name FROM primers WHERE plate_name = :plate AND direction = :dir";
try {
	$primer_stmt_h = $db->prepare($primer_stmt);
} catch (PDOExecption $ex) {
	die ($ex);
}

/**make well labels (if have file).  Add back later
$well_file_names="../spring_2018/all_plates.csv";
$well_labels = make_well_labels($well_file_names);   //list of file names
**/

$plates_array = array();
$plate_num_array = array();
while ($plate_fetch = $select_plates_stmt->fetch()) {
	$plate = $plate_fetch['plate_name'];
	
	//get forward primers
	$params = array(":plate"=>$plate, ":dir"=>"F");
	try {
		$result = $primer_stmt_h->execute($params);	
	} catch (PDOExecption $ex) {
		die ($ex);
	}
	$for_p = $primer_stmt_h->fetchAll();
	$params = array(":plate"=>$plate, ":dir"=>"R");
	try {
		$result = $primer_stmt_h->execute($params);	
	} catch (PDOExecption $ex) {
		die ($ex);
	}
	$rev_p = $primer_stmt_h->fetchAll();
	$table_num = '';
	$table = '';
	$col_num ='A';
	$plate_num_array = array();
	while ($col_num < "I") { 
		$row_letter = '1';
		$col_number = toNumber($col_num) - 1; 	//converts letter to number for array use.
		$rp = $rev_p[$col_number]["primer_name"];
		$table .= "<tr><td><b>".$col_num."</b></td>";
		$table_num .= "<tr><td><b>".$col_num."</b></td>";
		while ($row_letter < "13") {
			$row_num = $row_letter - 1;
			$fp = $for_p[$row_num]["primer_name"];;
			$table .= "<td style'width:300px'>";
			$table_num .= "<td style'width:300px'>";
			//$table .= "<input type='text' name='".$plate. '-' .$col_num. '-'. $row_letter ."' size='4' value='".$plate. '-' .$col_num. '-'. $row_letter ."'> ";	
			//$table .= "<a href=" .'./get_fasta2_collapser.php?cell=' .$plate. '-' .$col_num. '-'. $row_letter ." target='_blank'>&nbsp". $well_labels[$plate][$col_num . $row_letter] ."&nbsp</a> </td>";	
			$table .= "<a href=" .'./get_fasta4.php?plate=' .$plate. '&forward=' .$fp. '&reverse='. $rp. '&db='. $database ." target='_blank'>&nbsp" .$fp. '-'. $rp ."&nbsp</a> </td>";	
			//get count of sequences
			$params = array(
				":rp" => $rp,
				":fp" => $fp
			);
			//print_r($params);
			try {
				$result = $select_num_seqs_stmt->execute($params);
			} catch (PODExecption $ex) {die ($ex);}	
			$num_seqs = $select_num_seqs_stmt->fetch();
			$table_num .= "&nbsp;&nbsp;" . $num_seqs[0] . "&nbsp;&nbsp;</td>" ;	

		$row_letter ++;
		}
	$table .= "</tr>";
	$table_num .= "</tr>";
	$col_num ++;
	$plates_array[$plate] = $table;
	$plates_num_array[$plate] = $table_num;
	} #column num while
}	//end plate while.
?>

<html>
<head>
	<title>Sequences for Analysis</title>
	<link rel="stylesheet" href="http://dgenotebook.ucr.edu/apps/transfer/php/app.css" type="text/css"/>
	<meta http-equiv="CACHE-CONTROL" content="NO-CACHE"/>
</head>
<body>
<img src="http://dgenotebook.ucr.edu/apps/reu/images/banner.png" />
<div id="container">

<br /><br /><br />
<?php 
foreach ($plates_array as $letter=>$string) {
//print_r($plates_array);

	echo "<h3>Plate " . $letter . " </h3>";
	echo "<p>Click a plate well to see sequences from that PCR product.</p>";
	echo "
	<table style'width:4000px' border='1'>
	<tr><th></th>
		<th>1</th>
	    <th>2</th>
	    <th>3</th> 
	    <th>4</th>
	    <th>5</th>
		<th>6</th>
		<th>7</th>
	    <th>8</th>
	    <th>9</th> 
	    <th>10</th>
	    <th>11</th>
		<th>12</th>
	</tr>";
	echo $string; 
	echo "</table><br /><br /><br />";
	echo "<h3>Sequence counts per well</h3>";
	echo "
	<table style'width:4000px' border='1'>
	<tr><th></th>
		<th>1</th>
	    <th>2</th>
	    <th>3</th> 
	    <th>4</th>
	    <th>5</th>
		<th>6</th>
		<th>7</th>
	    <th>8</th>
	    <th>9</th> 
	    <th>10</th>
	    <th>11</th>
		<th>12</th>
	</tr>";
	echo $plates_num_array[$letter];	
	echo "</table><br /><br /><br />";
	
}
?>


</div>
</body>
</html>


