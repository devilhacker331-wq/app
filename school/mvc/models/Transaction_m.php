<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Transaction_m extends MY_Model {

    protected $_table_name = 'transaction';
    protected $_primary_key = 'transactionID';
    protected $_primary_filter = 'intval';
    protected $_order_by = "transactionID asc";

    function __construct() {
        parent::__construct();
    }

    function get_transaction($array=NULL, $signal=FALSE) {
        $query = parent::get($array, $signal);
        return $query;
    }

    function get_single_transaction($array) {
        $query = parent::get_single($array);
        return $query;
    }

    function get_order_by_transaction($array=NULL) {
        $query = parent::get_order_by($array);
        return $query;
    }

    function insert_transaction($array) {
        $id = parent::insert($array);
        return $id;
    }

    function update_transaction($data, $id = NULL) {
        parent::update($data, $id);
        return $id;
    }

    public function delete_transaction($id){
        parent::delete($id);
    }
}
