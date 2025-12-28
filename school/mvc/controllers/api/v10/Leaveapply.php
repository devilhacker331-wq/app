<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') || exit('No direct script access allowed');

class Leaveapply extends Api_Controller
{
    public $load;
    public $session;
    public $retdata;
    public $leavecategory_m;
    public $leaveapplication_m;
    public $leaveassign_m;
    public $upload_data;
    public $upload;
    public $retdata2;
    public $data;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('leaveapplication_m');
        $this->load->model('leavecategory_m');
        $this->load->model('usertype_m');
        $this->load->model('leaveassign_m');
        $this->load->model("systemadmin_m");
        $this->load->model("teacher_m");
        $this->load->model("student_m");
        $this->load->model("parents_m");
        $this->load->model("user_m");
        $this->load->model("studentrelation_m");
        $this->lang->load('leaveapply', $this->data['language']);

    }

    public function index_get()
    {
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $this->retdata['leaveapplications'] = $this->leaveapplication_m->get_order_by_leaveapply_with_user(array('leaveapplications.schoolyearID' => $schoolyearID, 'leaveapplications.create_usertypeID' => $this->session->userdata('usertypeID'), 'leaveapplications.create_userID' => $this->session->userdata('loginuserID')));
        $this->retdata['leavecategorys'] = pluck($this->leavecategory_m->get_leavecategory(), 'leavecategory', 'leavecategoryID');
        $this->response([
            'status' => true,
            'message' => 'Success',
            'data' => $this->retdata,
        ], REST_Controller::HTTP_OK);
    }

    public function view_get($id = null)
    {
        if ((int) $id !== 0) {
            $schoolyearID = $this->session->userdata("defaultschoolyearID");
            $this->retdata['usertypes'] = pluck($this->usertype_m->get_usertype(), 'usertype', 'usertypeID');
            $this->retdata['leaveapply'] = $this->leaveapplication_m->get_single_leaveapplication(array('leaveapplicationID' => $id, 'schoolyearID' => $schoolyearID));

            if (customCompute($this->retdata['leaveapply'])) {
                if (($this->retdata['leaveapply']->create_userID == $this->session->userdata('loginuserID')) && ($this->retdata['leaveapply']->create_usertypeID == $this->session->userdata('usertypeID'))) {

                    $leavecategory = $this->leavecategory_m->get_single_leavecategory(array('leavecategoryID' => $this->retdata['leaveapply']->leavecategoryID));
                    $this->retdata['leaveapply']->category = customCompute($leavecategory) ? $leavecategory->leavecategory : '';

                    $availableleave = $this->leaveapplication_m->get_sum_of_leave_days_by_user_for_single_category($this->session->userdata('usertypeID'), $this->session->userdata('loginuserID'), $schoolyearID, $this->retdata['leaveapply']->leavecategoryID);
                    $availableleavedays = isset($availableleave->days) && $availableleave->days > 0 ? $availableleave->days : 0;

                    $leaveassign = $this->leaveassign_m->get_single_leaveassign(array('leavecategoryID' => $this->retdata['leaveapply']->leavecategoryID, 'schoolyearID' => $schoolyearID));
                    if (customCompute($leaveassign)) {
                        $this->retdata['leaveapply']->leaveavabledays = ($leaveassign->leaveassignday - $availableleavedays);
                    } else {
                        $this->retdata['leaveapply']->leaveavabledays = $this->lang->line('leaveapply_deleted');
                    }

                    $this->retdata['applicant'] = getObjectByUserTypeIDAndUserID($this->retdata['leaveapply']->create_usertypeID, $this->retdata['leaveapply']->create_userID, $schoolyearID);

                    $this->retdata['daysArray'] = $this->leavedayscustomCompute($this->retdata['leaveapply']->from_date, $this->retdata['leaveapply']->to_date);

                    $this->response([
                        'status' => true,
                        'message' => 'Success',
                        'data' => $this->retdata,
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => false,
                        'message' => 'Error 404',
                        'data' => [],
                    ], REST_Controller::HTTP_OK);
                }
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Error 404',
                    'data' => [],
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response([
                'status' => false,
                'message' => 'Error 404',
                'data' => [],
            ], REST_Controller::HTTP_OK);
        }
    }

    public function usertype_get()
    {
        $this->retdata['usertype'] = $this->usertype_m->get_usertype_by_permission_for_api('leaveapplication', 1);
        $this->response([
            'status' => true,
            'message' => 'Success',
            'data' => $this->retdata,
        ], REST_Controller::HTTP_OK);
    }

    public function applicationto_get($usertypeID)
    {
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $sessionUsertypeID = $this->session->userdata('usertypeID');
        $sessionUserID = $this->session->userdata('loginuserID');
        if ((int) $usertypeID !== 0 && (int) $usertypeID !== 3 && (int) $usertypeID !== 4) {
            $this->retdata = array();
            if ($usertypeID == 1) {
                $users = $this->systemadmin_m->get_systemadmin();
                if (customCompute($users)) {
                    foreach ($users as $value) {
                        if (!(($value->systemadminID == $sessionUserID) && $sessionUsertypeID == $usertypeID)) {
                            $this->retdata['users'][] = (object) $value;
                        }
                    }
                }
            } elseif ($usertypeID == 2) {
                $users = $this->teacher_m->get_teacher();
                if (customCompute($users)) {
                    foreach ($users as $value) {
                        if (!(($value->teacherID == $sessionUserID) && $sessionUsertypeID == $usertypeID)) {
                            $this->retdata['users'][] = (object) $value;
                        }
                    }
                }
            } else {
                $users = $this->user_m->get_order_by_user(array('usertypeID' => $usertypeID));
                if (customCompute($users)) {
                    foreach ($users as $value) {
                        if (!(($value->userID == $sessionUserID) && $sessionUsertypeID == $usertypeID)) {
                            $this->retdata['users'][] = (object) $value;
                        }
                    }
                }
            }

            $this->response([
                'status' => true,
                'message' => 'Success',
                'data' => $this->retdata,
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => false,
                'message' => 'Error 404',
                'data' => [],
            ], REST_Controller::HTTP_OK);
        }
    }

    public function leavecategory_get()
    {
        $this->retdata['leavecategories'] = $this->leavecategory_m->get_join_leavecategory_and_leaveassign($this->session->userdata('usertypeID'), $this->session->userdata('defaultschoolyearID'));
        $this->response([
            'status' => true,
            'message' => 'Success',
            'data' => $this->retdata,
        ], REST_Controller::HTTP_OK);
    }

    public function add_post() { 
 
        if (inputCall()) { 
            $rules = $this->rules();
            $this->form_validation->set_rules($rules);
            if ($this->form_validation->run() == false) {
                $this->retdata2['validation'] = $this->form_validation->error_array();
                $this->response([
                    'status' => false,
                    'message' => 'Error',
                    'data' => $this->retdata2,
                ], REST_Controller::HTTP_NOT_FOUND);
            } else {
                $array["from_date"]                = date('Y-m-d', strtotime(inputCall('from_date')));

                $array["to_date"]                  = date('Y-m-d', strtotime(inputCall('to_date')));
                $leavedaysCount                    = $this->leavedayscustomCompute(inputCall('from_date'), inputCall('to_date'));

                $array["leave_days"]               = isset($leavedaysCount['totaldayCount']) ? $leavedaysCount['totaldayCount'] : 0;

                $array["leavecategoryID"]          = inputCall('leavecategoryID');

                $array["applicationto_usertypeID"] = inputCall('applicationto_usertypeID');

                $array["applicationto_userID"]     = inputCall('applicationto_userID');
                $array["reason"]                   = inputCall('reason');

                $array["attachment"]               = $this->upload_data['file']['file_name'];
                $array["attachmentorginalname"]    = $this->upload_data['file']['original_file_name'];

                $array["from_time"]                = date('H:i:s');
                $array["to_time"]                  = date('H:i:s');
                $array["create_date"]              = date("Y-m-d H:i:s");
                $array["modify_date"]              = date("Y-m-d H:i:s");

                $array["create_userID"]            = $this->session->userdata('loginuserID');
                $array["create_usertypeID"]        = $this->session->userdata('usertypeID');
                $array['schoolyearID']             = $this->session->userdata("defaultschoolyearID");
                                                                                                                                               

                $this->leaveapplication_m->insert_leaveapplication($array);
                $this->response([
                    'status' => true,
                    'message' => 'Success',
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response([
                'status' => false,
                'message' => 'Error',
            ], REST_Controller::HTTP_NOT_FOUND);
        }

    }

    public function attachmentUpload(){ 
        $new_file = "";
        $original_file_name = '';
        if (isset($_FILES["attachment"]) && $_FILES["attachment"]['name'] != "") {
            $file_name = $_FILES["attachment"]['name'];
            $original_file_name = $file_name;
            $random = random19();
            $makeRandom = hash('sha512', $random . 'leaveapplication' . config_item("encryption_key"));
            $file_name_rename = $makeRandom;
            $explode = explode('.', (string) $file_name);
            if (customCompute($explode) >= 2) {
                $new_file = $file_name_rename . '.' . end($explode);
                $config['upload_path'] = "./uploads/images";
                $config['allowed_types'] = "gif|jpg|png|jpeg|pdf|doc|xml|docx|GIF|JPG|PNG|JPEG|PDF|DOC|XML|DOCX|xls|xlsx|txt|ppt|csv";
                $config['file_name'] = $new_file;
                $config['max_size'] = '1024';
                $config['max_width'] = '3000';
                $config['max_height'] = '3000';
                $this->load->library('upload', $config);
                if (!$this->upload->do_upload("attachment")) {
                    $this->form_validation->set_message("attachmentUpload", $this->upload->display_errors());
                    return false;
                } else {
                    $this->upload_data['file'] = $this->upload->data();
                    $this->upload_data['file']['original_file_name'] = $original_file_name;
                    return true;
                }
            } else {
                $this->form_validation->set_message("attachmentUpload", "Invalid file");
                return false;
            }
        }else {
            $this->upload_data['file'] = array('file_name' => $new_file);
            $this->upload_data['file']['original_file_name'] = $original_file_name;
            return true;
        }
    }

    protected function rules()
    {
        return array(
            array(
                'field' => 'applicationto_usertypeID',
                'label' => 'usertype',
                'rules' => 'trim|required|xss_clean|numeric|max_length[11]|callback_unique_data',
            ),
            array(
                'field' => 'applicationto_userID',
                'label' => 'user',
                'rules' => 'trim|required|xss_clean|numeric|max_length[11]|callback_unique_data',
            ),
            array(
                'field' => 'leavecategoryID',
                'label' => 'leavecategory',
                'rules' => 'trim|required|xss_clean|numeric|max_length[11]|callback_unique_data',
            ),
            array(
                'field' => 'from_date',
                'label' => 'from_date',
                'rules' => 'trim|required|xss_clean|callback_unique_data',
            ),
            array(
                'field' => 'to_date',
                'label' => 'to_date',
                'rules' => 'trim|required|xss_clean|callback_unique_data',
            ),
            array(
                'field' => 'reason',
                'label' => 'reason',
                'rules' => 'trim|required|xss_clean|max_length[10000]',
            ),
            array(
                'field' => 'attachment',
                'label' => 'attachment',
                'rules' => 'trim|max_length[200]|xss_clean|callback_attachmentUpload',
            ),
        );
    }

    public function unique_data()
    {
        if (inputCall('applicationto_usertypeID') == 0) {
           
            $this->form_validation->set_message('unique_data', 'The %s field is required.');
            return false;
        }
        return true;
    }

    public function date_schedule_valid($date)
    {
        if ($date) {
            $dateLength = strlen((string) $date);
            if ($dateLength == 23) {
                $dataArray = explode('-', (string) $date);
                $from_date = trim($dataArray[0]);
                $to_date = trim($dataArray[1]);

                if ($from_date !== '' && $from_date !== '0') {
                    if (strlen($from_date) != 10) {
                        $this->form_validation->set_message("date_schedule_valid", "The %s is not valid dd-mm-yyyy.");
                        return false;
                    } else {
                        $arr = explode("/", $from_date);
                        $dd = $arr[1];
                        $mm = $arr[0];
                        $yyyy = $arr[2];
                        if (checkdate($mm, $dd, $yyyy)) {
                            if ($to_date !== '' && $to_date !== '0') {
                                if (strlen($to_date) != 10) {
                                    $this->form_validation->set_message("date_schedule_valid", "The %s is not valid dd-mm-yyyy.");
                                    return false;
                                } else {
                                    $arr = explode("/", $to_date);
                                    $dd = $arr[1];
                                    $mm = $arr[0];
                                    $yyyy = $arr[2];
                                    if (checkdate($mm, $dd, $yyyy)) {
                                        return true;
                                    } else {
                                        $this->form_validation->set_message("date_schedule_valid", "The %s is not valid dd-mm-yyyy.");
                                        return false;
                                    }
                                }
                            } else {
                                $this->form_validation->set_message("date_schedule_valid", "The %s is not valid dd-mm-yyyy.");
                                return false;
                            }
                        } else {
                            $this->form_validation->set_message("date_schedule_valid", "The %s is not valid dd-mm-yyyy.");
                            return false;
                        }
                    }
                } else {
                    $this->form_validation->set_message("date_schedule_valid", "The %s is not valid dd-mm-yyyy.");
                    return false;
                }
            } else {
                $this->form_validation->set_message("date_schedule_valid", "The %s is not valid dd-mm-yyyy.");
                return false;
            }
        } else {
            return true;
        }
    }

    private function leavedayscustomCompute($fromdate, $todate)
    {
        $allholidayArray = $this->getHolidaysSession();
        $getweekenddayArray = $this->getWeekendDaysSession();
        $leavedays = get_day_using_two_date(strtotime((string) $fromdate), strtotime((string) $todate));

        $holidayCount = 0;
        $weekenddayCount = 0;
        $leavedayCount = 0;
        $totaldayCount = 0;
        $retArray = [];
        if (customCompute($leavedays)) {
            foreach ($leavedays as $leaveday) {
                if (in_array($leaveday, $allholidayArray)) {
                    $holidayCount++;
                } elseif (in_array($leaveday, $getweekenddayArray)) {
                    $weekenddayCount++;
                } else {
                    $leavedayCount++;
                }
                $totaldayCount++;
            }
        }

        $retArray['fromdate'] = $fromdate;
        $retArray['todate'] = $todate;
        $retArray['holidayCount'] = $holidayCount;
        $retArray['weekenddayCount'] = $weekenddayCount;
        $retArray['leavedayCount'] = $leavedayCount;
        $retArray['totaldayCount'] = $totaldayCount;
        return $retArray;
    }


}
