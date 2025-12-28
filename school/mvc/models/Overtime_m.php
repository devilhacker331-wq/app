<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Overtime_m extends MY_Model
{

    protected $_table_name     = 'overtime';
    protected $_primary_key    = 'id';
    protected $_primary_filter = 'intval';
    protected $_order_by       = "id desc";

    public function __construct()
    {
        parent::__construct();
    }

    public function get_overtime($array = null, $signal = false)
    {
        return parent::get($array, $signal);
    }

    public function get_order_by_overtime($array = null)
    {
        return parent::get_order_by($array);
    }

    public function get_single_overtime($array = null)
    {
        return parent::get_single($array);
    }

    public function insert_overtime($array)
    {
        return parent::insert($array);
    }

    public function update_overtime($data, $id = null)
    {
        parent::update($data, $id);
        return $id;
    }

    public function delete_overtime($id)
    {
        return parent::delete($id);
    }

    public function get_overtime_for_report($queryArray)
    {
        $this->db->select('*');
        $this->db->from($this->_table_name);

        if (isset($queryArray['usertypeID']) && $queryArray['usertypeID']) {
            $this->db->where('usertypeID', $queryArray['usertypeID']);

            if (isset($queryArray['userID']) && $queryArray['userID']) {
                $this->db->where('userID', $queryArray['userID']);
            }
        }

        if ((isset($queryArray['fromdate']) && $queryArray['fromdate'] != '') && (isset($queryArray['todate']) && $queryArray['todate'] != '')) {
            $fromdate = date('Y-m-d', strtotime($queryArray['fromdate'])) . " 00:00:00";
            $todate   = date('Y-m-d', strtotime($queryArray['todate'])) . " 23:59:59";

            $this->db->where('date >=', $fromdate);
            $this->db->where('date <=', $todate);
        }
        $query = $this->db->get();
        return $query->result();
    }
}
