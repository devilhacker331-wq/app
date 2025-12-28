<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Leavecategory extends Admin_Controller {
    public $load;
    public $session;
    public $lang;
    public $data;
    public $uri;
    public $leavecategory_m;
    public $form_validation;
    public $input;
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
        $this->load->model("leavecategory_m");

        $language = $this->session->userdata('lang');
        $this->lang->load('leavecategory', $language);
    }

    public function index() {
        $this->data['leave_categories'] = $this->leavecategory_m->get_leavecategory();
        $this->data["subview"] = "leavecategory/index";
        $this->load->view('_layout_main', $this->data);
    }

    protected function rules() {
        return array(
            array(
                'field' => 'leavecategory',
                'label' => $this->lang->line("leavecategory_category"),
                'rules' => 'trim|required|xss_clean|max_length[255]|callback_unique_leavecategory'
            )
        );
    }

    public function unique_leavecategory($leavecategory) {
        $leavecategoryID = htmlentities((string) escapeString($this->uri->segment(3)));
        if((int)$leavecategoryID !== 0) {
            $leavecategory = $this->leavecategory_m->get_order_by_leavecategory(array('leavecategory'=>$leavecategory,'leavecategoryID !='=>$leavecategoryID));
        } else {
            $leavecategory = $this->leavecategory_m->get_order_by_leavecategory(array('leavecategory'=>$leavecategory));
        }
        if(customCompute($leavecategory)) {
            $this->form_validation->set_message('unique_leavecategory','The %s field value already exits.');
            return FALSE;
        }
        return TRUE;
    }

    public function add() {
        if($_POST !== []) {
            $rules = $this->rules();
            $this->form_validation->set_rules($rules);
            if ($this->form_validation->run() == FALSE) {
                $this->data["subview"] = "leavecategory/add";
                $this->load->view('_layout_main', $this->data);
            } else {
                $array = array(
                    "leavecategory" => $this->input->post("leavecategory"),
                    "leavegender"   => 1,
                    "create_date"   => date("Y-m-d H:i:s"),
                    "modify_date"   => date("Y-m-d H:i:s"),
                    "create_userID" => $this->session->userdata('loginuserID'),
                    "create_usertypeID" => $this->session->userdata('usertypeID')
                );

                $this->leavecategory_m->insert_leavecategory($array);
                $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                redirect(base_url("leavecategory/index"));
            }
        } else {
            $this->data["subview"] = "leavecategory/add";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function edit() {
        $leavecategoryID = htmlentities((string) escapeString($this->uri->segment(3)));
        if((int)$leavecategoryID !== 0) {
            $this->data['leavecategory'] = $this->leavecategory_m->get_single_leavecategory(array('leavecategoryID' => $leavecategoryID));
            if(customCompute($this->data['leavecategory'])) {
                if($_POST !== []) {
                    $rules = $this->rules();
                    $this->form_validation->set_rules($rules);
                    if ($this->form_validation->run() == FALSE) {
                        $this->data["subview"] = "leavecategory/edit";
                        $this->load->view('_layout_main', $this->data);
                    } else {
                        $array = array(
                            "leavecategory" => $this->input->post("leavecategory"),
                            "leavegender"   => 1,
                            "modify_date"   => date("Y-m-d H:i:s")
                        );
                        $this->leavecategory_m->update_leavecategory($array, $leavecategoryID);
                        $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                        redirect(base_url("leavecategory/index"));
                    }
                } else {
                    $this->data["subview"] = "leavecategory/edit";
                    $this->load->view('_layout_main', $this->data);
                }
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function delete() {
        $leavecategoryID = htmlentities((string) escapeString($this->uri->segment(3)));
        if((int)$leavecategoryID !== 0) {
            $this->data['leavecategory'] = $this->leavecategory_m->get_single_leavecategory(array('leavecategoryID' => $leavecategoryID));
            if(customCompute($this->data['leavecategory'])) {
                $this->leavecategory_m->delete_leavecategory($leavecategoryID);
                $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                redirect(base_url("leavecategory/index"));
            } else {
                redirect(base_url("leavecategory/index"));
            }
        } else {
            redirect(base_url("leavecategory/index"));
        }
    }

}
