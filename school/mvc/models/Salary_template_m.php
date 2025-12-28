<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Salary_template_m extends MY_Model 
{

    protected $_table_name          = 'salary_template';
    protected $_primary_key         = 'salary_templateID';
    protected $_primary_filter      = 'intval';
    protected $_order_by            = "salary_templateID asc";

    public function __construct() 
    {
        parent::__construct();
    }

    public function get_salary_template($array=null, $signal=false) 
    {
        return parent::get($array, $signal);

    }

    public function get_single_salary_template($array) 
    {
        return parent::get_single($array);
    }

    public function get_order_by_salary_template($array=null) 
    {
        return parent::get_order_by($array);
    }

    public function insert_salary_template($array) 
    {
        return parent::insert($array);
    }

    public function update_salary_template($data, $id = null) 
    {
        parent::update($data, $id);
        return $id;
    }

    public function delete_salary_template($id)
    {
        parent::delete($id);
    }
}
