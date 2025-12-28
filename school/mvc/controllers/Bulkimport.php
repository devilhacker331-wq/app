<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Bulkimport extends Admin_Controller
{
    public $load;
    public $session;
    public $lang;
    public $data;
    public $upload;
    public $csvimport;
    public $teacher_m;
    public $parents_m;
    public $input;
    public $user_m;
    public $book_m;
    public $student_m;
    public $db;
    public $classes_m;
    public $section_m;
    public $studentrelation_m;
    public $studentextend_m;
    public $question_bank_m;
    public $question_option_m;
    public $question_answer_m;
    public $subject_m;
    public $studentgroup_m;
    public $usertype_m;
    public $question_group_m;
    public $question_level_m;
    public $question_type_m;
    /*
    | -----------------------------------------------------
    | PRODUCT NAME: 	INILABS SCHOOL MANAGEMENT SYSTEM
    | -----------------------------------------------------
    | AUTHOR:			INILABS TEAM
    | -----------------------------------------------------
    | EMAIL:			info@inilabs.net
    | -----------------------------------------------------
    | COPYRIGHT:		RESERVED BY INILABS IT
    | -----------------------------------------------------
    | WEBSITE:			http://inilabs.net
    | -----------------------------------------------------
    */
    function __construct()
    {
        parent::__construct();
        $this->load->model("teacher_m");
        $this->load->model("parents_m");
        $this->load->model("student_m");
        $this->load->model("user_m");
        $this->load->model("book_m");
        $this->load->model("studentrelation_m");
        $this->load->model("section_m");
        $this->load->model("classes_m");
        $this->load->model("studentextend_m");
        $this->load->model("subject_m");
        $this->load->model("studentgroup_m");
        $this->load->model("question_bank_m");
        $this->load->model("question_group_m");
        $this->load->model("question_level_m");
        $this->load->model("question_type_m");
        $this->load->model("question_answer_m");
        $this->load->model("question_option_m");
        $this->load->model("online_exam_question_m");

        $this->load->library('csvimport');

        $language = $this->session->userdata('lang');
        $this->lang->load('bulkimport', $language);
    }

    public function index()
    {
        $this->data["subview"] = "bulkimport/index";
        $this->load->view('_layout_main', $this->data);
    }


    public function teacher_bulkimport()
    {
        if (isset($_FILES["csvFile"])) {
            $config['upload_path']   = "./uploads/csv/";
            $config['allowed_types'] = 'text/plain|text/csv|csv';
            $config['max_size']      = '2048';
            $config['file_name']     = $_FILES["csvFile"]['name'];
            $config['overwrite']     = TRUE;
            $this->load->library('upload', $config);
            if (!$this->upload->do_upload("csvFile")) {
                $this->session->set_flashdata('error', $this->lang->line('bulkimport_upload_fail'));
                redirect(base_url("bulkimport/index"));
            } else {
                $file_data      = $this->upload->data();
                $file_path      = './uploads/csv/' . $file_data['file_name'];
                $column_headers = [
                    "Name",
                    "Designation",
                    "Dob",
                    "Gender",
                    "Religion",
                    "Email",
                    "Phone",
                    "Address",
                    "Jod",
                    "Username",
                    "Password"
                ];

                if ($csv_array = @$this->csvimport->get_array($file_path, $column_headers)) {
                    if (customCompute($csv_array)) {
                        $msg     = "";
                        $i       = 1;
                        $csv_col = [];
                        foreach ($csv_array as $row) {
                            if ($i == 1) {
                                $csv_col = array_keys($row);
                            }
                            $match = array_diff($column_headers, $csv_col);
                            if (customCompute($match) <= 0) {
                                $array              = $this->arrayToPost($row);
                                $singleteacherCheck = $this->singleteacherCheck($array);

                                if ($singleteacherCheck['status']) {
                                    $insert_data = [
                                        'name'            => $row['Name'],
                                        'designation'     => $row['Designation'],
                                        'dob'             => $this->trim_required_convertdate($row['Dob']),
                                        'sex'             => $row['Gender'],
                                        'religion'        => $row['Religion'],
                                        'email'           => $row['Email'],
                                        'phone'           => $row['Phone'],
                                        'address'         => $row['Address'],
                                        'jod'             => $this->trim_required_convertdate($row['Jod']),
                                        'username'        => $row['Username'],
                                        'password'        => $this->teacher_m->hash($row['Password']),
                                        'usertypeID'      => 2,
                                        'photo'           => 'default.png',
                                        "create_date"     => date("Y-m-d h:i:s"),
                                        "modify_date"     => date("Y-m-d h:i:s"),
                                        "create_userID"   => $this->session->userdata('loginuserID'),
                                        "create_username" => $this->session->userdata('username'),
                                        "create_usertype" => $this->session->userdata('usertype'),
                                        "active"          => 1,
                                    ];
                                    $this->usercreatemail($row['Email'], $row['Username'], $row['Password']);
                                    $this->teacher_m->insert_teacher($insert_data);
                                } else {
                                    $msg .= $i . ". " . $row['Name'] . " is not added! , ";
                                    $msg .= implode(' , ', $singleteacherCheck['error']);
                                    $msg .= ". <br/>";
                                }
                            } else {
                                $this->session->set_flashdata('error', "Wrong csv file!");
                                redirect(base_url("bulkimport/index"));
                            }
                            $i++;
                        }
                        if ($msg != "") {
                            $this->session->set_flashdata('msg', $msg);
                        }
                        $this->session->set_flashdata('success', $this->lang->line('bulkimport_success'));
                        redirect(base_url("bulkimport/index"));
                    } else {
                        $this->session->set_flashdata('error', $this->lang->line('bulkimport_data_not_found'));
                        redirect(base_url("bulkimport/index"));
                    }
                } else {
                    $this->session->set_flashdata('error', "Wrong csv file!");
                    redirect(base_url("bulkimport/index"));
                }
            }
        } else {
            $this->session->set_flashdata('error', $this->lang->line('bulkimport_select_file'));
            redirect(base_url("bulkimport/index"));
        }
    }

    public function parent_bulkimport()
    {
        if (isset($_FILES["csvParent"])) {
            $config['upload_path']   = "./uploads/csv/";
            $config['allowed_types'] = 'text/plain|text/csv|csv';
            $config['max_size']      = '2048';
            $config['file_name']     = $_FILES["csvParent"]['name'];
            $config['overwrite']     = TRUE;
            $this->load->library('upload', $config);
            if (!$this->upload->do_upload("csvParent")) {
                $this->session->set_flashdata('error', $this->lang->line('bulkimport_upload_fail'));
                redirect(base_url("bulkimport/index"));
            } else {
                $file_data      = $this->upload->data();
                $file_path      = './uploads/csv/' . $file_data['file_name'];
                $column_headers = [
                    "Name",
                    "Father Name",
                    "Mother Name",
                    "Father Profession",
                    "Mother Profession",
                    "Email",
                    "Phone",
                    "Address",
                    "Username",
                    "Password"
                ];

                if ($csv_array = @$this->csvimport->get_array($file_path, $column_headers)) {
                    if (customCompute($csv_array)) {
                        $i       = 1;
                        $msg     = "";
                        $csv_col = [];
                        foreach ($csv_array as $row) {
                            if ($i == 1) {
                                $csv_col = array_keys($row);
                            }
                            $match = array_diff($column_headers, $csv_col);
                            if (customCompute($match) <= 0) {
                                $array             = $this->arrayToPost($row);
                                $singleparentCheck = $this->singleparentCheck($array);
                                if ($singleparentCheck['status']) {
                                    $insert_data = [
                                        'name'              => $row['Name'],
                                        'father_name'       => $row['Father Name'],
                                        'mother_name'       => $row['Mother Name'],
                                        'father_profession' => $row['Father Profession'],
                                        'mother_profession' => $row['Mother Profession'],
                                        'email'             => $row['Email'],
                                        'phone'             => $row['Phone'],
                                        'photo'             => 'default.png',
                                        'address'           => $row['Address'],
                                        'username'          => $row['Username'],
                                        'password'          => $this->parents_m->hash($row['Password']),
                                        'usertypeID'        => 2,
                                        'photo'             => 'default.png',
                                        "create_date"       => date("Y-m-d h:i:s"),
                                        "modify_date"       => date("Y-m-d h:i:s"),
                                        "create_userID"     => $this->session->userdata('loginuserID'),
                                        "create_username"   => $this->session->userdata('username'),
                                        "create_usertype"   => $this->session->userdata('usertype'),
                                        "active"            => 1,
                                    ];
                                    // For Email
                                    $this->usercreatemail($this->input->post('email'), $this->input->post('username'), $this->input->post('password'));
                                    $this->parents_m->insert_parents($insert_data);
                                } else {
                                    $msg .= $i . ". " . $row['Name'] . " is not added! , ";
                                    $msg .= implode(' , ', $singleparentCheck['error']);
                                    $msg .= ". <br/>";
                                }
                            } else {
                                $this->session->set_flashdata('error', "Wrong csv file!");
                                redirect(base_url("bulkimport/index"));
                            }
                            $i++;
                        }
                        if ($msg != "") {
                            $this->session->set_flashdata('msg', $msg);
                        }
                        $this->session->set_flashdata('success', $this->lang->line('bulkimport_success'));
                        redirect(base_url("bulkimport/index"));
                    } else {
                        $this->session->set_flashdata('error', $this->lang->line('bulkimport_data_not_found'));
                        redirect(base_url("bulkimport/index"));
                    }
                } else {
                    $this->session->set_flashdata('error', "Wrong csv file!");
                    redirect(base_url("bulkimport/index"));
                }
            }
        } else {
            $this->session->set_flashdata('error', $this->lang->line('bulkimport_select_file'));
            redirect(base_url("bulkimport/index"));
        }
    }

    public function user_bulkimport()
    {
        if (isset($_FILES["csvUser"])) {
            $config['upload_path']   = "./uploads/csv/";
            $config['allowed_types'] = 'text/plain|text/csv|csv';
            $config['max_size']      = '2048';
            $config['file_name']     = $_FILES["csvUser"]['name'];
            $config['overwrite']     = TRUE;
            $this->load->library('upload', $config);
            if (!$this->upload->do_upload("csvUser")) {
                $this->session->set_flashdata('error', $this->lang->line('bulkimport_upload_fail'));
                redirect(base_url("bulkimport/index"));
            } else {
                $file_data      = $this->upload->data();
                $file_path      = './uploads/csv/' . $file_data['file_name'];
                $column_headers = [
                    "Name",
                    "Dob",
                    "Gender",
                    "Religion",
                    "Email",
                    "Phone",
                    "Address",
                    "Jod",
                    "Username",
                    "Password",
                    "Usertype"
                ];
                if ($csv_array = @$this->csvimport->get_array($file_path, $column_headers)) {
                    if (customCompute($csv_array)) {
                        $i       = 1;
                        $msg     = "";
                        $csv_col = [];
                        foreach ($csv_array as $row) {
                            if ($i == 1) {
                                $csv_col = array_keys($row);
                            }
                            $match = array_diff($column_headers, $csv_col);
                            if (customCompute($match) <= 0) {
                                $array           = $this->arrayToPost($row);
                                $singleuserCheck = $this->singleuserCheck($array);
                                if ($singleuserCheck['status']) {
                                    $dob         = $this->trim_required_convertdate($row['Dob']);
                                    $jod         = $this->trim_required_convertdate($row['Jod']);
                                    $insert_data = [
                                        'name'            => $row['Name'],
                                        'dob'             => $dob,
                                        'sex'             => $row['Gender'],
                                        'religion'        => $row['Religion'],
                                        'email'           => $row['Email'],
                                        'phone'           => $row['Phone'],
                                        'address'         => $row['Address'],
                                        'jod'             => $jod,
                                        'photo'           => 'default.png',
                                        'username'        => $row['Username'],
                                        'password'        => $this->user_m->hash($row['Password']),
                                        'usertypeID'      => $this->trim_check_usertype($row['Usertype']),
                                        "create_date"     => date("Y-m-d h:i:s"),
                                        "modify_date"     => date("Y-m-d h:i:s"),
                                        "create_userID"   => $this->session->userdata('loginuserID'),
                                        "create_username" => $this->session->userdata('username'),
                                        "create_usertype" => $this->session->userdata('usertype'),
                                        "active"          => 1,
                                    ];
                                    $this->user_m->insert_user($insert_data);
                                    $this->usercreatemail($this->input->post('email'), $this->input->post('username'), $this->input->post('password'));
                                } else {
                                    $msg .= $i . ". " . $row['Name'] . " is not added! , ";
                                    $msg .= implode(' , ', $singleuserCheck['error']);
                                    $msg .= ". <br/>";
                                }
                            } else {
                                $this->session->set_flashdata('error', "Wrong csv file!");
                                redirect(base_url("bulkimport/index"));
                            }
                            $i++;
                        }
                        if ($msg != "") {
                            $this->session->set_flashdata('msg', $msg);
                        }
                        $this->session->set_flashdata('success', $this->lang->line('bulkimport_success'));
                        redirect(base_url("bulkimport/index"));
                    } else {
                        $this->session->set_flashdata('error', $this->lang->line('bulkimport_data_not_found'));
                        redirect(base_url("bulkimport/index"));
                    }
                } else {
                    $this->session->set_flashdata('error', "Wrong csv file!");
                    redirect(base_url("bulkimport/index"));
                }
            }
        } else {
            $this->session->set_flashdata('error', $this->lang->line('bulkimport_select_file'));
            redirect(base_url("bulkimport/index"));
        }
    }

    public function book_bulkimport()
    {
        if (isset($_FILES["csvBook"])) {
            $config['upload_path']   = "./uploads/csv/";
            $config['allowed_types'] = 'text/plain|text/csv|csv';
            $config['max_size']      = '2048';
            $config['file_name']     = $_FILES["csvBook"]['name'];
            $config['overwrite']     = TRUE;
            $this->load->library('upload', $config);
            if (!$this->upload->do_upload("csvBook")) {
                $this->session->set_flashdata('error', $this->lang->line('bulkimport_upload_fail'));
                redirect(base_url("bulkimport/index"));
            } else {
                $file_data      = $this->upload->data();
                $file_path      = './uploads/csv/' . $file_data['file_name'];
                $column_headers = ["Book", "Subject code", "Author", "Price", "Quantity", "Rack"];
                if ($csv_array = @$this->csvimport->get_array($file_path, $column_headers)) {
                    if (customCompute($csv_array)) {
                        $i       = 1;
                        $msg     = "";
                        $csv_col = [];
                        foreach ($csv_array as $row) {
                            if ($i == 1) {
                                $csv_col = array_keys($row);
                            }

                            $match = array_diff($column_headers, $csv_col);
                            if (customCompute($match) <= 0) {
                                $array           = $this->arrayToPost($row);
                                $singlebookCheck = $this->singlebookCheck($array);

                                if ($singlebookCheck['status']) {
                                    $insert_data = [
                                        'book'         => $row['Book'],
                                        'subject_code' => $row['Subject code'],
                                        'author'       => $row['Author'],
                                        'price'        => $row['Price'],
                                        'quantity'     => $row['Quantity'],
                                        'due_quantity' => 0,
                                        'rack'         => $row['Rack']
                                    ];
                                    $this->book_m->insert_book($insert_data);
                                } else {
                                    $msg .= $i . ". " . $row['Book'] . " is not added! , ";
                                    $msg .= implode(' , ', $singlebookCheck['error']);
                                    $msg .= ". <br/>";
                                }
                            } else {
                                $this->session->set_flashdata('error', "Wrong csv file!");
                                redirect(base_url("bulkimport/index"));
                            }
                            $i++;
                        }
                        if ($msg != "") {
                            $this->session->set_flashdata('msg', $msg);
                        }
                        $this->session->set_flashdata('success', $this->lang->line('bulkimport_success'));
                        redirect(base_url("bulkimport/index"));
                    } else {
                        $this->session->set_flashdata('error', $this->lang->line('bulkimport_data_not_found'));
                        redirect(base_url("bulkimport/index"));
                    }
                } else {
                    $this->session->set_flashdata('error', "Wrong csv file!");
                    redirect(base_url("bulkimport/index"));
                }
            }
        } else {
            $this->session->set_flashdata('error', $this->lang->line('bulkimport_error'));
            redirect(base_url("bulkimport/index"));
        }
    }

    public function student_bulkimport()
    {
        if (isset($_FILES["csvStudent"])) {
            $config['upload_path']   = "./uploads/csv/";
            $config['allowed_types'] = 'text/plain|text/csv|csv';
            $config['max_size']      = '2048';
            $config['file_name']     = $_FILES["csvStudent"]['name'];
            $config['overwrite']     = TRUE;
            $this->load->library('upload', $config);
            if (!$this->upload->do_upload("csvStudent")) {
                $this->session->set_flashdata('error', $this->lang->line('bulkimport_upload_fail'));
                redirect(base_url("bulkimport/index"));
            } else {
                $file_data      = $this->upload->data();
                $file_path      = './uploads/csv/' . $file_data['file_name'];
                $column_headers = [
                    "Name",
                    "Dob",
                    "Gender",
                    "Religion",
                    "Email",
                    "Phone",
                    "Address",
                    "Class",
                    "Section",
                    "Username",
                    "Password",
                    "Roll",
                    "BloodGroup",
                    "State",
                    "Country",
                    "RegistrationNO",
                    "Group",
                    "OptionalSubject"
                ];
                if ($csv_array = @$this->csvimport->get_array($file_path, $column_headers)) {
                    if (customCompute($csv_array)) {
                        $msg     = "";
                        $i       = 1;
                        $csv_col = [];
                        foreach ($csv_array as $row) {
                            if ($i == 1) {
                                $csv_col = array_keys($row);
                            }
                            $match = array_diff($column_headers, $csv_col);
                            if (customCompute($match) <= 0) {
                                $array              = $this->arrayToPost($row);
                                $singlestudentCheck = $this->singlestudentCheck($array);
                                if ($singlestudentCheck['status']) {
                                    $classID         = $this->get_student_class($row['Class']);
                                    $sections        = $this->get_student_section($classID, $row['Section']);
                                    $group           = $this->get_student_group($row['Group']);
                                    $optionalSubject = $this->get_student_optional_subject($classID, $row['OptionalSubject']);
                                    $dob             = $this->trim_required_convertdate($row['Dob']);

                                    $insert_data = [
                                        'name'               => $row['Name'],
                                        'dob'                => $dob,
                                        'sex'                => $row['Gender'],
                                        'religion'           => $row['Religion'],
                                        'email'              => $row['Email'],
                                        'phone'              => $row['Phone'],
                                        'photo'              => 'default.png',
                                        'address'            => $row['Address'],
                                        "bloodgroup"         => $row['BloodGroup'],
                                        "state"              => $row['State'],
                                        "country"            => $this->get_student_country($row['Country']),
                                        "registerNO"         => $row['RegistrationNO'],
                                        'classesID'          => $classID,
                                        'sectionID'          => $sections->sectionID,
                                        'roll'               => $row['Roll'],
                                        'username'           => $row['Username'],
                                        'password'           => $this->student_m->hash($row['Password']),
                                        'usertypeID'         => 3,
                                        'parentID'           => 0,
                                        'library'            => 0,
                                        'hostel'             => 0,
                                        'transport'          => 0,
                                        'createschoolyearID' => $this->session->userdata('defaultschoolyearID'),
                                        'schoolyearID'       => $this->session->userdata('defaultschoolyearID'),
                                        "create_date"        => date("Y-m-d h:i:s"),
                                        "modify_date"        => date("Y-m-d h:i:s"),
                                        "create_userID"      => $this->session->userdata('loginuserID'),
                                        "create_username"    => $this->session->userdata('username'),
                                        "create_usertype"    => $this->session->userdata('usertype'),
                                        "active"             => 1,
                                    ];

                                    $this->usercreatemail($this->input->post('email'), $this->input->post('username'), $this->input->post('password'));
                                    $this->student_m->insert_student($insert_data);
                                    $studentID = $this->db->insert_id();

                                    $classes = $this->classes_m->general_get_single_classes(['classesID' => $classID]);
                                    $section = $this->section_m->general_get_single_section([
                                        'classesID' => $classID,
                                        'sectionID' => $sections->sectionID
                                    ]);

                                    $setClasses = customCompute($classes) ? $classes->classes : NULL;

                                    $setSection = customCompute($section) ? $section->section : NULL;

                                    $studentReletion = $this->studentrelation_m->get_order_by_studentrelation([
                                        'srstudentID'    => $studentID,
                                        'srschoolyearID' => $this->session->userdata('defaultschoolyearID')
                                    ]);
                                    if (!customCompute($studentReletion)) {
                                        $arrayStudentRelation = [
                                            'srstudentID'         => $studentID,
                                            'srname'              => $row['Name'],
                                            'srclassesID'         => $classID,
                                            'srclasses'           => $setClasses,
                                            'srroll'              => $row['Roll'],
                                            'srregisterNO'        => $row['RegistrationNO'],
                                            'srsectionID'         => $sections->sectionID,
                                            'srsection'           => $setSection,
                                            'srstudentgroupID'    => $group->studentgroupID,
                                            'sroptionalsubjectID' => $optionalSubject->subjectID,
                                            'srschoolyearID'      => $this->session->userdata('defaultschoolyearID')
                                        ];
                                        $this->studentrelation_m->insert_studentrelation($arrayStudentRelation);
                                    } else {
                                        $arrayStudentRelation = [
                                            'srname'              => $row['Name'],
                                            'srclassesID'         => $classID,
                                            'srclasses'           => $setClasses,
                                            'srroll'              => $row['Roll'],
                                            'srregisterNO'        => $row['RegistrationNO'],
                                            'srsectionID'         => $sections->sectionID,
                                            'srsection'           => $setSection,
                                            'srstudentgroupID'    => $group->studentgroupID,
                                            'sroptionalsubjectID' => $optionalSubject->subjectID,
                                        ];
                                        $this->studentrelation_m->update_studentrelation_with_multicondition($arrayStudentRelation, [
                                            'srstudentID'    => $studentID,
                                            'srschoolyearID' => $this->session->userdata('defaultschoolyearID')
                                        ]);
                                    }

                                    $studentExtend = $this->studentextend_m->get_single_studentextend(['studentID' => $studentID]);
                                    if (!customCompute($studentExtend)) {
                                        $studentExtendArray = [
                                            'studentID'                 => $studentID,
                                            'studentgroupID'            => $group->studentgroupID,
                                            'optionalsubjectID'         => $optionalSubject->subjectID,
                                            'extracurricularactivities' => NULL,
                                            'remarks'                   => NULL
                                        ];
                                        $this->studentextend_m->insert_studentextend($studentExtendArray);
                                    } else {
                                        $studentExtendArray = [
                                            'studentID'                 => $studentID,
                                            'studentgroupID'            => $group->studentgroupID,
                                            'optionalsubjectID'         => $optionalSubject->subjectID,
                                            'extracurricularactivities' => NULL,
                                            'remarks'                   => NULL
                                        ];
                                        $this->studentextend_m->update_studentextend($studentExtendArray, $studentExtend->studentextendID);
                                    }
                                } else {
                                    $msg .= $i . ". " . $row['Name'] . " is not added! , ";
                                    $msg .= implode(' , ', $singlestudentCheck['error']);
                                    $msg .= ". <br/>";
                                }
                            } else {
                                $this->session->set_flashdata('error', "Wrong csv file!");
                                redirect(base_url("bulkimport/index"));
                            }
                            $i++;
                        }
                        if ($msg != "") {
                            $this->session->set_flashdata('msg', $msg);
                        }
                        $this->session->set_flashdata('success', 'Success');
                        redirect(base_url("bulkimport/index"));
                    } else {
                        $this->session->set_flashdata('error', $this->lang->line('bulkimport_data_not_found'));
                        redirect(base_url("bulkimport/index"));
                    }
                } else {
                    $this->session->set_flashdata('error', "Wrong csv file!");
                    redirect(base_url("bulkimport/index"));
                }
            }
        } else {
            $this->session->set_flashdata('error', $this->lang->line('bulkimport_select_file'));
            redirect(base_url("bulkimport/index"));
        }
    }

    public function question_bulkimport()
    {
        if (isset($_FILES["csvQuestion"])) {
            $config['upload_path']   = "./uploads/csv/";
            $config['allowed_types'] = 'text/plain|text/csv|csv';
            $config['max_size']      = '2048';
            $config['file_name']     = $_FILES["csvQuestion"]['name'];
            $config['overwrite']     = TRUE;
            $this->load->library('upload', $config);
            if (!$this->upload->do_upload("csvQuestion")) {
                $this->session->set_flashdata('error', $this->lang->line('bulkimport_upload_fail'));
                redirect(base_url("bulkimport/index"));
            } else {
                $file_data      = $this->upload->data();
                $file_path      = './uploads/csv/' . $file_data['file_name'];
                $column_headers = [
                    "Question Group",
                    "Difficulty Level",
                    "Question",
                    "Explanation",
                    "Hints",
                    "Mark",
                    "Question Type",
                    "Total Option",
                    "Options",
                    "Correct Answer"
                ];

                if ($csv_array = @$this->csvimport->get_array($file_path, $column_headers)) {
                    if (customCompute($csv_array)) {
                        $i       = 1;
                        $msg     = "";
                        $csv_col = [];
                        foreach ($csv_array as $row) {
                            if ($i == 1) {
                                $csv_col = array_keys($row);
                            }
                            $match = array_diff($column_headers, $csv_col);
                            if (customCompute($match) <= 0) {
                                $array               = $this->arrayToPost($row);
                                $singleQuestionCheck = $this->singleQuestionCheck($array);
                                if ($singleQuestionCheck['status']) {
                                    $levelID      = $this->get_id($row['Difficulty Level'], 'level');
                                    $groupID      = $this->get_id($row['Question Group'], 'group');
                                    $typeID       = $this->get_id($row['Question Type'], 'type');
                                    $insert_data  = [
                                        'question'          => $row['Question'],
                                        'explanation'       => $row['Explanation'],
                                        'levelID'           => $levelID,
                                        'groupID'           => $groupID,
                                        'totalOption'       => $row['Total Option'],
                                        'typeNumber'        => $typeID,
                                        'mark'              => $row['Mark'],
                                        'hints'             => $row['Hints'],
                                        "create_date"       => date("Y-m-d h:i:s"),
                                        "modify_date"       => date("Y-m-d h:i:s"),
                                        "create_userID"     => $this->session->userdata('loginuserID'),
                                        "create_usertypeID" => $this->session->userdata('usertypeID'),
                                    ];
                                    $questionID   = $this->question_bank_m->insert_question_bank($insert_data);
                                    $questionType = $this->get_id($row['Question Type'], 'type');
                                    $totalOption  = $row['Total Option'];
                                    $options      = explode(',', (string) $row['Options']);
                                    $totalAnswers = explode(',', (string) $row['Correct Answer']);
                                    $answers      = [];
                                    foreach ($options as $key => $option) {
                                        foreach ($totalAnswers as $singleAnswer) {
                                            if ($singleAnswer === $option) {
                                                $answers[$key] = $singleAnswer;
                                            }
                                        }
                                    }
                                    if ($questionType == 1 || $questionType == 2) {

                                        $getQuestionOptions = pluck($this->question_option_m->get_order_by_question_option(['questionID' => $questionID]), 'optionID');

                                        if (!customCompute($getQuestionOptions)) {
                                            foreach (range(1, 10) as $optionID) {
                                                $data                 = [
                                                    'name'       => '',
                                                    'questionID' => $questionID
                                                ];
                                                $getQuestionOptions[] = $this->question_option_m->insert_question_option($data);
                                            }
                                        }

                                        foreach ($options as $key => $option) {
                                            if ($option == '') {
                                                $totalOption--;
                                                continue;
                                            }

                                            $data = [
                                                'name' => $option,
                                            ];

                                            $this->question_option_m->update_question_option($data, $getQuestionOptions[$key]);

                                            if (array_key_exists($key, $answers)) {
                                                $ansData = [
                                                    'questionID' => $questionID,
                                                    'optionID'   => $getQuestionOptions[$key],
                                                    'typeNumber' => $questionType
                                                ];
                                                $this->question_answer_m->insert_question_answer($ansData);
                                            }
                                        }

                                        if ($totalOption != $row['Total Option']) {
                                            $this->question_bank_m->update_question_bank(['totalOption' => $totalOption], $questionID);
                                        }
                                    } elseif ($questionType == 3) {
                                        foreach ($answers as $answer) {
                                            if ($answer === '') {
                                                $totalOption--;
                                                continue;
                                            }
                                            $ansData = [
                                                'questionID' => $questionID,
                                                'text'       => $answer,
                                                'typeNumber' => $questionType
                                            ];
                                            $this->question_answer_m->insert_question_answer($ansData);
                                        }
                                        if ($totalOption != $row['Total Option']) {
                                            $this->question_bank_m->update_question_bank(['totalOption' => $totalOption], $questionID);
                                        }
                                    }
                                } else {
                                    $msg .= $i . ". " . $row['Question'] . " is not added! , ";
                                    $msg .= implode(' , ', $singleQuestionCheck['error']);
                                    $msg .= ". <br/>";
                                }
                            } else {
                                $this->session->set_flashdata('error', "Wrong csv file!");
                                redirect(base_url("bulkimport/index"));
                            }
                            $i++;
                        }
                        if ($msg != "") {
                            $this->session->set_flashdata('msg', $msg);
                            $this->session->set_flashdata('error', $this->lang->line('bulkimport_error'));
                            redirect(base_url("bulkimport/index"));
                        }
                        $this->session->set_flashdata('success', $this->lang->line('bulkimport_success'));
                        redirect(base_url("bulkimport/index"));
                    } else {
                        $this->session->set_flashdata('error', $this->lang->line('bulkimport_data_not_found'));
                        redirect(base_url("bulkimport/index"));
                    }
                } else {
                    $this->session->set_flashdata('error', "Wrong csv file!");
                    redirect(base_url("bulkimport/index"));
                }
            }
        } else {
            $this->session->set_flashdata('error', $this->lang->line('bulkimport_select_file'));
            redirect(base_url("bulkimport/index"));
        }
    }

    // Single  Validation Check
    private function singleteacherCheck($array)
    {
        $name        = $this->trim_required_string_maxlength_minlength_Check($array['name'], 60);
        $designation = $this->trim_required_string_maxlength_minlength_Check($array['designation'], 128);
        $dob         = $this->trim_required_date_Check($array['dob']);
        $gender      = $this->trim_required_string_maxlength_minlength_Check($array['gender'], 10);
        $religion    = $this->trim_required_string_maxlength_minlength_Check($array['religion'], 25);
        $email       = $this->trim_check_unique_email($array['email']);
        $phone       = $this->trim_required_string_maxlength_minlength_Check($array['phone'], 25, 5);
        $address     = $this->trim_required_string_maxlength_minlength_Check($array['address'], 200);
        $jod         = $this->trim_required_date_Check($array['jod']);
        $username    = $this->trim_check_unique_username($array['username']);
        $password    = $this->trim_required_string_maxlength_minlength_Check($array['password'], 40);

        $retArray['status'] = TRUE;
        if ($name && $designation && $dob && $gender && $religion && $email && $phone && $address && $jod && $username && $password) {
            $retArray['status'] = TRUE;
        } else {
            $retArray['status'] = FALSE;
            if (!$name) {
                $retArray['error']['name'] = 'Invalid Teacher Name';
            }
            if (!$designation) {
                $retArray['error']['designation'] = 'Invalid Designation';
            }
            if (!$dob) {
                $retArray['error']['dob'] = 'Invalid Date Of Birth';
            }
            if (!$gender) {
                $retArray['error']['gender'] = 'Invalid Gender';
            }
            if (!$religion) {
                $retArray['error']['religion'] = 'Invalid Riligion';
            }
            if (!$email) {
                $retArray['error']['email'] = 'Invalid email address or email address already exists.';
            }
            if (!$phone) {
                $retArray['error']['phone'] = 'Invalid Phone Number';
            }
            if (!$address) {
                $retArray['error']['address'] = 'Invalid Address';
            }
            if (!$jod) {
                $retArray['error']['jod'] = 'Invalid Date Of Birth';
            }
            if (!$username) {
                $retArray['error']['username'] = 'Invalid username or username already exists';
            }
            if (!$password) {
                $retArray['error']['password'] = 'Invalid Password';
            }
        }
        return $retArray;
    }

    private function singleparentCheck($array)
    {
        $name              = $this->trim_required_string_maxlength_minlength_Check($array['name'], 60);
        $father_name       = $this->trim_required_string_maxlength_minlength_Check($array['father_name'], 60);
        $mother_name       = $this->trim_required_string_maxlength_minlength_Check($array['mother_name'], 40);
        $father_profession = $this->trim_required_string_maxlength_minlength_Check($array['father_profession'], 40);
        $mother_profession = $this->trim_required_string_maxlength_minlength_Check($array['mother_profession'], 40);
        $email             = $this->trim_check_unique_email($array['email']);
        $phone             = $this->trim_required_string_maxlength_minlength_Check($array['phone'], 25, 5);
        $address           = $this->trim_required_string_maxlength_minlength_Check($array['address'], 200);
        $username          = $this->trim_check_unique_username($array['username']);
        $password          = $this->trim_required_string_maxlength_minlength_Check($array['password'], 40, 4);

        $retArray['status'] = TRUE;
        if ($name && $father_name && $mother_name && $father_profession && $mother_profession && $email && $phone && $address && $username && $password) {
            $retArray['status'] = TRUE;
        } else {
            $retArray['status'] = FALSE;
            if (!$name) {
                $retArray['error']['name'] = 'Invalid Parent Name';
            }
            if (!$father_name) {
                $retArray['error']['father_name'] = 'Invalid Father Name';
            }
            if (!$mother_name) {
                $retArray['error']['mother_name'] = 'Invalid Mother Name';
            }
            if (!$father_profession) {
                $retArray['error']['father_profession'] = 'Invalid Father Profession';
            }
            if (!$mother_profession) {
                $retArray['error']['mother_profession'] = 'Invalid Mother Profession';
            }
            if (!$email) {
                $retArray['error']['email'] = 'Invalid email address or email address already exists.';
            }
            if (!$phone) {
                $retArray['error']['phone'] = 'Invalid Phone Number';
            }
            if (!$address) {
                $retArray['error']['address'] = 'Invalid Address';
            }
            if (!$username) {
                $retArray['error']['username'] = 'Invalid username or username already exists';
            }
            if (!$password) {
                $retArray['error']['password'] = 'Invalid Password';
            }
        }
        return $retArray;
    }

    private function singleuserCheck($array)
    {
        $name     = $this->trim_required_string_maxlength_minlength_Check($array['name'], 60);
        $dob      = $this->trim_required_date_Check($array['dob']);
        $gender   = $this->trim_required_string_maxlength_minlength_Check($array['gender'], 10);
        $religion = $this->trim_required_string_maxlength_minlength_Check($array['religion'], 25);
        $email    = $this->trim_check_unique_email($array['email']);
        $phone    = $this->trim_required_string_maxlength_minlength_Check($array['phone'], 25, 5);
        $address  = $this->trim_required_string_maxlength_minlength_Check($array['address'], 200);
        $jod      = $this->trim_required_date_Check($array['jod']);
        $username = $this->trim_check_unique_username($array['username']);
        $password = $this->trim_required_string_maxlength_minlength_Check($array['password'], 40);
        $usertype = $this->trim_check_usertype($array['usertype']);

        $retArray['status'] = TRUE;
        if ($name && $dob && $gender && $religion && $email && $phone && $address && $jod && $username && $password && $usertype) {
            $retArray['status'] = TRUE;
        } else {
            $retArray['status'] = FALSE;
            if (!$name) {
                $retArray['error']['name'] = 'Invalid User Name';
            }
            if (!$dob) {
                $retArray['error']['dob'] = 'Invalid Date Of Birth';
            }
            if (!$gender) {
                $retArray['error']['gender'] = 'Invalid Gender';
            }
            if (!$religion) {
                $retArray['error']['religion'] = 'Invalid Riligion';
            }
            if (!$email) {
                $retArray['error']['email'] = 'Invalid email address or email address already exists.';
            }
            if (!$phone) {
                $retArray['error']['phone'] = 'Invalid Phone Number';
            }
            if (!$address) {
                $retArray['error']['address'] = 'Invalid Address';
            }
            if (!$jod) {
                $retArray['error']['jod'] = 'Invalid Date Of Birth';
            }
            if (!$username) {
                $retArray['error']['username'] = 'Invalid username or username already exists';
            }
            if (!$password) {
                $retArray['error']['password'] = 'Invalid Password';
            }
            if (!$usertype) {
                $retArray['error']['usertype'] = 'Invalid Usertype';
            }
        }
        return $retArray;
    }

    private function singlebookCheck($array)
    {
        $book         = $this->trim_required_string_maxlength_minlength_Check($array['book'], 60);
        $price        = $this->trim_required_int_maxlength_minlength_Check($array['price'], 10);
        $rack         = $this->trim_required_string_maxlength_minlength_Check($array['rack'], 60);
        $author       = $this->trim_required_string_maxlength_minlength_Check($array['author'], 100);
        $quantity     = $this->trim_required_int_maxlength_minlength_Check($array['quantity'], 10);
        $subject_code = $this->trim_required_string_maxlength_minlength_Check($array['subject_code'], 20);

        $retArray['status'] = TRUE;
        if ($book && $price && $rack && $author && $quantity && $subject_code) {
            $books = $this->book_m->get_single_book([
                'LOWER(book)'         => strtolower((string) $book),
                'LOWER(author)'       => strtolower((string) $author),
                'LOWER(subject_code)' => strtolower((string) $subject_code)
            ]);
            if (customCompute($books)) {
                $retArray['status']        = FALSE;
                $retArray['error']['book'] = 'Book already exits';
            } else {
                $retArray['status'] = TRUE;
            }
        } else {
            $retArray['status'] = FALSE;
            if (!$book) {
                $retArray['error']['book'] = 'Invalid Book Name';
            }
            if (!$price) {
                $retArray['error']['price'] = 'Price are not valid';
            }
            if (!$rack) {
                $retArray['error']['rack'] = 'Rack are not valid';
            }
            if (!$author) {
                $retArray['error']['author'] = 'Author are not valid';
            }
            if (!$quantity) {
                $retArray['error']['quantity'] = 'Quantity are not valid';
            }
            if (!$subject_code) {
                $retArray['error']['subject_code'] = 'Subject Code are not valid';
            }
        }
        return $retArray;
    }

    public function singlestudentCheck($array)
    {
        $name            = $this->trim_required_string_maxlength_minlength_Check($array['name'], 60);
        $dob             = $this->trim_required_date_Check($array['dob']);
        $gender          = $this->trim_required_string_maxlength_minlength_Check($array['gender'], 10);
        $religion        = $this->trim_required_string_maxlength_minlength_Check($array['religion'], 25);
        $email           = $this->trim_check_unique_email($array['email']);
        $phone           = $this->trim_required_string_maxlength_minlength_Check($array['phone'], 25, 5);
        $address         = $this->trim_required_string_maxlength_minlength_Check($array['address'], 200);
        $class           = $this->trim_required_class_Check($array['class']);
        $section         = $this->trim_required_section_Check($array['class'], $array['section']);
        $username        = $this->trim_check_unique_username($array['username']);
        $password        = $this->trim_required_string_maxlength_minlength_Check($array['password'], 40);
        $roll            = $this->trim_roll_Check($array);
        $bloodgroup      = $this->trim_required_string_maxlength_minlength_Check($array['bloodgroup'], 5);
        $state           = $this->trim_required_string_maxlength_minlength_Check($array['state'], 128);
        $country         = $this->trim_required_string_maxlength_minlength_Check($array['country'], 128);
        $registrationno  = $this->trim_required_registration_Check($array['registrationno']);
        $group           = $this->trim_group_Check($array['group']);
        $optionalsubject = $this->trim_optionalsubject_Check($array['optionalsubject'], $array['class']);

        $checkStudent = $this->trim_check_section_student($array);

        $retArray['status'] = FALSE;
        if ($name && $dob && $gender && $religion && $email && $phone && $address && $class && $section && $username && $password && $roll && $bloodgroup && $state && $country && $registrationno && $group && $optionalsubject && $checkStudent) {
            $retArray['status'] = TRUE;
        } else {
            if (!$name) {
                $retArray['error']['name'] = 'Invalid Teacher Name';
            }
            if (!$dob) {
                $retArray['error']['dob'] = 'Invalid Date Of Birth';
            }
            if (!$gender) {
                $retArray['error']['gender'] = 'Invalid Gender';
            }
            if (!$religion) {
                $retArray['error']['religion'] = 'Invalid Riligion';
            }
            if (!$email) {
                $retArray['error']['email'] = 'Invalid email address or email address already exists.';
            }
            if (!$phone) {
                $retArray['error']['phone'] = 'Invalid Phone Number';
            }
            if (!$address) {
                $retArray['error']['address'] = 'Invalid Address';
            }
            if (!$class) {
                $retArray['error']['class'] = 'Invalid Class';
            }
            if (!$section) {
                $retArray['error']['section'] = 'Invalid Section';
            }
            if (!$username) {
                $retArray['error']['username'] = 'Invalid username or username already exists';
            }
            if (!$password) {
                $retArray['error']['password'] = 'Invalid Password';
            }
            if (!$roll) {
                $retArray['error']['roll'] = 'Invalid roll or roll already exists in class';
            }
            if (!$bloodgroup) {
                $retArray['error']['bloodgroup'] = 'Invalid bloodgroup';
            }
            if (!$state) {
                $retArray['error']['state'] = 'Invalid state';
            }
            if (!$country) {
                $retArray['error']['country'] = 'Invalid country';
            }
            if (!$registrationno) {
                $retArray['error']['registrationno'] = 'Invalid registration no or registration no already exists';
            }
            if (!$group) {
                $retArray['error']['group'] = 'Invalid Group';
            }
            if (!$optionalsubject) {
                $retArray['error']['optionalsubject'] = 'Invalid OptionalSubject Subject';
            }
            if (!$checkStudent) {
                $retArray['error']['checkStudent'] = 'Student can not add in section';
            }
        }
        return $retArray;
    }

    private function singleQuestionCheck($array)
    {
        $groupID            = $this->trim_required_exists_Check($array['question_group'], 'group');
        $levelID            = $this->trim_required_exists_Check($array['difficulty_level'], 'level');
        $question           = $this->trim_required_string_maxlength_minlength_Check($array['question'], 1000);
        $explanation        = $this->trim_string_maxlength_minlength_Check($array['explanation'], 1000);
        $hints              = $this->trim_string_maxlength_minlength_Check($array['hints'], 1000);
        $mark               = $this->trim_required_string_maxlength_minlength_Check($array['mark'], 40);
        $typeNumber         = $this->trim_required_exists_Check($array['question_type'], 'type');
        $totalOption        = $this->trim_int_maxlength_minlength_Check($array['total_option'], 10, 1);
        $options            = $this->trim_unique_required_string_maxlength_minlength_Check($array['options'], $array, 'options', 10, 1);
        $correctAnswers     = $this->trim_unique_required_string_maxlength_minlength_Check($array['correct_answer'], $array, 'answers', 10, 1);
        $retArray['status'] = TRUE;
        if ($groupID && $levelID && $question && $explanation && $hints && $mark && $typeNumber && $totalOption && $options && $correctAnswers) {
            $retArray['status'] = TRUE;
        } else {
            $retArray['status'] = FALSE;
            if (!$groupID) {
                $retArray['error']['groupID'] = 'Invalid Group';
            }
            if (!$levelID) {
                $retArray['error']['levelID'] = 'Invalid Level';
            }
            if (!$question) {
                $retArray['error']['question'] = 'Invalid Question';
            }
            if (!$explanation) {
                $retArray['error']['explanation'] = 'Invalid Explanation';
            }
            if (!$hints) {
                $retArray['error']['hints'] = 'Invalid hints';
            }
            if (!$mark) {
                $retArray['error']['mark'] = 'Invalid Marking';
            }
            if (!$typeNumber) {
                $retArray['error']['typeNumber'] = 'Invalid Question Type';
            }
            if (!$totalOption) {
                $retArray['error']['totalOption'] = 'Invalid Total Option';
            }
            if (!$options) {
                $retArray['error']['options'] = 'Invalid Options';
            }
            if (!$correctAnswers) {
                $retArray['error']['correctAnswers'] = 'Invalid Correct Answers';
            }
        }
        return $retArray;
    }

    // Student Valiadtion Check
    private function trim_check_section_student($array)
    {
        $classes = strtolower(trim((string) $array['class']));
        $section = strtolower(trim((string) $array['section']));

        if ($classes && $section) {
            $result = $this->classes_m->general_get_single_classes(['LOWER(classes)' => $classes]);
            if (customCompute($result)) {
                $result = $this->section_m->general_get_single_section([
                    'classesID'      => $result->classesID,
                    'LOWER(section)' => $section
                ]);
                if (customCompute($result)) {
                    $capacity     = $result->capacity;
                    $schoolyearID = $this->session->userdata('defaultschoolyearID');
                    $students     = $this->studentrelation_m->general_get_order_by_student([
                        'srclassesID'    => $result->classesID,
                        'srsectionID'    => $result->sectionID,
                        'srschoolyearID' => $schoolyearID
                    ]);
                    $totalStudent = customCompute($students);
                    if ($totalStudent <= $capacity) {
                        return TRUE;
                    }
                }
            }
        }
        return FALSE;
    }

    private function trim_required_registration_Check($data)
    {
        $data = trim((string) $data);
        if ($data !== '' && $data !== '0') {
            $student = $this->studentrelation_m->general_get_single_student(["srregisterNO" => $data]);
            if (customCompute($student)) {
                return FALSE;
            } else {
                return $data;
            }
        }
        return FALSE;
    }

    private function trim_roll_Check($data)
    {
        $roll    = trim((string) $data['roll']);
        $classes = strtolower(trim((string) $data['class']));
        if ($roll && $classes) {
            $result = $this->classes_m->general_get_single_classes(['LOWER(classes)' => $classes]);
            if (customCompute($result)) {
                $schoolyearID = $this->session->userdata('defaultschoolyearID');
                $student      = $this->studentrelation_m->general_get_order_by_student([
                    "srroll"         => $roll,
                    "srclassesID"    => $result->classesID,
                    'srschoolyearID' => $schoolyearID
                ]);
                if (customCompute($student)) {
                    return FALSE;
                } else {
                    return $roll;
                }
            }
        }
        return FALSE;
    }

    private function trim_optionalsubject_Check($subject, $classes)
    {
        if ($subject == '' || $subject == 0) {
            $array = [
                'subjectID' => 0,
                'subject'   => ''
            ];
            return (object)$array;
        } else {
            $subject = strtolower(trim((string) $subject));
            $classes = strtolower(trim((string) $classes));
            if ($subject && $classes) {
                $result = $this->classes_m->general_get_single_classes(['LOWER(classes)' => $classes]);
                if (customCompute($result)) {
                    $result = $this->subject_m->general_get_single_subject([
                        'classesID'      => $result->classesID,
                        'type'           => 0,
                        'LOWER(subject)' => $subject
                    ]);
                    if (customCompute($result)) {
                        return $result;
                    }
                }
            }
            return FALSE;
        }
    }

    private function trim_required_class_Check($classes)
    {
        $classes = strtolower(trim((string) $classes));
        if ($classes !== '' && $classes !== '0') {
            $result = $this->classes_m->general_get_single_classes(['LOWER(classes)' => $classes]);
            if (customCompute($result)) {
                return $result;
            }
        }
        return FALSE;
    }

    private function trim_group_Check($group)
    {
        $group1 = strtolower(trim((string) $group));
        $group2 = trim((string) $group);
        if ($group1 && $group2) {
            $result1 = $this->studentgroup_m->get_single_studentgroup(['group' => $group1]);
            $result2 = $this->studentgroup_m->get_single_studentgroup(['group' => $group2]);
            if (customCompute($result1)) {
                return $result1;
            } elseif (customCompute($result2)) {
                return $result2;
            }
        }
        return FALSE;
    }

    private function trim_required_section_Check($classes, $section)
    {
        $classes = strtolower(trim((string) $classes));
        $section = strtolower(trim((string) $section));
        if ($classes && $section) {
            $result = $this->classes_m->general_get_single_classes(['LOWER(classes)' => $classes]);
            if (customCompute($result)) {
                $result = $this->section_m->general_get_single_section([
                    'classesID'      => $result->classesID,
                    'LOWER(section)' => $section
                ]);
                if (customCompute($result)) {
                    return $result;
                }
            }
        }
        return FALSE;
    }

    // User Validation Check
    private function trim_check_usertype($usertype)
    {
        $usertype = strtolower(trim((string) $usertype));
        if ($usertype !== '' && $usertype !== '0') {
            $result = $this->usertype_m->get_single_usertype(['LOWER(usertype)' => $usertype]);
            if (customCompute($result)) {
                $usertypeID   = $result->usertypeID;
                $blockuserArr = [1, 2, 3, 4];
                if (in_array($usertypeID, $blockuserArr)) {
                    return FALSE;
                } else {
                    return $usertypeID;
                }
            }
        }
        return FALSE;
    }

    // Username and Email Validation Check
    private function trim_check_unique_username($data)
    {
        $data = (string)trim((string) $data);
        if ($data !== '' && $data !== '0') {
            $tables = ['student', 'parents', 'teacher', 'user', 'systemadmin'];
            $i      = 0;
            $array  = [];
            foreach ($tables as $table) {
                $user = $this->student_m->get_username($table, ["username" => $data]);
                $array['permition'][$i] = customCompute($user) ? 'no' : 'yes';
                $i++;
            }

            if (in_array('no', $array['permition'])) {
                return FALSE;
            } else {
                return $data;
            }
        }
        return FALSE;
    }

    private function trim_check_unique_email($data)
    {
        $data = trim((string) $data);
        if (filter_var($data, FILTER_VALIDATE_EMAIL)) {
            $tables = ['student', 'parents', 'teacher', 'user', 'systemadmin'];
            $array  = [];
            $i      = 0;
            foreach ($tables as $table) {
                $user = $this->student_m->get_username($table, ["email" => $data]);
                $array['permition'][$i] = customCompute($user) ? 'no' : 'yes';
                $i++;
            }
            if (in_array('no', $array['permition'])) {
                return FALSE;
            } else {
                return $data;
            }
        }
        return FALSE;
    }

    // Default Function All Import Validation Check
    public function arrayToPost($data)
    {
        if (is_array($data)) {
            $post = [];
            foreach ($data as $key => $item) {
                $key        = preg_replace('/\s+/', '_', $key);
                $key        = strtolower($key);
                $post[$key] = $item;
            }
            return $post;
        }
        return [];
    }

    private function trim_required_string_maxlength_minlength_Check($data, $maxlength = 10, $minlength = 0)
    {
        $data       = (string)trim((string) $data);
        $dataLength = strlen($data);

        if (($dataLength == 0) || ($dataLength > $maxlength) || ($dataLength < $minlength)) {
            return FALSE;
        } else {
            if (is_string($data)) {
                return $data;
            }
            return FALSE;
        }
    }

    private function trim_required_int_maxlength_minlength_Check($data, $maxlength = 10, $minlength = 0)
    {
        $data       = (int)trim((string) $data);
        $dataLength = strlen($data);

        if (($dataLength == 0) || ($dataLength > $maxlength) || ($dataLength < $minlength)) {
            return FALSE;
        } else {
            if (is_int($data)) {
                return $data;
            }
            return FALSE;
        }
    }

    private function trim_required_date_Check($date)
    {
        $date = trim((string) $date);
        if ($date !== '' && $date !== '0') {
            $date = str_replace('/', '-', $date);
            return date("Y-m-d", strtotime($date));
        }
        return FALSE;
    }

    private function trim_required_convertdate($date)
    {
        $date = trim((string) $date);
        if ($date !== '' && $date !== '0') {
            $date = str_replace('/', '-', $date);
            return date("Y-m-d", strtotime($date));
        }
        return 0;
    }

    // For Only Student Import Check Query
    public function get_student_class($classes)
    {
        $classes = strtolower(trim((string) $classes));
        if ($classes !== '' && $classes !== '0') {
            $result = $this->classes_m->general_get_single_classes(['LOWER(classes)' => $classes]);
            if (customCompute($result)) {
                return $result->classesID;
            }
        }
        return 0;
    }

    public function get_student_section($classesID, $section)
    {
        $section = strtolower(trim((string) $section));
        if ($classesID) {
            $result = $this->section_m->general_get_single_section([
                'classesID'      => $classesID,
                'LOWER(section)' => $section
            ]);
            if (customCompute($result)) {
                return $result;
            }
        }
        return 0;
    }

    public function get_student_group($group)
    {
        $group1 = strtolower(trim((string) $group));
        $group2 = trim((string) $group);
        if ($group1 && $group2) {
            $result1 = $this->studentgroup_m->get_single_studentgroup(['group' => $group1]);
            $result2 = $this->studentgroup_m->get_single_studentgroup(['group' => $group2]);
            if (customCompute($result1)) {
                return $result1;
            } elseif (customCompute($result2)) {
                return $result2;
            }
        }
        $array = [
            'studentgroupID' => 0,
            'group'          => ''
        ];
        return (object)$array;
    }

    public function get_student_optional_subject($classesID, $subject)
    {
        $subject = strtolower(trim((string) $subject));
        if ($subject !== '' && $subject !== '0') {
            $result = $this->subject_m->general_get_single_subject([
                'classesID'      => $classesID,
                'type'           => 0,
                'LOWER(subject)' => $subject
            ]);
            if (customCompute($result)) {
                return $result;
            }
        }
        $array = [
            'subjectID' => 0,
            'subject'   => ''
        ];
        return (object)$array;
    }

    public function get_student_country($country)
    {
        $countryArr = $this->getAllCountry();
        $key        = array_search($country, $countryArr);
        return ($key) ? $key : 0;
    }

    // Question import only
    private function trim_required_exists_Check($data, $type)
    {
        $data = trim((string) $data);
        if ($data == '') {
            return false;
        }
        return $this->get_id($data, $type);
    }

    private function get_id($data, $type)
    {
        if ($type == 'group') {
            $group = $this->question_group_m->get_single_question_group(['title' => $data]);
            if ($group) {
                return $group->questionGroupID;
            }
            return false;
        } elseif ($type == 'level') {
            $level = $this->question_level_m->get_single_question_level(['name' => $data]);
            if ($level) {
                return $level->questionLevelID;
            }
            return false;
        } else {
            $type = $this->question_type_m->get_single_question_type(['name' => $data]);
            if ($type) {
                return $type->typeNumber;
            }
            return false;
        }
    }

    private function trim_string_maxlength_minlength_Check($data, $maxlength = 10)
    {
        $data       = (string)trim((string) $data);
        $dataLength = strlen($data);
        return $dataLength <= $maxlength;
    }

    private function trim_int_maxlength_minlength_Check($data, $maxlength = 10, $minlength = 1)
    {
        $data       = (int)trim((string) $data);
        $dataLength = strlen($data);

        if (($dataLength > $maxlength) || ($dataLength < $minlength)) {
            return FALSE;
        } else {
            if (is_int($data)) {
                return $data;
            }
            return FALSE;
        }
    }

    private function trim_unique_required_string_maxlength_minlength_Check($data, $array, $type, $maxlength = 10, $minlength = 1)
    {

        $data = trim((string) $data);
        if ($data === '') {
            return false;
        }

        //newArray is either options or answers
        $newArray = explode(',', $data);

        if (customCompute($newArray) > $maxlength) {
            return false;
        }

        $totalOption  = $array['total_option'];
        $questionType = $this->get_id($array['question_type'], 'type');
        if ($type == 'options') {
            if ((customCompute($newArray) != (int)$totalOption) || (customCompute($newArray) !== customCompute(array_unique($newArray)))) {
                return false;
            }
        } elseif ($questionType == 1) {
            if (customCompute($newArray) > 1) {
                return false;
            }
        } elseif ($questionType == 2) {
            if ((customCompute($newArray) !== customCompute(array_unique($newArray)) || ((customCompute($newArray)) > $totalOption))) {
                return false;
            }
        } elseif (customCompute($newArray) < 1) {
            return false;
        }
        return $data;
    }
}
