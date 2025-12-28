<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') || exit('No direct script access allowed');

class Subject extends Api_Controller
{
    public $load;
    public $session;
    public $data;
    public $retdata;
    public function __construct()
    {
        parent::__construct();
        $this->load->model('teacher_m');
        $this->load->model('subject_m');
        $this->load->model('classes_m');
        $this->load->model("student_m");
        $this->load->model('subjectteacher_m');
    }

    public function index_get($id = null)
    {
        if ($this->session->userdata('usertypeID') == 3) {
            $id = $this->data['myclass'];
        }

    
        if ((int)$id !== 0) {
            $this->retdata['classesID'] = $id;
            
            $teachers = pluck($this->teacher_m->general_get_teacher(), 'obj');

            $this->retdata['teachers'] = [];

            foreach ($teachers as $teacher) {
                $this->retdata['teachers'][$teacher->teacherID] = [
                    'name' => $teacher->name,
                    'photo' => $teacher->photo,
                ];
            }

            $this->retdata['classes'] = $this->student_m->get_classes();
            $fetchClass = pluck($this->retdata['classes'], 'classesID', 'classesID');
            if (isset($fetchClass[$id])) {
                $this->retdata['subjects'] = $this->subject_m->general_get_order_by_subject(array('classesID' => $id));
                $this->retdata['subjectteachers'] = pluck_multi_array($this->subjectteacher_m->get_order_by_subjectteacher(array('classesID' => $id)), 'teacherID', 'subjectID');
                $subjectteachers = $this->retdata['subjectteachers'];
                $subjects = $this->retdata['subjects'];
                $teachers = $this->retdata['teachers'];
                if (customCompute($subjects)) {
                    $i = 1;
                    foreach ($subjects as $subject) {
                        if (isset($subjectteachers[$subject->subjectID])) {
                            foreach ($subjectteachers[$subject->subjectID] as $teacherID) {
                                if (isset($teachers[$teacherID])) {
                                    $subjects[$i - 1]->teacher_name = $teachers[$teacherID];
                                }
                            }
                        }
                        $i++;
                    }
                }
            } else {
                $this->data['set'] = 0;
                $this->data['subjects'] = [];
                $this->data['subjectteachers'] = [];
                $this->data['classes'] = $this->student_m->get_classes();
            }
        } else {
            $this->retdata['classesID'] = 0;
            $this->retdata['subjects'] = [];
            $this->retdata['subjectteachers'] = [];
            $this->retdata['classes'] = $this->classes_m->get_classes();
        }

        $this->response([
            'status'    => true,
            'message'   => 'Success',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }
}
