<?php
require ("functions.php");
/***********************************

	1. create Database
	2. make seqs table
	3. make PacBioBarCode table
	4. make primers table
	5. create output table
	6. add indexes to output table
	7. Run filter7
	8. Notify of completion

***********************************/

/*******

Gather Info

*/
if ($_POST['db_name']) {
	$html_filename = $_POST['output_name'];
	$db_name = $_POST['db_name'];
	$db_name .= '.sqlite3';
	$seq_filename = $_FILES["seq_file"]["name"];
	$pacBioPrimer_filename = $_FILES['pac_file']['name'];
	$primers_filename = $_FILES['primer_file']['name'];
	$run_name = $_POST['run_name'];

	move_uploaded_file($_FILES["seq_file"]["tmp_name"], "/tmp/" . $_FILES["seq_file"]["name"]);
	move_uploaded_file($_FILES["pac_file"]["tmp_name"], "/tmp/" . $_FILES["pac_file"]["name"]);
	move_uploaded_file($_FILES["primer_file"]["tmp_name"], "/tmp/" . $_FILES["primer_file"]["name"]);
	
	$db = new PDO('sqlite:../Databases/'.$db_name);
	$pac_stm = 'CREATE TABLE PacBioBarcodedPrimers (
			Name	TEXT,
			Primer	TEXT,
			Direction	TEXT,
			Well	TEXT
			)';
	$db->exec($pac_stm);
	$primers_stm = 'CREATE TABLE primers (
			primer_name	TEXT,
			seq	TEXT,
			direction	TEXT,
			plate_name	TEXT
		)';
	$db->exec($primers_stm);
	$output_stm = 'CREATE TABLE output (
			id	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
			plate_num	INTEGER,
			rp	TEXT,
			fp	TEXT,
			fasta_id	INTEGER,
			seq	TEXT
		)';
	$db->exec($output_stm);
	$coll_stm = 'CREATE TABLE collapsed (
			id	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
			fasta_id	TEXT,
			sequence	TEXT,
			fp	TEXT,
			rp	TEXT
		)';
	$db->exec($coll_stm);
	$index_out = 'CREATE INDEX out_index ON output (
			rp,
			fp,
			id
		)';
	$db->exec($index_out);
	$index_out = 'CREATE INDEX col_index ON collapsed (
			rp,
			fp,
			id
		)';
	$db->exec($index_out);
	$seq_stm = 'CREATE TABLE seqs (
			id	INTEGER,
			name	TEXT,
			sequence	TEXT,
			PRIMARY KEY(id)
		)';
	$db->exec($seq_stm);
	$options = array(
		'table' => 'primers'
	);
	/*****
	!!! This isn't working   !!!!
	
	
	************/
	$result = import_csv_to_sqlite($db, "/tmp/".$primers_filename, $options);
	print_r ($result);
}


?>

<html>
<head>
	<title>Set Up BarCode Filter</title>
	<link rel="stylesheet" href="http://dgenotebook.ucr.edu/apps/transfer/php/app.css" type="text/css"/>
	<meta http-equiv="CACHE-CONTROL" content="NO-CACHE"/>
</head>
<body>
<img src="http://dgenotebook.ucr.edu/apps/reu/images/banner.png" />
<div id="container">

<br /><br /><br />
<form action='set_up.php' method="post" enctype="multipart/form-data">
	Run Name: <input type="text" name="run_name" value="" tabindex="6"/> <br />
	Database Name (no spaces):  <input type="text" name="db_name" value="" tabindex="6"/><br />
	Output Filename: <input type="text" name="output_name" value="" tabindex="6"/><br /><br />
	
	<label for="seq_file">Sequence FASTA File:</label>
	<input type="file" name="seq_file" id="seq_file"><br>
	<label for="pac_file">PacBio Barcode CSV File:</label>
	<input type="file" name="pac_file" id="pac_file"><br>
	<label for="primer_file">Primer CSV File:</label>
	<input type="file" name="primer_file" id="primer_file"><br>
	<input type="submit" name="submit" value="Submit">
</div>


</form>
</body>
</html>
