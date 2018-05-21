<?php
echo "test";
$primer_array = array();
$row_number = 1;
while ($row_number < 9){
	$row_letter ='A';
	while ($row_letter < "I") { 
		$column = '1';
		$rp = $row_letter . $row_number;
		while ($column < "13") {
			$fp = $row_letter . $column;
			$column ++;
			echo $rp . "-" . $fp . "\n";
		}
	$row_letter ++;
	} 
	$row_number ++;
}


?>