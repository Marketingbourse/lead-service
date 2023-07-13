<?php

// Include the NuSOAP library
require_once('./lib/nusoap.php');

// Create a new SOAP client
$client = new nusoap_client('https://crm.roikingdom.net/public/legacy/soap.php?wsdl', true);

// Authenticate to SuiteCRM
$loginResult = $client->call('login', array('user_name' => 'apiconnector', 'password' => 'v$kvQbr6+KCEh3$1PSEq^FgQX6rQ~awGB#zaQNAsSHN2WxEW?q'));

// Check if authentication was successful
if ($loginResult['error']['number'] == 0) {
    $sessionId = $loginResult['id'];

    // Set the parameters for the get_entry_list call
    $params = array(
        'session' => $sessionId,
        'module_name' => 'Users',
        'query' => "segment_identifier_c = 'e24yQm4yOVFWSDE7IDIwMjMtMDctMDdUMTI6MjY6MTcrMDA6MDA7IHZYWUt4bktK'",
        'order_by' => '',
        'offset' => 0,
        'select_fields' => array('id', 'first_name', 'last_name', 'email'),
        'link_name_to_fields_array' => array(),
        'max_results' => 1,
        'deleted' => 0,
        'Favorites' => false,
    );

    // Call the get_entry_list method
    $result = $client->call('get_entry_list', $params);

    // Check if the API call was successful
    if ($result['error']['number'] == 0) {
        // Extract the user details
        $user = $result['entry_list'][0]['name_value_list'];

        // Output the user details
        echo "User ID: " . $user['id']['value'] . "<br>";
        echo "First Name: " . $user['first_name']['value'] . "<br>";
        echo "Last Name: " . $user['last_name']['value'] . "<br>";
        echo "Email: " . $user['email']['value'] . "<br>";
    } else {
        // Handle the error
        echo "Error: " . $result['error']['description'];
    }
} else {
    // Handle login error
    echo "Login failed: " . $loginResult['error']['description'];
}

?>