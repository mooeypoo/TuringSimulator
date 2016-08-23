<?php

$d = $_GET['d'];

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");
header("Content-Disposition: attachment;filename=turing.moo ");
//header("Content-Transfer-Encoding: binary ");

//print json_encode($d);
print $d;
?>