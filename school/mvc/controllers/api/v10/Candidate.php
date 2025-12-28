<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') || exit('No direct script access allowed');

class Candidate extends Api_Controller
{

    public $load;
    public $retdata;
    public function __construct()
    {
        parent::__construct();
        $this->load->model("candidate_m");
        $this->load->model("classes_m");
        $this->load->model("section_m");
        $this->load->model('studentrelation_m');
        $this->load->model('student_m');
        $this->load->model('sponsor_m');
        $this->load->model('subject_m');
        $this->load->model('sponsorship_m');
        $this->load->model('studentgroup_m');
        $this->load->model('transaction_m');
    }

    public function index_get()
    {

        $this->retdata['classes'] = pluck($this->classes_m->general_get_classes(), 'classes', 'classesID');
        $this->retdata['sponsors'] = pluck($this->sponsor_m->get_sponsor(), 'name', 'sponsorID');
        $this->retdata['candidates'] = $this->candidate_m->get_candidate_with_student_sponsorship();
        $this->response([
            'status' => true,
            'message' => 'Success',
            'data' => $this->retdata
        ], REST_Controller::HTTP_OK);
    }

    public function view_get($id = 0)
    {
        if ((int)$id !== 0) {
            $this->retdata['candidate'] = $this->candidate_m->get_single_candidate(['candidateID' => $id]);

            if (customCompute($this->retdata['candidate'])) {
                $this->retdata['photo'] = pluck($this->student_m->general_get_student(), 'photo', 'studentID');
                $this->retdata['profile'] = $this->studentrelation_m->get_single_studentrelation(['srstudentID' => $this->retdata['candidate']->studentID]);
                $this->retdata['groups'] = pluck($this->studentgroup_m->get_studentgroup(), 'group', 'studentgroupID');
                $this->retdata['subjects'] = pluck($this->subject_m->general_get_subject(), 'subject', 'subjectID');
                $this->retdata['usertypes'] = pluck($this->usertype_m->get_usertype(), 'usertype', 'usertypeID');
                $this->retdata['classes'] = $this->classes_m->get_single_classes(['classesID' => $this->retdata['profile']->srclassesID]);
                $this->retdata['section'] = $this->section_m->get_single_section(['sectionID' => $this->retdata['profile']->srsectionID]);
                $this->retdata['sponsors'] = pluck($this->sponsor_m->get_sponsor(), 'name', 'sponsorID');

                $this->response([
                    'status' => true,
                    'message' => 'Success',
                    'data' => $this->retdata
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
