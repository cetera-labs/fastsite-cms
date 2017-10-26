<?
require_once(__DIR__.'/include/constants.php');

if (file_exists(DOCROOT.LIBRARY_PATH)) {
	header('Location: /cms/');
	die();
}

try {
	echo 'Trying to download '.LIBRARY_FILE.' ... ';
	download_file(LIBRARY_FILE);
	echo 'OK<br>';
	
	echo 'Extracting archive ... ';
	$zip = new \ZipArchive;
	$zip->open(LIBRARY_FILE);
	$zip->extractTo(DOCROOT);
	unlink(LIBRARY_FILE);
	echo 'OK<br>';	
	
	echo 'Redirect to backoffice ... ';
	header('Location: /cms/');
	die();
	
} catch (\Exception $e) {
	print 'FAIL.<br>';	
	print $e->getMessage();
}

function download_file($file, $local = false) {
    if (!$local) $local = $file;

	$d = curl_get(DISTRIB_HOST.$file);
	if (!$d) return false;
	file_put_contents($local, $d);
	return true;

}

function curl_get($url) {   
    $defaults = array(
        CURLOPT_URL => $url,
		CURLOPT_SSL_VERIFYHOST => 0,
		CURLOPT_SSL_VERIFYPEER => 0,
        //CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => TRUE,
        //CURLOPT_TIMEOUT => 4
    );
   
    $ch = curl_init();
    curl_setopt_array($ch, $defaults);
    if( ! $result = curl_exec($ch))
    {
        trigger_error(curl_error($ch));
    }
    curl_close($ch);
    return $result;
}