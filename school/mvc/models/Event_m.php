<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Event_m extends MY_Model
{

	protected $_table_name = 'event';
	protected $_primary_key = 'eventID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "fdate desc,ftime asc";

	function __construct()
	{
		parent::__construct();
	}

	function get_event($array = NULL, $signal = FALSE)
	{
		$query = parent::get($array, $signal);
		return $query;
	}

	function get_order_by_event($array = NULL)
	{
		$query = parent::get_order_by($array);
		return $query;
	}

	function get_single_event($array)
	{
		$query = parent::get_single($array);
		return $query;
	}

	function insert_event($array)
	{
		$error = parent::insert($array);
		return TRUE;
	}

	function update_event($data, $id = NULL)
	{
		parent::update($data, $id);
		return $id;
	}

	public function delete_event($id)
	{
		parent::delete($id);
	}

	public function get_events_with_limit_offset($offset,$limit = 6,$array = null)
	{
		if($array){
			$this->db->where($array);
		}
		$this->db->limit($limit, $offset);
		$this->db->order_by('eventID', 'DESC');
		$query = $this->db->get($this->_table_name);
		return $query->result();
	}

	public function get_latest_events($limit = 6,$array = null)
	{
		if($array){
			$this->db->where($array);
		}
		$query = $this->db->get($this->_table_name, $limit);
		$this->db->order_by('eventID','desc');
		return $query->result();
	}
}

/* End of file holiday_m.php */
/* Location: .//D/xampp/htdocs/school/mvc/models/holiday_m.php */
