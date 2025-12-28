<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Candidate extends Admin_Controller
{
public $load;
    public $session;
    public $lang;
    public $data;
    public $form_validation;
    public $candidate_m;
    public $db;
    public $uri;
    public $input;
    public $sponsorship_m;
    public $transaction_m;
    public $section_m;
    public $studentrelation_m;
    public $classes_m;
    /*
| -----------------------------------------------------
| PRODUCT NAME:     INILABS SCHOOL MANAGEMENT SYSTEM
| -----------------------------------------------------
| AUTHOR:            INILABS TEAM
| -----------------------------------------------------
| EMAIL:            info@inilabs.net
| -----------------------------------------------------
| COPYRIGHT:        RESERVED BY INILABS IT
| -----------------------------------------------------
| WEBSITE:            http://inilabs.net
| -----------------------------------------------------
 */
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

        $language = $this->session->userdata('lang');
        $this->lang->load('candidate', $language);
    }

    public function index()
    {
        $this->data['classes']    = pluck($this->classes_m->general_get_classes(), 'classes', 'classesID');
        $this->data['sponsors']   = pluck($this->sponsor_m->get_sponsor(), 'name', 'sponsorID');
        $this->data['candidates'] = $this->candidate_m->get_candidate_with_student_sponsorship();

        $this->data["subview"] = "candidate/index";
        $this->load->view('_layout_main', $this->data);
    }

    public function add()
    {
        $this->data['headerassets'] = [
            'css' => [
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css',
                'assets/datepicker/datepicker.css',
            ],
            'js'  => [
                'assets/select2/select2.js',
                'assets/datepicker/datepicker.js',
            ],
        ];

        $this->data['get_all_holidays'] = $this->getHolidaysSession();
        $this->data['students']         = $this->studentrelation_m->get_order_by_student(['srschoolyearID' => $this->session->userdata('defaultschoolyearID')]);
        $this->data['studentInfo']      = $this->getStudentInfo(0);
        if ($_POST !== []) {
            $rules = $this->rules();
            $this->form_validation->set_rules($rules);
            if ($this->form_validation->run() == false) {
                $this->data['studentInfo'] = $this->getStudentInfo($this->input->post('studentID'));
                $this->data["subview"]     = "candidate/add";
                $this->load->view('_layout_main', $this->data);
            } else {
                $array["studentID"]         = $this->input->post('studentID');
                $array["verified_by"]       = $this->input->post('verified_by');
                $array["schoolyearID"]      = $this->session->userdata('defaultschoolyearID');
                $array["date_verification"] = date("Y-m-d", strtotime((string) $this->input->post("date_verification")));
                $array["create_date"]       = date("Y-m-d H:i:s");
                $array["modify_date"]       = date("Y-m-d H:i:s");
                $array["create_userID"]     = $this->session->userdata('loginuserID');
                $array["create_usertypeID"] = $this->session->userdata('usertypeID');

                $this->candidate_m->insert_candidate($array);

                $candidate = $this->candidate_m->get_single_candidate(['candidateID' => $this->db->insert_id()]);
                $this->transaction_log($candidate, 'Add', 'C');

                $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                redirect(base_url("candidate/index"));
            }
        } else {
            $this->data["subview"] = "candidate/add";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function edit()
    {
        $this->data['headerassets'] = [
            'css' => [
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css',
                'assets/datepicker/datepicker.css',
            ],
            'js'  => [
                'assets/select2/select2.js',
                'assets/datepicker/datepicker.js',
            ],
        ];

        $this->data['get_all_holidays'] = $this->getHolidaysSession();
        $candidateID                    = htmlentities((string) escapeString($this->uri->segment(3)));
        $schoolyearID                   = $this->session->userdata('defaultschoolyearID');
        if ((int) $candidateID !== 0) {
            $this->data['candidate'] = $this->candidate_m->get_single_candidate(['candidateID' => $candidateID]);
            $this->data['students']  = $this->studentrelation_m->get_order_by_studentrelation(['srschoolyearID' => $schoolyearID]);
            if (customCompute($this->data['candidate'])) {
                $this->data['studentInfo'] = $this->getStudentInfo($this->data['candidate']->studentID);

                if (customCompute($this->data['studentInfo'])) {
                    if ($_POST !== []) {
                        $rules                     = $this->rules();
                        $this->data['studentInfo'] = $this->getStudentInfo($this->input->post('studentID'));
                        $this->form_validation->set_rules($rules);
                        if ($this->form_validation->run() == false) {

                            $this->data["subview"] = "candidate/edit";
                            $this->load->view('_layout_main', $this->data);
                        } else {
                            $array["studentID"]         = $this->data['studentInfo']->student_id;
                            $array["verified_by"]       = $this->input->post('verified_by');
                            $array["schoolyearID"]      = $this->data['candidate']->schoolyearID;
                            $array["date_verification"] = date("Y-m-d", strtotime((string) $this->input->post("date_verification")));
                            $array["create_date"]       = date("Y-m-d H:i:s");
                            $array["modify_date"]       = date("Y-m-d H:i:s");
                            $array["create_userID"]     = $this->session->userdata('loginuserID');
                            $array["create_usertypeID"] = $this->session->userdata('usertypeID');

                            $this->candidate_m->update_candidate($array, $candidateID);
                            $candidate = $this->candidate_m->get_single_candidate(['candidateID' => $candidateID]);
                            $this->transaction_log($candidate, 'Upd', 'U');

                            $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                            redirect(base_url("candidate/index"));
                        }
                    } else {
                        $this->data["subview"] = "candidate/edit";
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
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function view()
    {
        $candidateID = htmlentities((string) escapeString($this->uri->segment(3)));
        if ((int) $candidateID !== 0) {
            $this->data['candidate'] = $this->candidate_m->get_single_candidate(['candidateID' => $candidateID]);
            if (customCompute($this->data['candidate'])) {
                $this->data['photo']   = pluck($this->student_m->general_get_student(),'photo','studentID');
                $this->data['profile']   = $this->studentrelation_m->get_single_studentrelation(['srstudentID' => $this->data['candidate']->studentID]);
                $this->data['groups']   = pluck($this->studentgroup_m->get_studentgroup(),'group','studentgroupID');
                $this->data['subjects']   = pluck($this->subject_m->general_get_subject(),'subject','subjectID');
                $this->data['usertypes'] = pluck($this->usertype_m->get_usertype(), 'usertype', 'usertypeID');
                $this->data['classes']   = $this->classes_m->get_single_classes(['classesID' => $this->data['profile']->srclassesID]);
                $this->data['section']   = $this->section_m->get_single_section(['sectionID' => $this->data['profile']->srsectionID]);
                $this->data['sponsors']   = pluck($this->sponsor_m->get_sponsor(), 'name', 'sponsorID');
                $this->data["subview"] = "candidate/view";
                $this->load->view('_layout_main', $this->data);
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function print_preview()
    {
        $candidateID = htmlentities((string) escapeString($this->uri->segment(3)));
        if ((int) $candidateID !== 0) {
            $this->data['candidate'] = $this->candidate_m->get_single_candidate(['candidateID' => $candidateID]);
            if (customCompute($this->data['candidate'])) {
                $this->data['profile']   = $this->studentrelation_m->get_single_student(['srstudentID' => $this->data['candidate']->studentID]);
                $this->data['usertypes'] = pluck($this->usertype_m->get_usertype(), 'usertype', 'usertypeID');
                $this->data['classes']   = $this->classes_m->get_single_classes(['classesID' => $this->data['profile']->srclassesID]);

                $this->data['section'] = $this->section_m->get_single_section(['sectionID' => $this->data['profile']->srsectionID]);

                $this->reportPDF('candidatemodule.css', $this->data, 'candidate/print_preview');
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function send_mail()
    {
        $retArray['status']  = false;
        $retArray['message'] = '';
        if (permissionChecker('candidate_view')) {
            if ($_POST !== []) {
                $rules = $this->send_mail_rules();
                $this->form_validation->set_rules($rules);
                if ($this->form_validation->run() == false) {
                    $retArray           = $this->form_validation->error_array();
                    $retArray['status'] = false;
                } else {
                    $candidateID = $this->input->post('candidateID');
                    if ((int) $candidateID !== 0) {
                        $this->data['candidate'] = $this->candidate_m->get_single_candidate(['candidateID' => $candidateID]);
                        if (customCompute($this->data['candidate'])) {
                            $this->data['profile']   = $this->studentrelation_m->get_single_student(['srstudentID' => $this->data['candidate']->studentID]);
                            $this->data['usertypes'] = pluck($this->usertype_m->get_usertype(), 'usertype', 'usertypeID');
                            $this->data['classes']   = $this->classes_m->get_single_classes(['classesID' => $this->data['profile']->srclassesID]);

                            $this->data['section'] = $this->section_m->get_single_section(['sectionID' => $this->data['profile']->srsectionID]);

                            $email   = $this->input->post('to');
                            $subject = $this->input->post('subject');
                            $message = $this->input->post('message');

                            $this->reportSendToMail('candidatemodule.css', $this->data, 'candidate/print_preview', $email, $subject, $message);

                            $retArray['message'] = "Message";
                            $retArray['status']  = true;
                        } else {
                            $retArray['message'] = $this->lang->line('candidate_data_not_found');
                        }
                    } else {
                        $retArray['message'] = $this->lang->line('candidate_data_not_found');
                    }
                }
            } else {
                $retArray['message'] = $this->lang->line('candidate_permissionmethod');
            }
        } else {
            $retArray['message'] = $this->lang->line('candidate_permission');
        }
        echo json_encode($retArray);
        exit;
    }

    public function delete()
    {
        $candidateID = htmlentities((string) escapeString($this->uri->segment(3)));
        if ((int) $candidateID !== 0) {
            $candidate = $this->candidate_m->get_single_candidate(['candidateID' => $candidateID]);
            if (customCompute($candidate)) {
                $sponsorship = $this->sponsorship_m->get_order_by_sponsorship(['candidateID' => $candidateID]);
                if (customCompute($sponsorship)) {
                    $this->session->set_flashdata('error', 'This candidate can\'t be delete because already has sponsorship.');
                } else {
                    $this->transaction_log($candidate, 'Del', 'D');

                    $this->candidate_m->delete_candidate($candidateID);
                    $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                }
            }
        }
        redirect(base_url("candidate/index"));
    }

    private function transaction_log($candidate, $action, $command)
    {
        $body = "<body>";
        $body .= "<candidateID>" . $candidate->candidateID . "</candidateID>";
        $body .= "<studentID>" . $candidate->studentID . "</studentID>";
        $body .= "<verified_by>" . $candidate->verified_by . "</verified_by>";
        $body .= "<schoolyearID>" . $candidate->schoolyearID . "</schoolyearID>";
        $body .= "<create_date>" . $candidate->create_date . "</create_date>";
        $body .= "<modify_date>" . $candidate->modify_date . "</modify_date>";
        $body .= "<create_userID>" . $candidate->create_userID . "</create_userID>";
        $body .= "<create_usertypeID>" . $candidate->create_usertypeID . "</create_usertypeID>";
        $body .= "</body>";

        $transactionArray = array(
            "login_id"          => $this->session->userdata('loginuserID'),
            "primary_key"       => $candidate->candidateID,
            "trans_name"        => strtoupper((string) $action) . '_CANDIDATE',
            "trans_date"        => date("Y-m-d"),
            "trans_time"        => date("H:i:s"),
            "table"             => 'candidate',
            "command"           => $command,
            "body"              => $body,
            "schoolyearID"      => $this->session->userdata('defaultschoolyearID'),
            "create_date"       => date("Y-m-d H:i:s"),
            "create_userID"     => $this->session->userdata('loginuserID'),
            "create_usertypeID" => $this->session->userdata('usertypeID'),
        );
        $this->transaction_m->insert_transaction($transactionArray);
    }

    protected function rules()
    {
        return array(
            array(
                'field' => 'studentID',
                'label' => $this->lang->line("candidate_student"),
                'rules' => 'trim|required|xss_clean|numeric|max_length[11]|callback_unique_data|callback_check_student',
            ),
            array(
                'field' => 'verified_by',
                'label' => $this->lang->line("candidate_verified_by"),
                'rules' => 'trim|required|xss_clean|max_length[100]',
            ),
        );
    }

    public function unique_data($data)
    {
        if ($data == 0) {
            $this->form_validation->set_message("unique_data", "The %s field is required");
            return false;
        }
        return true;
    }

    public function check_student($studentID)
    {
        $candidateID = htmlentities((string) escapeString($this->uri->segment(3)));
        if ((int) $candidateID !== 0) {
            $candidate = $this->candidate_m->get_single_candidate(['studentID' => $studentID, 'candidateID !=' => $candidateID]);
        } else {
            $candidate = $this->candidate_m->get_single_candidate(['studentID' => $studentID]);
        }
        if (customCompute($candidate)) {
            $this->form_validation->set_message("check_student", "This %s already exists");
            return false;
        }
        return true;
    }

    public function getSection()
    {
        $classesID = $this->input->post('classesID');
        if ((int) $classesID !== 0) {
            $sections = $this->section_m->general_get_order_by_section(array('classesID' => $classesID));
            echo "<option value='0'>", $this->lang->line("candidate_select_section"), "</option>";
            foreach ($sections as $section) {
                echo "<option value='" . $section->sectionID . "'>" . $section->section . "</option>";
            }
        }
    }

    public function getSingleStudent()
    {
        $studentID = $this->input->post('studentID');
        echo json_encode((array) $this->getStudentInfo($studentID));
    }

    private function getStudentInfo($studentID)
    {
        $info = [
            'student_id'      => '',
            'registration_no' => '',
            'class'           => '',
            'classes_id'      => '',
            'section'         => '',
            'section_id'      => '',
        ];

        if ((int) $studentID !== 0) {
            $query['srstudentID']    = $studentID;
            $query['srschoolyearID'] = $this->session->userdata('defaultschoolyearID');
            $student                 = $this->studentrelation_m->get_single_studentrelation($query);
            if (customCompute($student)) {
                $info['student_id']      = $student->srstudentID;
                $info['registration_no'] = $student->srregisterNO;
                $section                 = $this->section_m->general_get_single_section(['sectionID' => $student->srsectionID]);
                $classes                 = $this->classes_m->general_get_single_classes(['classesID' => $student->srclassesID]);
                if (customCompute($classes)) {
                    $info['class']      = $classes->classes;
                    $info['classes_id'] = $classes->classesID;
                }

                if (customCompute($section)) {
                    $info['section']    = $section->section;
                    $info['section_id'] = $section->sectionID;
                }
            }
        }

        return (object) $info;
    }

    public function send_mail_rules()
    {
        return array(
            array(
                'field' => 'to',
                'label' => $this->lang->line("candidate_to"),
                'rules' => 'trim|required|max_length[60]|valid_email|xss_clean',
            ),
            array(
                'field' => 'subject',
                'label' => $this->lang->line("candidate_subject"),
                'rules' => 'trim|required|xss_clean',
            ),
            array(
                'field' => 'message',
                'label' => $this->lang->line("candidate_message"),
                'rules' => 'trim|xss_clean',
            ),
            array(
                'field' => 'candidateID',
                'label' => $this->lang->line("candidate_candidate"),
                'rules' => 'trim|required|max_length[10]|xss_clean|callback_unique_data',
            ),
        );
    }

}
