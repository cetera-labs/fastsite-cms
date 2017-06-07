<?php
header('Content-type: application/x-javascript; charset=UTF8'); 
?>
var LangData = <?php include('lang/data.php') ?>;

function _(key) {
	if (LangData[key]) return LangData[key];
	return key;
}