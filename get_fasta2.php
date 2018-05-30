<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);


//get primer numbers
list($plate, $row, $column) = explode ("-",$_GET['cell']);
echo $plate ." " .$row ." ". $column;
$rp = $row . '1';
$fp = "A".$column;
//$rp = "B1";
//$fp = 'A1';
//$plate = "A";
//connect to Database
$db = new PDO('sqlite:../Databases/spring2018_2.sqlite'); 




//get sequences using primers
//$select = "SELECT seqs.name, seqs.sequence FROM output JOIN seqs on output.fasta_id = seqs.id WHERE output.plate_num = :plate AND output.rp = :rp AND output.fp = :fp ";
$select = "SELECT output.fasta_id, output.seq FROM output WHERE output.plate_num = :plate AND output.rp = :rp AND output.fp = :fp ";

$params = array(
	":plate" => $plate,
	":rp" => $rp,
	":fp" => $fp
);
try {
	$select_stmt = $db->prepare($select);
	$result = $select_stmt->execute($params);
} catch (PODExecption $ex) {
	die ($ex);
}
$fatsa ='';
while($rows = $select_stmt->fetch()) {
	$fasta .= ">". $rows['fasta_id'] . "\n" .wordwrap($rows['seq'], 80, "\n", true) . "\n";
}
//echo $fasta;

?>
<html>
<head>
	<title>Sequences</title>
	
	<meta http-equiv="CACHE-CONTROL" content="NO-CACHE"/>
</head>
<body>
<pre>
<?php echo $fasta; ?>
</pre>
</body>
</html>
