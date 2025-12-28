<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') || exit('No direct script access allowed');

class Transport extends Api_Controller 
{

    public $load;
    public $retdata;
    public function __construct() 
    {
        parent::__construct();
        $this->load->model('transport_m');
    }

    public function index_get() 
    {
        $this->retdata['transports'] = $this->transport_m->get_order_by_transport();
        
        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
