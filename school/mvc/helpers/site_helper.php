<?php
function getIpAddress()
{
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

    if ( filter_var($client, FILTER_VALIDATE_IP) ) {
        $ip = $client;
    } elseif ( filter_var($forward, FILTER_VALIDATE_IP) ) {
        $ip = $forward;
    } else {
        $ip = ( $remote == "::1" ? "127.0.0.1" : $remote );
    }

    return $ip;
}

if ( !function_exists('customCompute') ) {
    function customCompute( $array )
    {
        if ( is_object($array) ) {
            if ( get_object_vars($array) !== [] ) {
                return count(get_object_vars($array));
            }
            return 0;
        } elseif ( is_array($array) ) {
            if ( $array !== [] ) {
                return count($array);
            }
            return 0;
        } elseif ( is_string($array) ) {
            return 1;
        } elseif ( is_null($array) ) {
            return 0;
        } elseif ( is_int($array) ) {
            return (int) $array;
        } elseif ( is_float($array) ) {
            return (float) $array;
        } else {
            return count($array);
        }
    }
}

?>