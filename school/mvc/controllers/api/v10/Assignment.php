<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') || exit('No direct script access allowed');

class Assignment extends Api_Controller
{
    public $retdata;
    public $classes_m;
    public $subject_m;
    public $assignment_m;
    public $upload_data;
    public $upload;
    public $assignmentanswer_m;
    public $retdata2;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('section_m');
        $this->load->model('classes_m');
        $this->load->model('assignment_m');
        $this->load->model('assignmentanswer_m');
    }

    public function index_get($id = null)
    {
        if ($this->session->userdata('usertypeID') == 3) {
            $id = $this->data['myclass'];
        }

        $this->retdata['classes'] = $this->classes_m->get_classes();
        if ((int) $id !== 0) {
            $fetchClasses = pluck($this->retdata['classes'], 'classesID', 'classesID');
            if (isset($fetchClasses[$id])) {
                $this->retdata['classesID'] = $id;
                $this->retdata['sections'] = pluck($this->section_m->general_get_order_by_section(array('classesID' => $id)), 'section', 'sectionID');
                $schoolyearID = $this->session->userdata('defaultschoolyearID');
                $this->retdata['assignments'] = $this->assignment_m->join_get_assignment($id, $schoolyearID);
            } else {
                $this->retdata['classesID'] = 0;
                $this->retdata['assignments'] = [];
            }
        } else {
            $this->retdata['classesID'] = 0;
            $this->retdata['assignments'] = [];
        }

        $assignmentanswer                        = $this->assignmentanswer_m->get_order_by_assignmentanswer(array('uploaderID' => $this->session->userdata('loginuserID'), 'uploadertypeID' => $this->session->userdata('usertypeID'), 'schoolyearID' => $this->session->userdata('defaultschoolyearID')));
        $this->retdata['assignmentanswer']       = $assignmentanswer;
        $this->retdata['total_assignmentanswer'] = count($assignmentanswer);

        $this->response([
            'status' => true,
            'message' => 'Success',
            'data' => $this->retdata,
        ], REST_Controller::HTTP_OK);
    }

    public function view_get($id = 0, $url = 0)
    {
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        if ((int) $id && (int) ($url)) {
            $this->retdata['classesID'] = $url;
            $fetchClasses = pluck($this->classes_m->get_classes(), 'classesID', 'classesID');
            if (isset($fetchClasses[$url])) {
                $assignment = $this->assignment_m->get_single_assignment(array('assignmentID' => $id, 'classesID' => $url, 'schoolyearID' => $schoolyearID));
                if (customCompute($assignment)) {
                    $this->retdata['assignmentanswers'] = $this->assignmentanswer_m->join_get_assignmentanswer($id, $schoolyearID);
                } else {
                    $this->retdata['assignmentanswers'] = [];
                }
            } else {
                $this->retdata['assignmentanswers'] = [];
            }
        } else {
            $this->retdata['classesID'] = $url;
            $this->retdata['assignmentanswers'] = [];
        }

        $this->response([
            'status' => true,
            'message' => 'Success',
            'data' => $this->retdata,
        ], REST_Controller::HTTP_OK);
    }

    public function checkStatus_get($assignmentID = 0)
    {
        if ((int) $assignmentID) {
            $assignment                  = $this->assignment_m->get_single_assignment(array('assignmentID' => $assignmentID, 'schoolyearID' => $this->session->userdata('defaultschoolyearID')));
            $assignmentanswer            = $this->assignmentanswer_m->get_single_assignmentanswer(array('uploaderID' => $this->session->userdata('loginuserID'), 'uploadertypeID' => $this->session->userdata('usertypeID'), 'schoolyearID' => $this->session->userdata('defaultschoolyearID'), 'assignmentID' => $assignmentID));
            if (customCompute($assignmentanswer)) {
                if (strtotime((string) $assignment->deadlinedate) >= strtotime(date('Y-m-d')) && customCompute($assignmentanswer->answerfile)) {
                    $this->retdata['status'] = 2; // complete 
                } elseif (strtotime((string) $assignment->deadlinedate) >= strtotime(date('Y-m-d')) && !customCompute($assignmentanswer->answerfile)) {
                    $this->retdata['status'] = 1; // not upload assingment student
                } else {
                    $this->retdata['status'] = 0; // expired
                }
            } else {
                $this->retdata['status'] = null;
            }
        } else {
            $this->retdata['status'] = null;
        }

        $this->response([
            'status' => true,
            'message' => 'Success',
            'data' => $this->retdata,
        ], REST_Controller::HTTP_OK);
    }

    protected function rules_fileupload()
    {
        return array(
            array(
                'field' => 'assignmentID',
                'label' => 'assignment',
                'rules' => 'trim|required|xss_clean|numeric|max_length[11]|callback_unique_data',
            ),
            array(
                'field' => 'file',
                'label' => 'file',
                'rules' => 'trim|max_length[512]|xss_clean|callback_fileuploadans',
            ),
        );
    }

    public function fileuploadans()
    {
        $new_file = "";
        $original_file_name = '';
        if ($_FILES["file"]['name'] != "") {
            $file_name = $_FILES["file"]['name'];
            $original_file_name = $file_name;
            $random = random19();
            $makeRandom = hash('sha512', $random . $this->input->post('title') . config_item("encryption_key"));
            $file_name_rename = $makeRandom;
            $explode = explode('.', (string) $file_name);
            if (customCompute($explode) >= 2) {
                $new_file = $file_name_rename . '.' . end($explode);
                $config['upload_path'] = "./uploads/images";
                $config['allowed_types'] = "gif|jpg|png|jpeg|pdf|doc|xml|docx|GIF|JPG|PNG|JPEG|PDF|DOC|XML|DOCX|xls|xlsx|txt|ppt|csv|XLS|XLSX|TXT|PPT|CSV";
                $config['file_name'] = $new_file;
                $config['max_size'] = '100024';
                $config['max_width'] = '3000';
                $config['max_height'] = '3000';
                $this->load->library('upload', $config);
                if (!$this->upload->do_upload("file")) {
                    $this->form_validation->set_message("fileuploadans", $this->upload->display_errors());
                    return false;
                } else {
                    $this->upload_data['file'] = $this->upload->data();
                    $this->upload_data['file']['original_file_name'] = $original_file_name;
                    return true;
                }
            } else {
                $this->form_validation->set_message("fileuploadans", "Invalid file");
                return false;
            }
        } else {
            $this->form_validation->set_message("fileuploadans", "The %s field is required");
            return false;
        }
    }

    public function unique_data()
    {
        if (inputCall('assignmentID') == '') {
            $this->form_validation->set_message('unique_data', 'The %s field is required.');
            return false;
        }
        return true;
    }

    public function assignmentanswer_post()
    {
        $rules = $this->rules_fileupload();
        $this->form_validation->set_rules($rules);
        if ($this->form_validation->run() == false) {
            $this->retdata2['validation'] = $this->form_validation->error_array();
            $this->response([
                'status'  => false,
                'message' => "Error",
                'data'    => $this->retdata2,
            ], REST_Controller::HTTP_NOT_FOUND);
        } else {
            $array['answerfileoriginal'] = $this->upload_data['file']['original_file_name'];
            $array['answerfile']         = $this->upload_data['file']['file_name'];
            $array['assignmentID']       = inputCall('assignmentID');
            $array['schoolyearID']       = $this->session->userdata('defaultschoolyearID');
            $array['uploaderID']         = $this->session->userdata('loginuserID');
            $array['uploadertypeID']     = $this->session->userdata('usertypeID');
            $array['answerdate']         = date('Y-m-d');
            $assignment                  = $this->assignment_m->get_single_assignment(array('assignmentID' => inputCall('assignmentID'), 'schoolyearID' => $this->session->userdata('defaultschoolyearID')));
            $assignmentanswer            = $this->assignmentanswer_m->get_single_assignmentanswer(array('uploaderID' => $this->session->userdata('loginuserID'), 'uploadertypeID' => $this->session->userdata('usertypeID'), 'schoolyearID' => $this->session->userdata('defaultschoolyearID'), 'assignmentID' => inputCall('assignmentID')));

            if (strtotime((string) $assignment->deadlinedate) >= strtotime(date('Y-m-d'))) {
                if (customCompute($assignmentanswer)) {
                    $this->assignmentanswer_m->update_assignmentanswer($array, $assignmentanswer->assignmentanswerID);
                    $this->response([
                        'status'  => true,
                        'message' => 'Success',
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->assignmentanswer_m->insert_assignmentanswer($array);
                    $this->response([
                        'status' => true,
                        'message' => 'Success',
                    ], REST_Controller::HTTP_OK);
                }
            } else {
                $this->response([
                    'status'  => false,
                    'message' => "Deadline Date Is Over",
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }
}
