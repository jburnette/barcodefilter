<?php

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
if ($_POST['$db_name']) {
	$html_filename = '';
	$db_name = '';
	$seq_filename = '';
	$pacBioPrimer_filename = '';
	$primers_filename = '';



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
	Run Name: <input type="text" name="run_name" value="" tabindex="6"/>
	Database Name (no spaces): Run Name: <input type="text" name="run_name" value="" tabindex="6"/>
	Output Filename: Run Name: <input type="text" name="output_name" value="" tabindex="6"/>
	
	<label for="seq_file">Sequence FASTA File:</label>
	<input type="file" name="seq_file" id="seq_file"><br>



</form>
</body>
</html>
