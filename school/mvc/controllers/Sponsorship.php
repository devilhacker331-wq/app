<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Sponsorship extends Admin_Controller
{
public $load;
    public $session;
    public $lang;
    public $data;
    public $form_validation;
    public $candidate_m;
    public $input;
    public $sponsorship_m;
    public $db;
    public $uri;
    public $studentrelation_m;
    public $section_m;
    public $classes_m;
    public $transaction_m;
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
        $this->load->model("sponsor_m");
        $this->load->model("sponsorship_m");
        $this->load->model("section_m");
        $this->load->model('studentrelation_m');
        $this->load->model('student_m');
        $this->load->model('candidate_m');
        $this->load->model('transaction_m');

        $language = $this->session->userdata('lang');
        $this->lang->load('sponsorship', $language);
    }

    public function index()
    {
        $this->data['sponsorships'] = $this->sponsorship_m->get_sponsorship_with_student();
        $this->data["subview"]      = "sponsorship/index";
        $this->load->view('_layout_main', $this->data);
    }

    public function add()
    {
        $this->data['headerassets'] = array(
            'css' => array(
                'assets/datepicker/datepicker.css',
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css',
            ),
            'js'  => array(
                'assets/datepicker/datepicker.js',
                'assets/select2/select2.js',
            ),
        );

        $this->data['sponsors']   = $this->sponsor_m->get_sponsor();
        $this->data['candidates'] = $this->candidate_m->get_candidate_with_student();
        if ($_POST !== []) {
            $rules = $this->rules();
            $this->form_validation->set_rules($rules);
            if ($this->form_validation->run() == false) {
                $this->data["subview"] = "sponsorship/add";
                $this->load->view('_layout_main', $this->data);
            } else {

                $candidate = $this->candidate_m->get_single_candidate(['candidateID' => $this->input->post('studentID')]);

                $array["sponsorID"]   = $this->input->post('sponsorID');
                $array["candidateID"] = $this->input->post('studentID');
                $array["studentID"]   = $candidate->studentID;
                $array["start_date"]  = date("Y-m-d H:i:s", strtotime((string) $this->input->post('start_date')));
                $array["end_date"]    = date("Y-m-d H:i:s", strtotime((string) $this->input->post('end_date')));
                $array["amount"]      = $this->input->post('amount');
                if ($this->input->post('payment_date')) {
                    $array["payment_date"] = date("Y-m-d H:i:s", strtotime((string) $this->input->post('end_date')));
                } else {
                    $array["payment_date"] = null;
                }
                $array["create_date"]       = date("Y-m-d H:i:s");
                $array["modify_date"]       = date("Y-m-d H:i:s");
                $array["create_userID"]     = $this->session->userdata('loginuserID');
                $array["create_usertypeID"] = $this->session->userdata('usertypeID');

                $this->sponsorship_m->insert_sponsorship($array);

                $sponsorshipID = $this->db->insert_id();
                $sponsorship   = $this->sponsorship_m->get_single_sponsorship(['sponsorshipID' => $sponsorshipID]);
                $this->transaction_log($sponsorship, 'Add', 'C');

                $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                redirect(base_url("sponsorship/index"));
            }
        } else {
            $this->data["subview"] = "sponsorship/add";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function edit()
    {
        $this->data['headerassets'] = array(
            'css' => array(
                'assets/datepicker/datepicker.css',
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css',
            ),
            'js'  => array(
                'assets/datepicker/datepicker.js',
                'assets/select2/select2.js',
            ),
        );

        $sponsorshipID = htmlentities((string) escapeString($this->uri->segment(3)));
        if ((int) $sponsorshipID !== 0) {
            $this->data['sponsorship'] = $this->sponsorship_m->get_single_sponsorship(['sponsorshipID' => $sponsorshipID]);
            if (customCompute($this->data['sponsorship'])) {

                $this->data['sponsors']   = $this->sponsor_m->get_sponsor();
                $this->data['candidates'] = $this->candidate_m->get_candidate_with_student();
                if ($_POST !== []) {
                    $rules = $this->rules();
                    $this->form_validation->set_rules($rules);
                    if ($this->form_validation->run() == false) {
                        $this->data["subview"] = "sponsorship/edit";
                        $this->load->view('_layout_main', $this->data);
                    } else {

                        $candidate = $this->candidate_m->get_single_candidate(['candidateID' => $this->input->post('studentID')]);

                        $array["sponsorID"]   = $this->input->post('sponsorID');
                        $array["candidateID"] = $this->input->post('studentID');
                        $array["studentID"]   = $candidate->studentID;
                        $array["start_date"]  = date("Y-m-d H:i:s", strtotime((string) $this->input->post('start_date')));
                        $array["end_date"]    = date("Y-m-d H:i:s", strtotime((string) $this->input->post('end_date')));
                        $array["amount"]      = $this->input->post('amount');
                        if ($this->input->post('payment_date')) {
                            $array["payment_date"] = date("Y-m-d H:i:s", strtotime((string) $this->input->post('end_date')));
                        } else {
                            $array["payment_date"] = null;
                        }
                        $array["create_date"]       = date("Y-m-d H:i:s");
                        $array["modify_date"]       = date("Y-m-d H:i:s");
                        $array["create_userID"]     = $this->session->userdata('loginuserID');
                        $array["create_usertypeID"] = $this->session->userdata('usertypeID');

                        $this->sponsorship_m->update_sponsorship($array, $sponsorshipID);

                        $sponsorship = $this->sponsorship_m->get_single_sponsorship(['sponsorshipID' => $sponsorshipID]);
                        $this->transaction_log($sponsorship, 'Upd', 'U');

                        $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                        redirect(base_url("sponsorship/index"));
                    }
                } else {
                    $this->data["subview"] = "sponsorship/edit";
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

    public function getSingleStudent()
    {
        $studentID = $this->input->post('studentID');

        if ($studentID) {
            $student = $this->getStudentInfo($studentID);

            $html = '<div class="box-header">';
            $html .= "<h3 class='box-title'><i class='fa icon-student'></i>" . $this->lang->line('sponsorship_student_profile') . "</h3>";
            $html .= "</div>";
            $html .= '<div class="box-body box-profile">';
            $html .= profileviewimage($student->photo);
            $html .= "<h3 class='profile-username text-center'>" . $student->name . "</h3>";
            $html .= "</div>";
            $html .= "<ul class='list-group list-group-unbordered'>";
            $html .= "<li class='list-group-item' style='background-color: #FFF'>";
            $html .= "<b>" . $this->lang->line('candidate_registerNO') . "</b> <a class='pull-right'>" . $student->registration_no . "</a>";
            $html .= "</li>";
            $html .= "<li class='list-group-item' style='background-color: #FFF'>";
            $html .= "<b>" . $this->lang->line('candidate_roll') . "</b> <a class='pull-right'>" . $student->roll . "</a>";
            $html .= "</li>";
            $html .= "<li class='list-group-item' style='background-color: #FFF'>";
            $html .= "<b>" . $this->lang->line('candidate_class') . "</b> <a class='pull-right'>" . $student->class . "</a>";
            $html .= "</li>";
            $html .= "<li class='list-group-item' style='background-color: #FFF'>";
            $html .= "<b>" . $this->lang->line('candidate_section') . "</b> <a class='pull-right'>" . $student->section . "</a>";
            $html .= "</li>";
            $html .= "</ul>";
            $html .= "</div>";
            echo $html;
        } else {
            echo "";
        }

    }

    private function getStudentInfo($studentID)
    {
        $info = [
            'student_id'      => '',
            'name'            => '',
            'roll'            => '',
            'sex'             => '',
            'registration_no' => '',
            'class'           => '',
            'classes_id'      => '',
            'section'         => '',
            'section_id'      => '',
            'grade'           => '',
            'phone'           => '',
        ];

        if ((int) $studentID !== 0) {
            $candidate               = $this->candidate_m->get_single_candidate(['candidateID' => $this->input->post('studentID')]);
            $query['srstudentID']    = $candidate->studentID;
            $query['srschoolyearID'] = $this->session->userdata('defaultschoolyearID');
            $student                 = $this->studentrelation_m->get_single_student($query);
            if (customCompute($student)) {
                $info['student_id']      = $student->studentID;
                $info['name']            = $student->srname;
                $info['roll']            = $student->srroll;
                $info['sex']             = $student->sex;
                $info['registration_no'] = $student->srregisterNO;
                $info['photo']           = $student->photo;
                $section                 = $this->section_m->general_get_single_section(['sectionID' => $student->srsectionID]);
                $classes                 = $this->classes_m->general_get_single_classes(['classesID' => $student->srclassesID]);
                if (customCompute($classes)) {
                    $info['class']      = $classes->classes;
                    $info['grade']      = $classes->classes;
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

    public function renew()
    {
        if (permissionChecker('sponsorship_add')) {
            $this->data['headerassets'] = array(
                'css' => array(
                    'assets/datepicker/datepicker.css',
                    'assets/select2/css/select2.css',
                    'assets/select2/css/select2-bootstrap.css',
                ),
                'js'  => array(
                    'assets/datepicker/datepicker.js',
                    'assets/select2/select2.js',
                ),
            );
            $sponsorshipID = htmlentities((string) escapeString($this->uri->segment(3)));
            if ((int) $sponsorshipID !== 0) {
                $sponsorship               = $this->sponsorship_m->get_single_sponsorship(['sponsorshipID' => $sponsorshipID]);
                $this->data['sponsorship'] = $sponsorship;
                if (customCompute($sponsorship)) {

                    $this->data['sponsors']    = $this->sponsor_m->get_single_sponsor(['sponsorID' => $sponsorship->sponsorID]);
                    $this->data['candidates'] = $this->candidate_m->get_candidate_with_student(['candidate.candidateID' => $sponsorship->candidateID]);

                    $start_date     = strtotime((string) $this->data['sponsorship']->start_date);
                    $new_start_date = date('Y-m-d H:i:s', strtotime("+1 year", $start_date));

                    $end_date     = strtotime((string) $this->data['sponsorship']->end_date);
                    $new_end_date = date('Y-m-d H:i:s', strtotime("+1 year", $end_date));

                    $this->data['sponsorship']->start_date   = $new_start_date;
                    $this->data['sponsorship']->end_date     = $new_end_date;
                    $this->data['sponsorship']->payment_date = '';

                    if ($_POST !== []) {
                        $rules = $this->rules();
                        $this->form_validation->set_rules($rules);
                        if ($this->form_validation->run() == false) {
                            $this->data["subview"] = "sponsorship/renew";
                            $this->load->view('_layout_main', $this->data);
                        } else {

                            $array["sponsorID"]   = $this->input->post('sponsorID');
                            $array["candidateID"] = $this->input->post('studentID');
                            $array["studentID"]   = $sponsorship->studentID;
                            $array["start_date"]  = date("Y-m-d H:i:s", strtotime((string) $this->input->post('start_date')));
                            $array["end_date"]    = date("Y-m-d H:i:s", strtotime((string) $this->input->post('end_date')));
                            $array["amount"]      = $this->input->post('amount');
                            if ($this->input->post('payment_date')) {
                                $array["payment_date"] = date("Y-m-d H:i:s", strtotime((string) $this->input->post('end_date')));
                            } else {
                                $array["payment_date"] = null;
                            }
                            $array["create_date"]       = date("Y-m-d H:i:s");
                            $array["modify_date"]       = date("Y-m-d H:i:s");
                            $array["create_userID"]     = $this->session->userdata('loginuserID');
                            $array["schoolyearID"]      = $this->session->userdata('defaultschoolyearID');
                            $array["create_usertypeID"] = $this->session->userdata('usertypeID');

                            $this->sponsorship_m->insert_sponsorship($array);

                            $sponsorshipID = $this->db->insert_id();
                            $sponsorship   = $this->sponsorship_m->get_single_sponsorship(['sponsorshipID' => $sponsorshipID]);
                            $this->transaction_log($sponsorship, 'Renew', 'R');

                            $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                            redirect(base_url("sponsorship/index"));
                        }
                    } else {
                        $this->data["subview"] = "sponsorship/renew";
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

    public function delete()
    {
        $sponsorshipID = htmlentities((string) escapeString($this->uri->segment(3)));
        if ((int) $sponsorshipID !== 0) {
            $sponsorship = $this->sponsorship_m->get_single_sponsorship(['sponsorshipID' => $sponsorshipID]);
            if (customCompute($sponsorship)) {
                if ($sponsorship->payment_date) {
                    $this->session->set_flashdata('error', 'You can\'t delete this sponsorship because its already paid.');
                } else {
                    $this->transaction_log($sponsorship, 'Del', 'D');
                    $this->sponsorship_m->delete_sponsorship($sponsorshipID);
                    $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                }
                redirect(base_url("sponsorship/index"));
            }
        }
        redirect(base_url("sponsorship/index"));
    }

    private function transaction_log($sponsorship, $action, $command)
    {
        $body = "<body>";
        $body .= "<sponsorshipID>" . $sponsorship->sponsorshipID . "</sponsorshipID>";
        $body .= "<sponsorID>" . $sponsorship->sponsorID . "</sponsorID>";
        $body .= "<candidateID>" . $sponsorship->candidateID . "</candidateID>";
        $body .= "<studentID>" . $sponsorship->studentID . "</studentID>";
        $body .= "<start_date>" . $sponsorship->start_date . "</start_date>";
        $body .= "<end_date>" . $sponsorship->end_date . "</end_date>";
        $body .= "<amount>" . $sponsorship->amount . "</amount>";
        $body .= "<payment_date>" . $sponsorship->payment_date . "</payment_date>";
        $body .= "<schoolyearID>" . $sponsorship->schoolyearID . "</schoolyearID>";
        $body .= "<create_date>" . $sponsorship->create_date . "</create_date>";
        $body .= "<modify_date>" . $sponsorship->modify_date . "</modify_date>";
        $body .= "<create_userID>" . $sponsorship->create_userID . "</create_userID>";
        $body .= "<create_usertypeID>" . $sponsorship->create_usertypeID . "</create_usertypeID>";
        $body .= "</body>";

        $transactionArray = array(
            "login_id"          => $this->session->userdata('loginuserID'),
            "primary_key"       => $sponsorship->sponsorshipID,
            "trans_name"        => strtoupper((string) $action) . '_SPONSORSHIP',
            "trans_date"        => date("Y-m-d"),
            "trans_time"        => date("H:i:s"),
            "table"             => 'sponsorship',
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
                'field' => 'sponsorID',
                'label' => $this->lang->line("sponsorship_sponsor"),
                'rules' => 'trim|required|xss_clean|numeric|max_length[11]|callback_unique_data',
            ),
            array(
                'field' => 'studentID',
                'label' => $this->lang->line("sponsorship_student"),
                'rules' => 'trim|required|xss_clean|numeric|max_length[11]|callback_unique_data',
            ),
            array(
                'field' => 'start_date',
                'label' => $this->lang->line("sponsorship_start_date"),
                'rules' => 'trim|required|xss_clean|max_length[11]',
            ),
            array(
                'field' => 'end_date',
                'label' => $this->lang->line("sponsorship_end_date"),
                'rules' => 'trim|required|xss_clean|max_length[11]|callback_unique_date',
            ),
            array(
                'field' => 'amount',
                'label' => $this->lang->line("sponsorship_amount"),
                'rules' => 'trim|required|xss_clean|numeric|max_length[11]',
            ),
            array(
                'field' => 'payment_date',
                'label' => $this->lang->line("sponsorship_payment_date"),
                'rules' => 'trim|xss_clean|max_length[11]',
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

    public function unique_date()
    {
        $start_date = $this->input->post('start_date');
        $end_date   = $this->input->post('end_date');

        if ($start_date != '' && $end_date != '' && strtotime((string) $start_date) > strtotime((string) $end_date)) {
            $this->form_validation->set_message("unique_date", "The End Date cannot be earlier than Start Date.");
            return false;
        }
        return true;
    }

    public function getSection()
    {
        $classesID = $this->input->post('classesID');
        if ((int) $classesID !== 0) {
            $sections = $this->section_m->general_get_order_by_section(array('classesID' => $classesID));
            echo "<option value='0'>", $this->lang->line("sponsorship_select_section"), "</option>";
            foreach ($sections as $section) {
                echo "<option value='" . $section->sectionID . "'>" . $section->section . "</option>";
            }
        }
    }

    public function getStudent()
    {
        $classesID    = $this->input->post('classesID');
        $sectionID    = $this->input->post('sectionID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');

        $queryArray['srschoolyearID'] = $schoolyearID;
        if ((int) $classesID !== 0) {
            $queryArray['srclassesID'] = $classesID;
        }

        if ((int) $sectionID !== 0) {
            $queryArray['srsectionID'] = $sectionID;
        }

        $students = $this->studentrelation_m->get_order_by_student($queryArray);
        echo "<option value='0'>" . $this->lang->line("sponsorship_select_section") . "</option>";
        if (customCompute($students)) {
            foreach ($students as $student) {
                echo "<option value='" . $student->srstudentID . "'>" . $student->srname . "</option>";
            }
        }
    }

    public function getEnddata()
    {
        $start_date = $this->input->post('start_date');
        $date       = date_create($start_date);
        date_add($date, date_interval_create_from_date_string("365 days"));
        echo date_format($date, "d-m-Y");
    }

}
