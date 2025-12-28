<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Updatechecker
{

    public function __construct()
    {
        $CI = &get_instance();
        $CI->load->config('support');
    }

    public function verifyValidUser($array = [], $arrayMerge = true, $url = null)
    {
        $this->_dataMaker($array, $arrayMerge);
        return $this->_apiCurl($array, $url);
    }

    private function _dataMaker(&$array, $arrayMerge)
    {
        $data = [
            'license_code' => '',
            'email'        => '',
            'ip'           => $this->getUserIP(),
            'domain'       => $_SERVER['HTTP_HOST'],
            'purpose'      => 'update',
            'product_name' => config_item('product_name'),
            'version'      => config_item('ini_version'),
            'product_id'   => config_item('itemId'),
        ];


        if ($arrayMerge) {
            $CI = &get_instance();
            $CI->load->model('setting_m');
            $setting      = $CI->setting_m->get_setting();
            $license_code = $setting->license_code ?? $this->_purchaseFileRead();
            if (customCompute($setting)) {
                $data['license_code'] = $license_code;
                $data['email']        = $setting->email;
            }
        }
        $array = array_merge($data, $array);
    }

    private function _purchaseFileRead()
    {
        $file = APPPATH . 'config/purchase.php';
        @chmod($file, FILE_WRITE_MODE);
        $purchase = file_get_contents($file);
        $purchase = json_decode($purchase);
        if (is_array($purchase)) {
            $license_code = trim((string) $purchase[0]);
        }
        return $license_code;
    }

    public function getUserIP()
    {
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = ($remote == "::1" ? "127.0.0.1" : $remote);
        }

        return $ip;
    }

    private function _apiCurl($data, $url = null)
    {
        if (is_null($url)) {
            $url = $this->_activeServer();
        }

        if (!$url) {
            return (object)[
                'status'  => false,
                'message' => 'Server Down',
                'for'     => 'purchasecode',
            ];
        }
        try {
            $guzzle      = new Guzzle();
            $response    = $guzzle->request($data, $url);
            $header      = explode(';', (string) $response->getHeader('Content-Type')[0]);
            $contentType = $header[0];
            if ($contentType == 'application/json') {
                $contents = $response->getBody()->getContents();
                $data     = json_decode((string) $contents);
                if (json_last_error() == JSON_ERROR_NONE) {
                    return $data;
                } else {
                    return (object)[
                        'status'  => false,
                        'message' => 'JSON decoding failed.',
                        'for'     => 'purchasecode',
                    ];
                }
            } else {
                return (object)[
                    'status'  => false,
                    'message' => 'Application type not json.',
                    'for'     => 'purchasecode',
                ];
            }
        } catch (Exception $exception) {
            return (object)[
                'status'  => false,
                'message' => $exception->getMessage(),
                'for'     => 'purchasecode'
            ];
        }
    }

    private function _activeServer()
    {
        $domain = config_item('licenseCodeCheckerUrl') . '/api/check-product-license';
        $url    = parse_url($domain);
        if ($this->_checkInternetConnection($url['host'])) {
            return $domain;
        }

        return false;
    }

    private function _checkInternetConnection($sCheckHost = 'www.google.com')
    {
        return (bool)@fsockopen($sCheckHost, 80, $iErrno, $sErrStr, 30);
    }

    public function getBrowser()
    {
        $u_agent  = $_SERVER['HTTP_USER_AGENT'];
        $bname    = 'Unknown';
        $platform = 'Unknown';

        if (preg_match('/linux/i', (string) $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', (string) $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', (string) $u_agent)) {
            $platform = 'windows';
        }

        if (preg_match('/MSIE/i', (string) $u_agent) && !preg_match('/Opera/i', (string) $u_agent)) {
            $bname = 'Internet Explorer';
            $ub    = "MSIE";
        } elseif (preg_match('/Firefox/i', (string) $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub    = "Firefox";
        } elseif (preg_match('/Chrome/i', (string) $u_agent)) {
            $bname = 'Google Chrome';
            $ub    = "Chrome";
        } elseif (preg_match('/Safari/i', (string) $u_agent)) {
            $bname = 'Apple Safari';
            $ub    = "Safari";
        } elseif (preg_match('/Opera/i', (string) $u_agent)) {
            $bname = 'Opera';
            $ub    = "Opera";
        } elseif (preg_match('/Netscape/i', (string) $u_agent)) {
            $bname = 'Netscape';
            $ub    = "Netscape";
        }

        $known   = ['Version', $ub, 'other'];
        $pattern = '#(?<browser>' . implode('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, (string) $u_agent, $matches)) {
            // we have no matching number just continue
        }

        $i = customCompute($matches['browser']);
        if ($i != 1) {
            $version = strripos((string) $u_agent, "Version") < strripos((string) $u_agent, $ub) ? $matches['version'][0] : $matches['version'][1];
        } else {
            $version = $matches['version'][0];
        }

        if ($version == null || $version == "") {
            $version = "?";
        }

        return (object)[
            'userAgent' => $u_agent,
            'name'      => $bname,
            'version'   => $version,
            'platform'  => $platform,
            'pattern'   => $pattern
        ];
    }
}
