<?php


function old_blast_read($read, $primer_file) {
	/******  THIS DOES NOT WORK.    ********/
	$blastn = "/Users/jamesburnette/Sites/ncbi-blast-2.7.1+/bin/blastn";
	$primer_file = "plate_primers.faasta";
	$comment = '>seq\n';
	$command = 'blastn -query <(echo -e "';
	$command .= $comment.$read;
	$command .= '") -subject ' . $primer_file . ' -outfmt "10 sseqid qend "';
	#echo $command;
	exec($command, $result[]);
	#$result=system($command);
	print_r($result);

}


function blast_read($read, $primer_file) {

	$comment = ">seq\n";
	$seq_out = $comment.$read;
	#echo $seq_out . "\n";
	file_put_contents("seq.fasta", $seq_out);
	$blast_command = 'blastn -word_size 11 -reward 1 -query seq.fasta ';
	$blast_command .= '-subject all_pacbio_barcodes.fasta -outfmt "10 sseqid qstart qend length send sstart"';
	$result = array();
	exec($blast_command, $result);
	//print_r($result);
	#echo $blast_command;
	unlink("seq.fasta");
	return $result;
	
}


function get_read_num ($comment) {

	list($junk, $read_num, $junk2) = explode ("/", $comment);
	return $read_num;

}


function make_well_labels($file_name) {
	//$file_names is a list of files to process
	$well_names = array();	
	$file_h = fopen($file_name, "r");
	while ($line = fgets($file_h)) {
		trim($line);
		list($plate, $well, $name) = explode(",", $line);		
		$well_names[$plate][$well] = $name;
	}
	fclose($file_h);
	//print_r($well_names);
	//die();
	return $well_names;
}

function primer_array($file_name) {
 	$file = fopen("./$file_name", "r") or die ("Cannot open primer file");
	$primer_array = array();
	while ($row = fgets($file)) {
		$row = trim($row);
		//string to array 
		$tmp_array = explode("\t", $row);
		//reverse complement primer 
		$primer_rev = strrev(trim($tmp_array[2]));
		$primer_rev_c = strtr($primer_rev, "AGCT", "TCGA");
		$tmp_array[3] = $primer_rev_c;
		array_push($primer_array, $tmp_array);
	}
	fclose($file);
	return $primer_array;
}

function toNumber($dest)
	//from: https://stackoverflow.com/questions/3580883/how-to-convert-some-character-into-numeric-in-php
    {
        if ($dest)
            return ord(strtolower($dest)) - 96;
        else
            return 0;
    }
function import_csv_to_sqlite(&$pdo, $csv_path, $options = array())
{
	#Source of code for function: https://gist.github.com/fcingolani/5364532
	extract($options);
	
	if (($csv_handle = fopen($csv_path, "r")) === FALSE)
		throw new Exception('Cannot open CSV file');
		
	if(!$delimiter)
		$delimiter = ',';
		
	if(!$table)
		$table = preg_replace("/[^A-Z0-9]/i", '', basename($csv_path));
	
	if(!$fields){
		$fields = array_map($field, fgetcsv($csv_handle, 0, $delimiter));
	}
	
	$create_fields_str = join(', ', array_map(function ($field){
		return "$field TEXT NULL";
	}, $fields));
	
	$pdo->beginTransaction();
	
	//$create_table_sql = "CREATE TABLE IF NOT EXISTS $table ($create_fields_str)";
	//$pdo->exec($create_table_sql);
	$insert_fields_str = join(', ', $fields);
	$insert_values_str = join(', ', array_fill(0, count($fields),  '?'));
	$insert_sql = "INSERT INTO $table ($insert_fields_str) VALUES ($insert_values_str)";
	echo $insert_sql;
	$insert_sth = $pdo->prepare($insert_sql);
	$pdo->errorInfo();
	$inserted_rows = 0;
	while (($data = fgetcsv($csv_handle, 0, $delimiter)) !== FALSE) {
		print_r ($data);
		$insert_sth->execute($data);
		$inserted_rows++;
	}
	
	$pdo->commit();
	
	fclose($csv_handle);
	
	return array(
			'table' => $table,
			'fields' => $fields,
			'insert' => $insert_sth,
			'inserted_rows' => $inserted_rows
		);
	}
//Testing functions
#get_read_num(">m180427_214944_42257R_c101419162550000001823307808281820_s1_p0/16827/ccs");
/****
$read = "ACACGCATGACACACTGTAAAACGACGGCCAGATGGTGGGCTAATGAACTCTGGCTTTGAGTACATACTCAAGGCTGGTGGGGTTGAGCGAGAGAAGGACTACCCTTACACCGGAACCGACGGTAGTTCCTGCAAATTTGACAAAAGCAAAATTGCTGCAGCTGTAACTAATTTCAGTGTTATTTCCTCTGATGAAGATCAAATGGCTGCAAATTTGGTGAAACATGGCCCTCTGGCAGGTAATATAGCTTCGATAGATTTACCTCATATTTCGTTTGTGAGCTTTTCTCTTCACTGTGAGCTTTCCAAAATAACATTTGGAAAAGTTAGTTAATTAATTAATTTCTTTTGAGATGTTGGTAATTTTTTTATTGAGCGGAATGGATAGAATGATGACAGAATTTGTGCTGATCTTGCTGTTTGCTTTTGCAGTGGGTATCAATGCCGTTTGGATGCAAACATATATTGGAGGAGTTTCATGCCCATACATTTGCGGGAAGTATTTGGATCATGGAGTGCTTATCGTGGGCTATGGATCTTCAGGTTTCGCCCCGATCCGATTCAAGGAGAAGCCTTACTGGGTCATAGCTGTTTCCTGCAGTCAGCGTCACGAGGAGAGCGCTGACTCTGCAGGAAACAGCTATGACGGCACCCTGTATCCTGTATATGAAGCACGAAACTCCTCATTAGTTAAGTCTGAAAATTCATTGGTGCCTAACTTGTACGTCCGATTCGCCTCCTCATTGGCTTTCTCAATGTATTCAAGGTTTTCTTTGAATATCTTAAACCGCATTTCCTTCTCCAGGTCGTCCTTGTAAGTGCGCCCATGCTCAGCCATCCATTTTTCATGCTTCTCAACAATGGATGGTTCATGCAGCGACCGGCCGGCGACCGACACCATTTTAGATGAATAAGTCACCAGAATGATCATAGACAGGGCAATGATATCAAAGCCTGGCCGTCGTTTTACGCAGAGTCATGTATAG";

#$read = "TAGACGTACGATCGA";
$primer_file = "plate_primers.fasta";
$test = blast_read($read, $primer_file);
print_r($test);
***/

//$file = "../spring_2018/all_plates.csv";
//make_well_labels($file);

?>