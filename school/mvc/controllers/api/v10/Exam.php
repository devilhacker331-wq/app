<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') || exit('No direct script access allowed');

class Exam extends Api_Controller 
{
    public $retdata;
    public function __construct() 
    {
        parent::__construct();
        $this->load->model('exam_m');
    }

    public function index_get() 
    {
        $this->retdata['exams'] = $this->exam_m->get_order_by_exam();

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);       
    }
}
