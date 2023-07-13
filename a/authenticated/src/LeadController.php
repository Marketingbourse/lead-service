<?php

$globals = $GLOBALS;

class LeadController
{

    private string $url = "https://crm.roikingdom.net/public/legacy/soap.php?wsdl";
    private string $user = "apiconnector";
    private string $password = 'v$kvQbr6+KCEh3$1PSEq^FgQX6rQ~awGB#zaQNAsSHN2WxEW?q';
    private string $password_md5 = "0ced5b0fcf46076de07579c4a0046ac6";
    private SoapClient $client;
    private string $secret = 'n2Bn29QVH1';

    private string $log_path = '/home/services/logs/authenticated.log';

    public function __construct()
    {
    }

    public function processLeadRequest(string $method, $lead_data = []): void
    {
        file_put_contents($this->log_path, "Input Object --------\r\n"
            .date('Y-m-d h:i:s')."\r\n"
            .json_encode($lead_data, JSON_PRETTY_PRINT)."\r\n"
            .json_encode($_SERVER, JSON_PRETTY_PRINT)
            ."\r\n---------------------\r\n".PHP_EOL, FILE_APPEND);

        if (!$lead_data) {
            exit('Data is empty');
        }

        if ($this->isValidToken($lead_data['auth'])){
            $this->addLeadToSuiteCrm($lead_data ,$method);
        } else {
            http_response_code(401);
            header("Please provide valid token");
        }
        //if (array_key_exists('token', $lead_data) && $this->isValidToken($this->extractToken($lead_data['token']))) {
        //    $this -> addLeadToSuiteCrm($lead_data, $method);
        //} else {
        //    http_response_code(401);
        //    echo('please provide valid token');
        //}
    }


    private function addLeadToSuiteCrm($lead_data, $method): void
    {
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
            $add_lead = $this->client->set_entry($session_id, "Leads", array(
                array("name" => 'lead_subscription_date_c', "value" => $lead_data['date'] ?? null),
                array("name" => 'first_name', "value" => $lead_data['first_name'] ?? null),
                array("name" => 'last_name', "value" => $lead_data['last_name'] ?? null),
                array("name" => 'email1', "value" => $lead_data['email']),
                array("name" => 'soi_c', "value" => true),
                array("name" => 'doi_c', "value" => false),
                array("name" => 'phone_c', "value" => $lead_data['phone'] ?? null),
                array("name" => 'vertical_c', "value" => $lead_data['vertical'] ?? null),
                array("name" => 'aff_id_c', "value" => $lead_data['aff_id'] ?? null),
                array("name" => 'sign_up_page_url_c', "value" => $lead_data['sing_up_page_url'] ?? null),
                array("name" => 'source_c', "value" => $lead_data['source'] ?? null),
                array("name" => 'assigned_user_name', "value" => 'SuiteCRM-lead_Connector'),
                array("name" => 'ip_address_c', "value" => $lead_data['ip_address'] ?? null),
                array("name" => 'geo_c', "value" => $lead_data['geo'] ?? null),
                array("name" => 'country_c', "value" => $lead_data['country'] ?? null),
                array("name" => 'region_c', "value" => $lead_data['region'] ?? null),
                array("name" => 'province_c', "value" => $lead_data['province'] ?? null),
                array("name" => 'state_c', "value" => $lead_data['state'] ?? null),
                array("name" => 'device_c', "value" => $lead_data['device'] ?? null),
                array("name" => 'os_c', "value" => $lead_data['os'] ?? null),
                array("name" => 'browser_c', "value" => $lead_data['browser'] ?? null),
                array("name" => 'segment_identifier_c', "value" => $lead_data['auth'] ?? null),
                array("name" => 'affsub_c', "value" => $lead_data['aff_sub'] ?? null),
                array("name" => 'affsub2_c', "value" => $lead_data['aff_sub2'] ?? null),
                array("name" => 'affsub3_c', "value" => $lead_data['aff_sub3'] ?? null),
                array("name" => 'affsub4_c', "value" => $lead_data['aff_sub4'] ?? null),
                array("name" => 'affsub5_c', "value" => $lead_data['aff_sub5'] ?? null),
                
                
                array("name" => 'transaction_id_c', "value" => $lead_data['transaction_id'] ?? null),
                array("name" => 'aff_click_id_c', "value" => $lead_data['aff_click_id'] ?? null),
                array("name" => 'affiliate_id_c', "value" => $lead_data['affiliate_id'] ?? null),
                array("name" => 'offer_id_c', "value" => $lead_data['offer_id'] ?? null),
            ));

            if (isset($lead_data['aff_sub']) && $lead_data['aff_sub']) {
                $url_track = 'https://tracking.tripleafindings.com/aff_lsr?transaction_id='.$lead_data['aff_sub'];
                $res_track = $this->curlRequest("GET", $url_track);

                file_put_contents($this->log_path, "Tracking Send -----------------\r\n"
                    .date('Y-m-d h:i:s')."\r\n".$url_track."\r\n".json_encode($res_track, JSON_PRETTY_PRINT)."\r\n---------------------\r\n".PHP_EOL, FILE_APPEND);
            }

            $res = var_dump($add_lead);
            $lead_id = $add_lead->id;


            file_put_contents($this->log_path, "CRM -----------------\r\n"
                .date('Y-m-d h:i:s')."\r\n"."\r\n"
                .json_encode($add_lead, JSON_PRETTY_PRINT)."\r\n---------------------\r\n".PHP_EOL, FILE_APPEND);

            
$segment_request = [
    "userId" => $lead_data['auth'],
    "context" => [
        "messaging_subscriptions" => [
            [
                "key" => $lead_data['email'],
                "type" => "EMAIL",
                "status" => "SUBSCRIBED"
            ]
        ],
        "externalIds" => [
            [
                "id" => $lead_data['email'],
                "type" => "email",
                "collection" => "users",
                "encoding" => "none"
            ]
        ],
        "traits" => [
            'lead_subscription_date_c' => $lead_data['date'] ?? null,
            'first_name' => $lead_data['first_name'] ?? null,
            'last_name' => $lead_data['last_name'] ?? null,
            'email1' => $lead_data['email'],
            'soi_c' => true,
            'doi_c' => false,
            'phone_c' => $lead_data['phone'] ?? null,
            'vertical_c' => $lead_data['vertical'] ?? null,
            'aff_id_c' => $lead_data['aff_id'] ?? null,
            'sign_up_page_url_c' => $lead_data['sing_up_page_url'] ?? null,
            'source_c' => $lead_data['source'] ?? null,
            'ip_address_c' => $lead_data['ip_address'] ?? null,
            'geo_c' => $lead_data['geo'] ?? null,
            'country_c' => $lead_data['country'] ?? null,
            'region_c' => $lead_data['region'] ?? null,
            'province_c' => $lead_data['province'] ?? null,
            'state_c' => $lead_data['state'] ?? null,
            'device_c' => $lead_data['device'] ?? null,
            'os_c' => $lead_data['os'] ?? null,
            'browser_c' => $lead_data['browser'] ?? null,
            'affsub_c' => $lead_data['aff_sub'] ?? null,
            'affsub2_c' => $lead_data['aff_sub2'] ?? null,
            'affsub3_c' => $lead_data['aff_sub3'] ?? null,
            'affsub4_c' => $lead_data['aff_sub4'] ?? null,
            'affsub5_c' => $lead_data['aff_sub5'] ?? null,
            
            'transaction_id' => $lead_data['transaction_id'] ?? null,
            'aff_click_id' => $lead_data['aff_click_id'] ?? null,
            'affiliate_id' => $lead_data['affiliate_id'] ?? null,
            'offer_id' => $lead_data['offer_id'] ?? null,
            'sub_id_1_c' => $lead_data['subid1'] ?? null,
        ]
    ],
    "traits" => [
        "email" => $lead_data['email']
    ]
];

            
            $this->curlRequest("POST", 'https://api.segment.io/v1/identify', $segment_request, []);
            echo $lead_id;
            
            http_response_code(201);
        } else {
            http_response_code(405);
            header("Allow: POST");
        }
    }


    /**
     * @throws Exception
     */
    private function isValidToken($token): bool
    {
        $decoded = explode("; ", base64_decode($token));
        $secretFromToken = ltrim($decoded[0], "{");
        $isSecretValid = $secretFromToken == $this->secret;
        $date_time_from_token_raw = $decoded[1];
        $date_time_from_token = new DateTimeImmutable($date_time_from_token_raw);
        $ttl_for_token = (new DateTimeImmutable())->sub(new DateInterval('PT' . 3600 . 'S'));
        $isDateStampValid = $date_time_from_token > $ttl_for_token;
        return $isSecretValid;
    }

    private function curlRequest($method, $url, $post_data = [], $headers = [])
    {
        echo 'request to service';
        $curl = curl_init();
        if (count($headers) != 0) {
            $contentHeaders = array(
        	"Authorization: Basic QklpcWJkYmJkTkl3dkRnTkNqOGRQVXpmdjA0Y3E5bnk=",
                "Content-Type: application/json",
                "cache-control: no-cache"
            );
            $headers = array_merge($headers, $contentHeaders);
        } else {
            $headers = array(
        	"Authorization: Basic QklpcWJkYmJkTkl3dkRnTkNqOGRQVXpmdjA0Y3E5bnk=",
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


        file_put_contents($this->log_path, "Segment -------------\r\n"
            .date('Y-m-d h:i:s')."\r\n".$url."\r\n".json_encode($post_data, JSON_PRETTY_PRINT)
            ."\r\n".json_encode($body, JSON_PRETTY_PRINT)
            ."\r\n".json_encode($response, JSON_PRETTY_PRINT)
            ."\r\n---------------------\r\n".PHP_EOL, FILE_APPEND);

        return ($body ==null)? []: json_decode($body, true);
    }
}









