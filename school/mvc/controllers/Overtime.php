<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Overtime extends Admin_Controller
{
public $load;
    public $session;
    public $lang;
    public $data;
    public $input;
    public $form_validation;
    public $overtime_m;
    public $uri;
    public $user_m;
    public $manage_salary_m;
    public $salary_template_m;
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

    public $tablename = [
        '1' => 'systemadmin',
        '2' => 'teacher',
        '3' => 'user',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('usertype_m');
        $this->load->model('user_m');
        $this->load->model('overtime_m');
        $this->load->model('manage_salary_m');
        $this->load->model('salary_template_m');
        $language = $this->session->userdata('lang');
        $this->lang->load('overtime', $language);
    }

    public function index()
    {
        $this->data['overtimes'] = $this->overtime_m->get_overtime();
        $this->data['roles']     = pluck($this->usertype_m->get_usertype(), 'usertype', 'usertypeID');
        $this->data['allUsers']  = getAllUserObjectWithoutStudent();

        $this->data["subview"] = "overtime/index";
        $this->load->view('_layout_main', $this->data);
    }

    protected function rules()
    {
        return array(
            array(
                'field' => 'roleId',
                'label' => $this->lang->line("overtime_role"),
                'rules' => 'trim|required|xss_clean|numeric|callback_uniqueRole',
            ),
            array(
                'field' => 'userId',
                'label' => $this->lang->line("overtime_user"),
                'rules' => 'trim|required|xss_clean|numeric|callback_uniqueUser',
            ),
            array(
                'field' => 'overtime_date',
                'label' => $this->lang->line("overtime_date"),
                'rules' => 'trim|required|xss_clean|max_length[128]|callback_date_valid',
            ),
            array(
                'field' => 'overtime_hours',
                'label' => $this->lang->line("overtime_hours"),
                'rules' => 'trim|required|xss_clean|numeric|max_length[10]',
            ),
        );
    }

    public function add()
    {

        $this->data['headerassets'] = [
            'css' => array(
                'assets/datetimepicker/datetimepicker.css',
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css',
            ),
            'js'  => array(
                'assets/datetimepicker/moment.js',
                'assets/datetimepicker/datetimepicker.js',
                'assets/select2/select2.js',
            ),
        ];

        $this->data['roles'] = $this->usertype_m->get_usertype_for_overtime();
        $roleId              = $this->input->post("roleId");
        $userID              = $this->input->post("userId");
        $table               = '';
        $tableID             = '';
        if ($userID > 0) {
            if ($roleId == 1) {
                $table   = 'systemadmin';
                $tableID = 'systemadminID';
            } elseif ($roleId == 2) {
                $table   = 'teacher';
                $tableID = 'teacherID';
            } else {
                $table   = 'user';
                $tableID = 'userID';
            }

            $this->data['users'] = $this->user_m->get_all_user($table, array('usertypeID' => $roleId));
        } else {
            $this->data['users'] = null;
        }

        $this->data['setUserId'] = 0;

        if ($_POST !== []) {
            $rules = $this->rules();
            $this->form_validation->set_rules($rules);

            if ($this->form_validation->run() == false) {
                $this->data["subview"] = "overtime/add";
                $this->load->view('_layout_main', $this->data);
            } else {
                $userHoursCalculation = $this->hourCalculation($this->input->post('roleId'), $this->input->post('userId'), $this->input->post('overtime_hours'));
                $array                = [
                    'date'              => date("Y-m-d H:i:s", strtotime((string) $this->input->post("overtime_date"))),
                    'hours'             => $this->input->post('overtime_hours'),
                    'amount'            => $userHoursCalculation->amount,
                    'total_amount'      => $userHoursCalculation->total_amount,
                    'userID'            => $this->input->post('userId'),
                    'user_table'        => (isset($this->tablename[$roleId]) ? $this->tablename[$roleId] : $this->tablename[3]),
                    'usertypeID'        => $this->input->post('roleId'),
                    'create_date'       => date('Y-m-d H:i:s'),
                    'modify_date'       => date('Y-m-d H:i:s'),
                    'create_userID'     => $this->session->userdata('loginuserID'),
                    'create_usertypeID' => $this->session->userdata('usertypeID'),
                ];

                $this->overtime_m->insert_overtime($array);

                $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                redirect(base_url("overtime/index"));
            }
        } else {
            $this->data["subview"] = "overtime/add";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function edit()
    {

        $this->data['headerassets'] = [
            'css' => array(
                'assets/datetimepicker/datetimepicker.css',
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css',
            ),
            'js'  => array(
                'assets/datetimepicker/moment.js',
                'assets/datetimepicker/datetimepicker.js',
                'assets/select2/select2.js',
            ),
        ];

        $id = htmlentities((string) escapeString($this->uri->segment(3)));

        $this->data['roles'] = $this->usertype_m->get_usertype_for_overtime();
        $roleId              = $this->input->post("roleId");
        $userID              = $this->input->post("userId");
        $table               = '';
        $tableID             = '';
        if ($userID > 0) {
            if ($roleId == 1) {
                $table   = 'systemadmin';
                $tableID = 'systemadminID';
            } elseif ($roleId == 2) {
                $table   = 'teacher';
                $tableID = 'teacherID';
            } else {
                $table   = 'user';
                $tableID = 'userID';
            }

            $this->data['users'] = $this->user_m->get_all_user($table, array('usertypeID' => $roleId));
        } else {
            $this->data['users'] = null;
        }

        $this->data['setUserId'] = 0;

        if ((int) $id !== 0) {
            $this->data['overtime'] = $this->overtime_m->get_overtime($id);
            if ($this->data['overtime']) {
                if ($_POST !== []) {
                    $rules = $this->rules();
                    $this->form_validation->set_rules($rules);
                    if ($this->form_validation->run() == false) {
                        $this->data["subview"] = "overtime/edit";
                        $this->load->view('_layout_main', $this->data);
                    } else {
                        $userHoursCalculation = $this->hourCalculation($this->input->post('roleId'), $this->input->post('userId'), $this->input->post('overtime_hours'));

                        $array                = [
                            'date'              => date("Y-m-d H:i:s", strtotime((string) $this->input->post("overtime_date"))),
                            'hours'             => $this->input->post('overtime_hours'),
                            'amount'            => $userHoursCalculation->amount,
                            'total_amount'      => $userHoursCalculation->total_amount,
                            'userID'            => $this->input->post('userId'),
                            'user_table'        => (isset($this->tablename[$roleId]) ? $this->tablename[$roleId] : $this->tablename[3]),
                            'usertypeID'        => $this->input->post('roleId'),
                            'create_date'       => date('Y-m-d H:i:s'),
                            'modify_date'       => date('Y-m-d H:i:s'),
                            'create_userID'     => $this->session->userdata('loginuserID'),
                            'create_usertypeID' => $this->session->userdata('usertypeID'),
                        ];

                        $this->overtime_m->update_overtime($array, $id);

                        $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                        redirect(base_url("overtime/index"));

                    }
                } else {
                    $this->data["subview"] = "overtime/edit";
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
        $id = htmlentities((string) escapeString($this->uri->segment(3)));
        if ((int) $id !== 0) {
            $this->overtime_m->delete_overtime($id);
            $this->session->set_flashdata('success', $this->lang->line('menu_success'));
            redirect(base_url("overtime/index"));
        } else {
            redirect(base_url("overtime/index"));
        }
    }

    public function uniqueRole()
    {
        if ($this->input->post('roleId') == 0) {
            $this->form_validation->set_message("uniqueRole", "The %s field is required");
            return false;
        }
        return true;
    }

    public function uniqueUser()
    {
        if ($this->input->post('userId') == 0) {
            $this->form_validation->set_message("uniqueUser", "The %s field is required");
            return false;
        }
        return true;
    }

    public function date_valid($date)
    {
        if ($date) {
            if (strlen((string) $date) < 19) {
                $this->form_validation->set_message("date_valid", "%s is not valid dd-mm-yyyy");
                return false;
            } else {
                $arr  = explode("-", (string) $date);
                $dd   = $arr[0];
                $mm   = $arr[1];
                $yyyy = explode(' ', $arr[2]);

                if (checkdate($mm, $dd, $yyyy[0])) {
                    return true;
                } else {
                    $this->form_validation->set_message("date_valid", "%s is not valid dd-mm-yyyy");
                    return false;
                }
            }
        }
        return true;
    }

    public function valid_hour()
    {
        if ($this->input->post('overtime_hours') && $this->input->post('overtime_hours') < 0) {
            $this->form_validation->set_message("valid_number", "%s is invalid number");
            return false;
        }
        return true;
    }

    public function userscall()
    {
        $roleId = $this->input->post('roleId');
        if ($roleId) {
            $table   = '';
            $tableID = '';
            if ($roleId == 1) {
                $table   = 'systemadmin';
                $tableID = 'systemadminID';
            } elseif ($roleId == 2) {
                $table   = 'teacher';
                $tableID = 'teacherID';
            } else {
                $table   = 'user';
                $tableID = 'userID';
            }

            $getUsers     = $this->user_m->get_all_user($table, array('usertypeID' => $roleId));
            $manageSalary = pluck_multi_array_key($this->manage_salary_m->get_order_by_manage_salary(['salary' => 1]), 'obj', 'usertypeID', 'userID');

            if (customCompute($getUsers)) {
                echo "<option value='0'>" . $this->lang->line("overtime_select_user") . "</option>";
                foreach ($getUsers as $key => $user) {
                    if (isset($manageSalary[$user->usertypeID][$user->$tableID])) {
                        echo "<option value='" . $user->$tableID . "'>" . $user->name . "</option>";
                    }
                }
            } else {
                echo "<option value='0'>" . $this->lang->line("overtime_select_user") . "</option>";
            }
        }
    }

    public function get_overtime_amount()
    {
        $roleId = $this->input->post("roleId");
        $userId = $this->input->post("userId");
        $hours  = $this->input->post("hours");

        echo $this->hourCalculation($roleId, $userId, $hours)->total_amount;
    }

    private function hourCalculation($roleId, $userId, $hours)
    {
        if ((int) $roleId && (int) $userId && $hours > 0) {
            $manageSalary = $this->manage_salary_m->get_single_manage_salary(['usertypeID' => $roleId, 'userID' => $userId]);
            if (is_object($manageSalary)) {
                $salaryTemplete = $this->salary_template_m->get_single_salary_template(['salary_templateID' => $manageSalary->template]);
                if (is_object($salaryTemplete)) {
                    return (object) ['amount' => $salaryTemplete->overtime_rate, 'total_amount' => ($hours * $salaryTemplete->overtime_rate)];
                }
            }
        }
        return (object) ['amount' => 0, 'total_amount' => 0];
    }

}
