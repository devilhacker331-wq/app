<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') || exit('No direct script access allowed');

class Sponsor extends Api_Controller 
{

    public $load;
    public $retdata;
    public function __construct() 
    {
        parent::__construct();
        $this->load->model("sponsor_m");
        $this->load->model("student_m");
        $this->retdata['titles'] = array_merge(['0' => $this->lang->line('sponsor_select_title')], $this->sponsor_m->titles);

    }

    public function index_get() 
    {
        $this->retdata['allcountry']           = $this->getAllCountry();
        $this->retdata['sponsors'] = $this->sponsor_m->get_order_by_sponsor();
            $this->response([
                'status'    => true,
                'message'   => 'Success',
                'data'      => $this->retdata
            ], REST_Controller::HTTP_OK);
    }

    public function view_get($id = 0) 
    {
    
        if((int)$id !== 0) {
            $this->retdata['sponsor'] = $this->sponsor_m->get_single_sponsor(array('sponsorID' => $id));
            $this->retdata['allcountry']           = $this->getAllCountry();

            if(customCompute($this->retdata['sponsor'])) {
                $this->response([
                    'status'    => true,
                    'message'   => 'Success',
                    'data'      => $this->retdata
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Error 404',
                    'data' => []
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            $this->response([
                'status' => false,
                'message' => 'Error 404',
                'data' => []
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
}
