<?php

class TokenController
{
    private $secret = 'n2Bn29QVH1';

    public function __construct()
    {

    }

    public function processTokenRequest(string $method)
    {
        if ($method == 'GET') {
            $date = date(DATE_ATOM);
            $token_string = "{{$this->secret}; {$date}; {$this->generateRandomString(8)}";
            $encrypted = base64_encode($token_string);
            http_response_code(201);
            $resp = json_encode(array('token'=>$encrypted));
            echo $resp;
        } else {
            http_response_code(405);
            header("Allow: GET");
        }
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}









