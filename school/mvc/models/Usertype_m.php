<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class usertype_m extends MY_Model {

	protected $_table_name = 'usertype';
	protected $_primary_key = 'usertypeID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "usertypeID desc";

	function __construct() {
		parent::__construct();
	}

	function get_usertype($array=NULL, $signal=FALSE) {
		$query = parent::get($array, $signal);
		return $query;
	}

	function get_order_by_usertype($array=NULL) {
		$query = parent::get_order_by($array);
		return $query;
	}

	function get_single_usertype($array=NULL) {
		$query = parent::get_single($array);
		return $query;
	}

	function get_usertype_for_overtime()
	{
		$this->db->select('*');
		$this->db->from('usertype');
		$this->db->where('usertypeID!=', 3);
		$this->db->where('usertypeID!=', 4);
		$query = $this->db->get();
		return $query->result();
	}

	function insert_usertype($array) {
		$error = parent::insert($array);
		return TRUE;
	}

	function update_usertype($data, $id = NULL) {
		parent::update($data, $id);
		return $id;
	}

	public function delete_usertype($id){
		parent::delete($id);
	}

	public function get_usertype_by_permission($featureName, $systemadminID = 0) {
		$this->db->select('*');

		$this->db->from('permission_relationships');
		$this->db->join('permissions', 'permissions.permissionID = permission_relationships.permission_id', 'LEFT');
		$this->db->join('usertype', 'usertype.usertypeID = permission_relationships.usertype_id', 'LEFT');
		$this->db->where(array('permissions.name' => $featureName));
		$query = $this->db->get();

		if($systemadminID == 0) {
			return $query->result();
		} else {
			$datas = $query->result();
			$retArray = [];
			if(customCompute($datas)) {
				$this->db->where($this->_primary_key, 1);
				$systemadminQuery = $this->db->get($this->_table_name);
				$systemadmin = $systemadminQuery->row();
				if(customCompute($systemadmin)) {
					$retArray[] = (object) array(
						'usertypeID' => $systemadmin->usertypeID,
						'usertype' => $systemadmin->usertype,
					);
				}	

				foreach ($datas as $dataKey => $data) {
					$retArray[] = (object)$data;
				}
				return $retArray;
			} else {
				$this->db->where($this->_primary_key, 1);
				$systemadminQuery = $this->db->get($this->_table_name);
				$systemadmin = $systemadminQuery->row();
				
				if(customCompute($systemadmin)) {
					$retArray[] = (object) array(
						'usertypeID' => $systemadmin->usertypeID,
						'usertype' => $systemadmin->usertype,
					);
				}
				return $retArray;				
			}
		}
	}

	public function get_usertype_by_permission_for_api($featureName, $systemadminID = 0) {
		$this->db->select('usertype.*');

		$this->db->from('permission_relationships');
		$this->db->join('permissions', 'permissions.permissionID = permission_relationships.permission_id', 'LEFT');
		$this->db->join('usertype', 'usertype.usertypeID = permission_relationships.usertype_id', 'LEFT');
		$this->db->where(array('permissions.name' => $featureName));
		$query = $this->db->get();

		if($systemadminID == 0) {
			return $query->result();
		} else {
			$datas = $query->result();
			$retArray = [];
			if(customCompute($datas)) {
				foreach ($datas as $dataKey => $data) {
					$retArray[] = (object)$data;
				}
				return $retArray;
			} else {
				$this->db->where($this->_primary_key, 1);
				$systemadminQuery = $this->db->get($this->_table_name);
				$systemadmin = $systemadminQuery->row();
				
				if(customCompute($systemadmin)) {
					$retArray[] = (object) array(
						'usertypeID' => $systemadmin->usertypeID,
						'usertype' => $systemadmin->usertype,
					);
				}
				return $retArray;				
			}
		}
	}
}