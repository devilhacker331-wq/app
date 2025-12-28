<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') || exit('No direct script access allowed');

class Sponsorship extends Api_Controller 
{

    public $load;
    public $retdata;
    public function __construct() 
    {
        parent::__construct();
        $this->load->model("sponsor_m");
        $this->load->model("sponsorship_m");
        $this->load->model("section_m");
        $this->load->model('studentrelation_m');
        $this->load->model('student_m');
        $this->load->model('candidate_m');
        $this->load->model('transaction_m');
    }

    public function index_get() 
    {
       
        $this->retdata['sponsorships'] = $this->sponsorship_m->get_sponsorship_with_student();
            $this->response([
                'status'    => true,
                'message'   => 'Success',
                'data'      => $this->retdata
            ], REST_Controller::HTTP_OK);
    }

}
