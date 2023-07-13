<?php

$globals = $GLOBALS;

class SoapClient {
    private string $url = "https://crm.roikingdom.net/public/legacy/soap.php?wsdl";
    private string $user = "apiconnector";
    private string $password = "v$kvQbr6+KCEh3$1PSEq^FgQX6rQ~awGB#zaQNAsSHN2WxEW?q";
    private string $password_md5 = "0ced5b0fcf46076de07579c4a0046ac6";
    private SoapClient $client;

            $this->client = new SoapClient($this->url);
            $this->client->__setLocation($this->url);
            $userAuth = array(
                'user_name' => $this->user,
                'password' => $this->password_md5,
                'version' => '0.1'
            );
            $appName = 'Connector';
            $nameValueList = array();
            $loginResults = $this->client->login($userAuth, $appName, $nameValueList);
            $session_id = $loginResults->id;
	    $searchParameters = array(
    'session' => $sessionId,
    'module_name' => 'Leads',
    'query' => "leads.segment_identifier_c = 'e24yQm4yOVFWSDE7IDIwMjMtMDctMDdUMTI6MjY6MTcrMDA6MDA7IHZYWUt4bktK'", // Replace with your segment_id value
    'order_by' => '',
    'offset' => 0,
    'select_fields' => array(),
    'link_name_to_fields_array' => array(),
    'max_results' => 1,
    'deleted' => 0,
    'Favorites' => false
);


$searchResult = $client->__soapCall('get_entry_list', array($searchParameters));
$lead = $searchResult->entry_list[0]->name_value_list; // Assuming only one lead is returned

if (!empty($lead)) {
    $leadId = $lead->id->value;
    $firstName = $lead->first_name->value;
    $lastName = $lead->last_name->value;

    // Output the lead information
    echo "Lead ID: $leadId<br>";
    echo "First Name: $firstName<br>";
    echo "Last Name: $lastName<br>";
} else {
    echo "Lead not found.";
}

}
?>
