<?php ! defined('BASEPATH') && exit('No direct script access allowed');
require_once(FCPATH . 'vendor/autoload.php');

use PHPQRCode\QRcode;
class Qrcodegenerator {
	public function generate_qrcode($text, $filename, $folder) {
		$PNG_TEMP_DIR = FCPATH.'uploads/'.$folder.'/';

		if (!file_exists($PNG_TEMP_DIR)) {
        	mkdir($PNG_TEMP_DIR);
        }

	    $filename = $PNG_TEMP_DIR.$filename.'.png';
		QRcode::png($text, $filename,"H",4);
	}
}