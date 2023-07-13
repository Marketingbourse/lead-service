<?php

$globals = $GLOBALS;

class LeadStatusController
{

    private string $url = "https://crm.roikingdom.net/public/legacy/soap.php?wsdl";
    private string $user = "apiconnector";
    private string $password = 'v$kvQbr6+KCEh3$1PSEq^FgQX6rQ~awGB#zaQNAsSHN2WxEW?q';
    private string $password_md5 = "0ced5b0fcf46076de07579c4a0046ac6";
    private SoapClient $client;
    private string $log_path = '/home/services/logs/status.log';

    public function __construct()
    {
    }

    public function processLeadUpdateRequest(string $method, $id_obj = []): void
    {
        file_put_contents($this->log_path, "Input Object --------\r\n"
            .date('Y-m-d h:i:s')."\r\n"
            .json_encode($id_obj, JSON_PRETTY_PRINT)."\r\n"
            .json_encode($_SERVER, JSON_PRETTY_PRINT)
            ."\r\n---------------------\r\n".PHP_EOL, FILE_APPEND);

        if (!$id_obj) {
            exit('Data is empty');
        }

        if ($method == 'POST') {
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


            if (isset($id_obj['aff_sub']) && $id_obj['aff_sub']) {
                $aff_id = isset($id_obj['aff_id']) && $id_obj['aff_id'] ? $id_obj['aff_id'] : 1;

                $url_track = 'https://tracking.tripleafindings.com/aff_goal?a=lsr&goal_name=doi&adv_id='.$aff_id.'&transaction_id='.$id_obj['aff_sub'];
                $res_track = $this->curlRequest("GET", $url_track);

                file_put_contents($this->log_path, "Tracking Send -----------------\r\n"
                    .date('Y-m-d h:i:s')."\r\n".$url_track."\r\n".json_encode($res_track, JSON_PRETTY_PRINT)."\r\n---------------------\r\n".PHP_EOL, FILE_APPEND);
            }

            file_put_contents($this->log_path, "CRM --------------\r\n ".date('Y-m-d h:i:s')."\r\n"
                .json_encode($id_obj, JSON_PRETTY_PRINT)."\r\n".PHP_EOL, FILE_APPEND);
            //<
$searchParameters = array(
    'session' => $session_id,
    'module_name' => 'Leads',
//    'query'=>"segment_identifier_c = '".$id_obj['id']."'", //HERE SEND segment_identifier_c
'query' => "leads.id in (SELECT eabr.bean_id FROM email_addr_bean_rel eabr JOIN email_addresses ea ON (ea.id = eabr.email_address_id) WHERE eabr.deleted=0 and ea.email_address LIKE '".$id_obj['id']."')", //FILTER LEADS BY EMAIL!!!!!
    'order_by' => '',
    'offset' => 0,
    'select_fields' => ['id'],
    'link_name_to_fields_array' => array(),
    'max_results' => 2,
    'deleted' => 0,
    'Favorites' => false
);
$searchResult = $this->client->__soapCall('get_entry_list', $searchParameters);
//var_dump(json_encode($searchResult));
//$lead = $searchResult->entry_list[0]->name_value_list; // Assuming only one lead is returned
if (!isset($searchResult->entry_list[0])) {
    exit('Lead not found');
}
$lead = $searchResult->entry_list[0]->name_value_list[0];
//Here an ARRAY of arrays!!!!!! Need to parse it!

//var_dump($lead->value);

$leadId = false;
if (!empty($lead)) {
    $leadId = $lead->value;
    // Output the lead information
    //echo "Lead ID: $leadId<br>";
} else {
    //echo "Lead not found.";
}

//exit($leadId);
            
            
            $modify_lead = $this->client->set_entry($session_id, "Leads", array(
                array("name" => 'id', "value" => $leadId),
                array("name" => 'doi_c', "value" => true),
            ));

            $res = $modify_lead;
            echo var_dump($res);
            //echo var_dump($lead_obj);
            http_response_code(201);
        } else {
            http_response_code(405);
            header("Allow: PATCH");
        }
    }

    private function curlRequest($method, $url, $post_data = [], $headers = [])
    {
        echo 'request to service';
        $curl = curl_init();
        if (count($headers) != 0) {
            $contentHeaders = array(
                "Content-Type: application/json",
                "cache-control: no-cache"
            );
            $headers = array_merge($headers, $contentHeaders);
        } else {
            $headers = array(
                "Content-Type: application/json",
                "cache-control: no-cache"
            );
        }
        curl_setopt_array($curl, array(
            //CURLOPT_PORT => '2999',
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $post_data ? json_encode($post_data) : "",
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => true,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_VERBOSE => 1,
            CURLOPT_HEADER => 1,
            CURLOPT_POST => 1,
            CURLOPT_TIMEOUT_MS => 2000
        ));


        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $responseHeader = substr($response, 0, $header_size);
        $httpCode = curl_getinfo($curl)['http_code'];
        $body = substr($response, $header_size);
        $headersArray = [];//parseHeaders($responseHeader);
        $time = round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 4);

        try {
            $resp = json_decode($response, true);
            $dataInsert[] = $url;//$_SERVER['REQUEST_SCHEME'] . '://' .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $dataInsert[] = $method;
            $dataInsert[] = json_encode($post_data);
            $dataInsert[] = $body;
            $dataInsert[] = json_encode($headers);
            $dataInsert[] = json_encode($headersArray);
            $dataInsert[] = $time;
            $dataInsert[] = 'php';
            $dataInsert[] = $httpCode;
        } catch (Exception $e) {
            info($e->getMessage());
            echo $e->getMessage();
        }
        curl_close($curl);

        return ($body ==null)? []: json_decode($body, true);
    }
}









