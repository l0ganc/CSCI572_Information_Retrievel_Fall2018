<?php  
require_once('Apache/Solr/Service.php');
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;

$solr = new Apache_Solr_Service('localhost', 8983, '/solr/myexample/');
$response = $solr->suggest($query);
echo $response->getRawResponse();
?>