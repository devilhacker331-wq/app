<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Manage_salary_m extends MY_Model 
{

    protected $_table_name          = 'manage_salary';
    protected $_primary_key         = 'manage_salaryID';
    protected $_primary_filter      = 'intval';
    protected $_order_by            = "manage_salaryID asc";

    public function __construct() 
    {
        parent::__construct();
    }

    public function get_manage_salary($array=null, $signal=FALSE) 
    {
        return parent::get($array, $signal);
    }

    public function get_single_manage_salary($array) 
    {
        return parent::get_single($array);
    }

    public function get_order_by_manage_salary($array=null) 
    {
        return parent::get_order_by($array);
    }

    public function insert_manage_salary($array) 
    {
        return parent::insert($array);
    }

    public function update_manage_salary($data, $id = null) 
    {
        parent::update($data, $id);
        return $id;
    }

    public function delete_manage_salary($id)
    {
        parent::delete($id);
    }

}
