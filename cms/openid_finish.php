<?php
require_once('include/common.php');
include_once(CMSROOT.'/include/classes/UserAuthAdapterOpenID.php');

$application->setFrontOffice(true);
$application->connectDb();
$application->initSession();

$result = $application->getAuth()->authenticate(new UserAuthAdapterOpenID());

if (isset($_SESSION['return_url'])) 
  $r = $_SESSION['return_url'];
  else $r = '/';

header("Location: ".$r);
exit;