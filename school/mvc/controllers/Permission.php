<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Permission extends Admin_Controller {
public $load;
 public $session;
 public $lang;
 public $data;
 public $uri;
 public $usertype_m;
 public $input;
 public $permission_m;
 /*
| -----------------------------------------------------
| PRODUCT NAME: 	INILABS SCHOOL MANAGEMENT SYSTEM
| -----------------------------------------------------
| AUTHOR:			INILABS TEAM
| -----------------------------------------------------
| EMAIL:			info@inilabs.net
| -----------------------------------------------------
| COPYRIGHT:		RESERVED BY INILABS IT
| -----------------------------------------------------
| WEBSITE:			http://inilabs.net
| -----------------------------------------------------
*/
	function __construct() {
		parent::__construct();
		$this->load->model("permission_m");
		$this->load->model("usertype_m");
		$language = $this->session->userdata('lang');
		$this->lang->load('permission', $language);	
	}

	public function index() {
		$this->data['headerassets'] = array(
			'css' => array(
				'assets/select2/css/select2.css',
				'assets/select2/css/select2-bootstrap.css'
			),
			'js' => array(
				'assets/select2/select2.js'
			)
		);

 		$id = htmlentities((string) escapeString($this->uri->segment(3)));
		if((int)$id !== 0) {
			$usertype = $this->usertype_m->get_usertype($id);
				if(customCompute($usertype)) {

				$this->data['set'] = $id;
				$this->data['usertypes'] = $this->usertype_m->get_usertype();
				$this->data['permissions'] = $this->permission_m->get_modules_with_permission($id);
				if(empty($this->data['permissions'])) {
					$this->data['permissions'] = NULL;
				}
				$this->data["subview"] = "permission/index";
				$this->load->view('_layout_main', $this->data);
			} else {
				$this->data["subview"] = "error";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			$this->data['usertypes'] = $this->usertype_m->get_usertype();
			$this->data["subview"] = "permission/index";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function permission_list() {
		$usertypeID = $this->input->post('usertypeID');
		if((int)$usertypeID !== 0) {
			$string = base_url("permission/index/$usertypeID");
			echo $string;
		} else {
			redirect(base_url("permission/index"));
		}
	}

	public function save() {
		$this->session->userdata('usertype');
		$usertypeID = $this->uri->segment(3);
		if ((int)$usertypeID !== 0) {
			$usertype = $this->usertype_m->get_usertype($usertypeID);
			if(customCompute($usertype)) {
				if ($this->permission_m->delete_all_permission($usertypeID)) {
					foreach ($_POST as $key => $value) {
						$array = array();
						$array['permission_id'] = $value;
						$array['usertype_id'] = $usertypeID;
						$this->permission_m->insert_relation($array);
					}
					redirect(base_url('permission/index/'.$usertypeID),'refresh');
				} else {
					redirect(base_url('permission/index/'.$usertypeID),'refresh');
				}
			} else {
				$this->data["subview"] = "error";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			redirect(base_url('permission/index/'.$usertypeID),'refresh');
		}
	}
}
