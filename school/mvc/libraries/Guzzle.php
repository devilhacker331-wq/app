<?php
defined('BASEPATH') || exit('No direct script access allowed');

require_once(FCPATH . 'vendor/autoload.php');

use GuzzleHttp\Client;

class Guzzle
{

    public function request($data, $url = null)
    {
        $http = new Client();

        $parameters = [
            'headers' => [
                'User-Agent'    => $_SERVER['HTTP_USER_AGENT']
            ],
            'form_params' => $data
        ];
         
        return $http->request('POST', $url, $parameters);



    }
}
