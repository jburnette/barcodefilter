<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$collapser_path = "./bin/fastx_collapser";
//get primer numbers
list($plate, $row, $column) = explode ("-",$_GET['cell']);
#echo $plate ." " .$row ." ". $column;
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
$fasta ='';
while($rows = $select_stmt->fetch()) {
	#$fasta .= ">". $rows['fasta_id'] . "\n" .wordwrap($rows['seq'], 80, "\n", true) . "\n";
	$fasta .= ">". $rows['fasta_id'] . "\n" .$rows['seq'] . "\n";
}
//echo $fasta;
file_put_contents("/var/tmp/temp_out.fasta", $fasta);
#$command = $collapser_path .  ' -i /var/tmp/temp_out.fasta -o /var/tmp/temp_in.fasta 2>&1';
$command = $collapser_path .  ' -i /var/tmp/temp_out.fasta';
#$command = $collapser_path . " -h";
#echo $command;

exec($command, $result);
//print_r($result);
$fastx = implode("\n", $result);

//die();
?>
<html>
<head>
	<title>Sequences</title>
	
	<meta http-equiv="CACHE-CONTROL" content="NO-CACHE"/>
</head>
<body>
<pre>
<?php echo $fastx; ?>
</pre>
</body>
</html>
