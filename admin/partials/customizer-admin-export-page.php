<?php
$file_name = $_GET['file_name'];
$dir = $_GET['dir'];
header("Content-type: text/plain");
header('Content-disposition: attachment; filename="'.$file_name.'"');
readfile($dir . '/' . $file_name);
