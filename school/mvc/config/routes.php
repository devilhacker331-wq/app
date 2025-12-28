<?php
    defined('BASEPATH') OR exit('No direct script access allowed');

    spl_autoload_register(function($className) {
        if ( strpos($className, 'CI_') !== 0 ) {
            $file = APPPATH . 'libraries/' . $className . '.php';
            if ( file_exists($file) && is_file($file) ) {
                @include_once( $file );
            }
        }
    });

    $route['version']            = 'app/version';
    $route['default_controller'] = 'frontend/index';