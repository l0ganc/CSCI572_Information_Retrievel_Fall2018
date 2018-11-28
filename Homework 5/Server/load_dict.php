<?php  
	$dict = [];
	$mapfile = fopen("URLtoHTML_mercury.csv","r");
	while (($line = fgets($mapfile)) !== false) {
        $dict[explode(",",$line)[0]] = explode(",",$line)[1];
    }

    fclose($mapfile);
?>