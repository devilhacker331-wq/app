<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Addons_m extends MY_Model {

	protected $_table_name = 'addons';
	protected $_primary_key = 'addonsID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "addonsID desc";

	function __construct() {
		parent::__construct();
	}

	function get_addons($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	function get_order_by_addons($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	function get_single_addons($array=NULL) {
		$query = parent::get_single($array);
		return $query;
	}

	function insert_addons($array) {
		$error = parent::insert($array);
		return TRUE;
	}

	function update_addons($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_addons($id){
		parent::delete($id);
	}
}
