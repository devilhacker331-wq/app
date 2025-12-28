<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') || exit('No direct script access allowed');

class Overtime extends Api_Controller
{

    public $retdata;
    public function __construct() 
    {
        parent::__construct();
        $this->load->model('usertype_m');
        $this->load->model('user_m');
        $this->load->model('overtime_m');
        $this->load->model('manage_salary_m');
        $this->load->model('salary_template_m');
    }

    public function index_get() 
    {
        $this->retdata['overtimes'] = $this->overtime_m->get_overtime();
        $this->retdata['roles']     = pluck($this->usertype_m->get_usertype(), 'usertype', 'usertypeID');
        $this->retdata['allUsers']  = getAllUserObjectWithoutStudent();
            $this->response([
                'status'    => true,
                'message'   => 'Success',
                'data'      => $this->retdata
            ], REST_Controller::HTTP_OK);
    }

}
