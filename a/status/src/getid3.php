<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$wsdl = 'https://crm.roikingdom.net/public/legacy/soap.php?wsdl'; // Replace with your SuiteCRM URL
$username = 'apiconnector'; // Replace with your SuiteCRM username
$password = 'v$kvQbr6+KCEh3$1PSEq^FgQX6rQ~awGB#zaQNAsSHN2WxEW?q'; // Replace with your SuiteCRM password
$md5Password = "0ced5b0fcf46076de07579c4a0046ac6";
// Create SOAP client
//$client = new SoapClient($wsdl, array('trace' => 1));
$client = new SoapClient($wsdl);
// Authenticate
$client->__setLocation($wsdl);

$loginParameters = array( 'user_auth' => array(
        'user_name' => $username,
        'password' => $md5Password,
        'version' => '0.1'
    ),
    'application_name' => 'Connect',
    'name_value_list' => array()
);

$loginResult = $client->__soapCall('login',$loginParameters);
$sessionId = $loginResult->id;
// Search for lead based on segment_id and segment_identifier_c fields
$searchParameters = array(
    'session' => $sessionId,
    'module_name' => 'Leads',
//    'email1' => 'dsm202307081007@proton.me',
    'query'=>"email1 = 'robert.tom.2015@gmail.com'", //HERE SEND segment_identifier_c
    'order_by' => '',
    'offset' => 0,
    'select_fields' => ['id'],
    'link_name_to_fields_array' => array(),
    'max_results' => 1,
    'deleted' => 0,
    'Favorites' => false
);
echo "Search Params";
var_dump($searchParameters);
//exit;

  try {
    $searchResult = $client->__soapCall('get_entry_list', $searchParameters);
    var_dump($searchResult);
          } catch (\SoapFault $e) {
                echo "Error ".$e->getMessage();
                  }

//var_dump($searchResult);
exit;
//var_dump(json_encode($searchResult));
//$lead = $searchResult->entry_list[0]->name_value_list; // Assuming only one lead is returned
$leadID = $searchResult->entry_list[0];//->id;
//Here an ARRAY of arrays!!!!!! Need to parse it!

var_dump($leadID);

/*
if (!empty($leadID)) {
    // Output the lead information
    echo "Lead ID: $leadID";
} else {
    echo "Lead not found.";
}
*/
// Logout
$client->__soapCall('logout', array($sessionId));

?>

