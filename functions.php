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
	$blast_command = 'blastn -word_size 11 -query seq.fasta ';
	$blast_command .= '-subject plate_primers.fasta -outfmt "10 sseqid qstart qend length send sstart"';
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


function make_well_labels($file_names) {
	//$file_names is a list of files to process
	$well_names = array();	
	foreach ($file_names as $file) {
		$file_h = fopen($file, "w");
		while ($line = fread($file_h)) {
			
		
		
		
		
		
		}
	
	
	
	
	
	}




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

?>