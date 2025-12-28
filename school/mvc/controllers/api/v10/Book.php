<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') || exit('No direct script access allowed');

class Book extends Api_Controller 
{
    public $load;
    public $retdata;
    public function __construct() 
    {
        parent::__construct();
        $this->load->model('book_m');
        $this->load->model("lmember_m");
        $this->load->model("issue_m");
    }

    public function index_get() 
    {
        $this->retdata['books'] = $this->book_m->get_order_by_book();
        
        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }

    public function issue_get() {
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $studentID = $this->session->userdata("loginuserID");
        $student = $this->studentrelation_m->get_single_student(array("srstudentID" => $studentID, 'srschoolyearID' => $schoolyearID));
        if(customCompute($student) && $student->library === '1') {
            $lmember = $this->lmember_m->get_single_lmember(array('studentID' => $student->studentID));
            $lID = $lmember->lID;
            $this->retdata['libraryID'] = $lID;

            $this->retdata['issues'] = $this->issue_m->get_issue_with_books(array("lID" => $lID));

            $this->response([
                'status'    => true,
                'message'   => 'Success',
                'data'      => $this->retdata
            ], REST_Controller::HTTP_OK);

        } else {
            $this->retdata['issues'] = [];
            $this->response([
               'status'    => true,
               'message'   => 'Success',
               'data'      => $this->retdata
           ], REST_Controller::HTTP_OK); 
        }
    }

}
