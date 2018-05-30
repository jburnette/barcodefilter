<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$db = new PDO('sqlite:../Databases/spring2018_2.sqlite'); 
//Get number of plates in table.
$select_plates = "SELECT DISTINCT(plate_num) FROM output WHERE 1 ORDER BY plate_num";
try {
	$select_plates_stmt = $db->prepare($select_plates);
	$result = $select_plates_stmt->execute();
}   catch (PODExecption $ex) {
	die ($ex);
}
$select_num_seqs = "SELECT COUNT(*) FROM output WHERE output.plate_num = :plate AND output.rp = :rp AND output.fp = :fp";
//echo "$";
try {
	$select_num_seqs_stmt = $db->prepare($select_num_seqs);
} catch (PODExecption $ex) {
	die ($ex);
}
$plates_array = array();
$plate_num_array = array();
while ($plate_fetch = $select_plates_stmt->fetch()) {
//echo "!";
	$plate = $plate_fetch['plate_num'];
	$table_num = '';
	$table = '';
	$col_num ='A';
	$plate_num_array = array();
	while ($col_num < "I") { 
		$row_letter = '1';
		$table .= "<tr><td><b>".$col_num."</b></td>";
		$table_num .= "<tr><td><b>".$col_num."</b></td>";
		while ($row_letter < "13") {
			$table .= "<td style'width:300px'>";
			$table_num .= "<td style'width:300px'>";
			//$table .= "<input type='text' name='".$plate. '-' .$col_num. '-'. $row_letter ."' size='4' value='".$plate. '-' .$col_num. '-'. $row_letter ."'> ";	
			$table .= "<a href=" .'./get_fasta.php?cell=' .$plate. '-' .$col_num. '-'. $row_letter ." target='_blank'>&nbsp&nbsp&nbsp".$col_num . $row_letter ."&nbsp&nbsp&nbsp&nbsp</a> </td>";	
			//get count of sequences
			$params = array(
				":plate" => $plate,
				":rp" => $col_num . "1",
				":fp" => 'A'.$row_letter
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
//print_r($plates_num_array);

//die();






/**
#Create table
$table = '';
$col_num ='A';
$plate = "A";
while ($col_num < "I") { 
$row_letter = '1';
$table .= "<tr><td><b>".$col_num."</b></td>";
	while ($row_letter < "13") {
		$table .= "<td style'width:300px'>";
			//$table .= "<input type='text' name='".$plate. '-' .$col_num. '-'. $row_letter ."' size='4' value='".$plate. '-' .$col_num. '-'. $row_letter ."'> ";	
			$table .= "<a href=" .'./get_fasta.php?cell=' .$plate. '-' .$col_num. '-'. $row_letter .">&nbsp&nbsp&nbsp".$col_num . $row_letter ."&nbsp&nbsp&nbsp&nbsp</a> ";	

	$row_letter ++;
	}
	$table .= "</tr>";
	$col_num ++;
} #column num while
**/

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


