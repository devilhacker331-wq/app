<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Sponsor extends Admin_Controller
{
    public $load;
    public $session;
    public $lang;
    public $data;
    public $uri;
    public $sponsor_m;
    public $input;
    public $upload;
    public $form_validation;
    public $upload_data;
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
        $this->load->model("student_m");
        $language = $this->session->userdata('lang');
        $this->lang->load('sponsor', $language);

        $this->data['titles'] = array_merge(['0' => $this->lang->line('sponsor_select_title')], $this->sponsor_m->titles);
    }

    public function index()
    {
        $this->data['sponsors'] = $this->sponsor_m->get_order_by_sponsor();
        $this->data["subview"]  = "sponsor/index";
        $this->load->view('_layout_main', $this->data);
    }

    protected function individualrules()
    {
        return array(
            array(
                'field' => 'sponsor_person_name',
                'label' => $this->lang->line("sponsor_person_name"),
                'rules' => 'trim|required|xss_clean|max_length[100]',
            ),
            array(
                'field' => 'organisation_name',
                'label' => $this->lang->line("sponsor_organisation_name"),
                'rules' => 'trim|xss_clean|max_length[100]',
            ),
            array(
                'field' => 'email',
                'label' => $this->lang->line("sponsor_email"),
                'rules' => 'trim|required|max_length[40]|valid_email|xss_clean|callback_unique_email',
            ),
            array(
                'field' => 'phone',
                'label' => $this->lang->line("sponsor_phone"),
                'rules' => 'trim|required|xss_clean|max_length[25]',
            ),
            array(
                'field' => 'title',
                'label' => $this->lang->line("sponsor_title"),
                'rules' => 'trim|required|max_length[128]|xss_clean|callback_unique_data',
            ),
            array(
                'field' => 'country',
                'label' => $this->lang->line("sponsor_country"),
                'rules' => 'trim|required|max_length[128]|xss_clean|callback_unique_country',
            ),
            array(
                'field' => 'photo',
                'label' => $this->lang->line("sponsor_photo"),
                'rules' => 'trim|max_length[200]|xss_clean|callback_photoupload',
            ),
        );
    }

    public function photoupload()
    {
        $id   = htmlentities((string) escapeString($this->uri->segment(3)));
        $user = [];
        if ((int) $id !== 0) {
            $user = $this->sponsor_m->get_single_sponsor(array('sponsorID' => $id));
        }

        $new_file = "default.png";
        if ($_FILES["photo"]['name'] != "") {
            $file_name        = $_FILES["photo"]['name'];
            $random           = random19();
            $makeRandom       = hash('sha512', $random . $this->input->post('username') . config_item("encryption_key"));
            $file_name_rename = $makeRandom;
            $explode          = explode('.', (string) $file_name);
            if (customCompute($explode) >= 2) {
                $new_file                = $file_name_rename . '.' . end($explode);
                $config['upload_path']   = "./uploads/images";
                $config['allowed_types'] = "gif|jpg|png";
                $config['file_name']     = $new_file;
                $config['max_size']      = '1024';
                $config['max_width']     = '3000';
                $config['max_height']    = '3000';
                $this->load->library('upload', $config);
                if (!$this->upload->do_upload("photo")) {
                    $this->form_validation->set_message("photoupload", $this->upload->display_errors());
                    return false;
                } else {
                    $this->upload_data['file'] = $this->upload->data();
                    return true;
                }
            } else {
                $this->form_validation->set_message("photoupload", "Invalid file");
                return false;
            }
        } elseif (customCompute($user)) {
            $this->upload_data['file'] = array('file_name' => $user->photo);
            return true;
        } else {
            $this->upload_data['file'] = array('file_name' => $new_file);
            return true;
        }
    }

    protected function organisationrules()
    {
        return array(
            array(
                'field' => 'sponsor_organisation_name',
                'label' => $this->lang->line("sponsor_sponsor_organisation_name"),
                'rules' => 'trim|required|xss_clean|max_length[100]',
            ),

            array(
                'field' => 'contact_person_name',
                'label' => $this->lang->line("sponsor_contact_person_name"),
                'rules' => 'trim|required|xss_clean|max_length[100]',
            ),
            array(
                'field' => 'email',
                'label' => $this->lang->line("sponsor_email"),
                'rules' => 'trim|required|max_length[40]|valid_email|xss_clean|callback_unique_email',
            ),
            array(
                'field' => 'phone',
                'label' => $this->lang->line("sponsor_phone"),
                'rules' => 'trim|required|xss_clean|max_length[25]',
            ),
            array(
                'field' => 'organisation_title',
                'label' => $this->lang->line("sponsor_title"),
                'rules' => 'trim|required|max_length[128]|xss_clean|callback_unique_data',
            ),
            array(
                'field' => 'country',
                'label' => $this->lang->line("sponsor_country"),
                'rules' => 'trim|required|max_length[128]|xss_clean|callback_unique_country',
            ),
            array(
                'field' => 'photo',
                'label' => $this->lang->line("sponsor_photo"),
                'rules' => 'trim|max_length[200]|xss_clean|callback_photoupload',
            ),
        );
    }

    public function unique_email()
    {
        if ($this->input->post('email')) {
            $id = htmlentities((string) escapeString($this->uri->segment(3)));
            if ((int) $id !== 0) {
                $sponsor = $this->sponsor_m->get_single_sponsor(array('email' => $this->input->post('email'), 'sponsorID !=' => $id));

                if (customCompute($sponsor)) {
                    $this->form_validation->set_message("unique_email", "The %s is already exists.");
                    return false;
                }
                return true;
            } else {
                $sponsor = $this->sponsor_m->get_single_sponsor(array('email' => $this->input->post('email')));
                if (customCompute($sponsor)) {
                    $this->form_validation->set_message("unique_email", "The %s is already exists.");
                    return false;
                }
                return true;
            }
        }
        return true;
    }

    public function add()
    {
        $this->data['headerassets'] = [
            'css' => array(
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css',
            ),
            'js'  => array(
                'assets/select2/select2.js',
            ),
        ];

        $this->data['checked'] = 'individual';
        if ($_POST !== []) {
            $schoolyearID = $this->session->userdata('defaultschoolyearID');

            if ($this->input->post("checked") == 'organisation') {
                $rules                 = $this->organisationrules();
                $this->data['checked'] = 'organisation';
            } else {
                $rules                 = $this->individualrules();
                $this->data['checked'] = 'individual';
            }

            $this->form_validation->set_rules($rules);
            if ($this->form_validation->run() == false) {
                $this->data["subview"] = "/sponsor/add";
                $this->load->view('_layout_main', $this->data);
            } else {

                if ($this->input->post("checked") == 'organisation') {
                    $name              = $this->input->post("sponsor_organisation_name");
                    $title             = $this->input->post("organisation_title");
                    $organisation_name = $this->input->post("contact_person_name");
                } else {
                    $name              = $this->input->post("sponsor_person_name");
                    $title             = $this->input->post("title");
                    $organisation_name = $this->input->post("organisation_name");
                }

                $array = [
                    "type"              => $this->input->post('checked'),
                    "name"              => $name,
                    "title"             => $title,
                    "organisation_name" => $organisation_name,
                    "email"             => $this->input->post("email"),
                    "phone"             => $this->input->post("phone"),
                    "country"           => $this->input->post("country"),
                    "schoolyearID"      => $schoolyearID,
                    "create_date"       => date("Y-m-d H:i:s"),
                    "modify_date"       => date("Y-m-d H:i:s"),
                    "create_userID"     => $this->session->userdata('loginuserID'),
                    "create_username"   => $this->session->userdata('username'),
                    "create_usertypeID" => $this->session->userdata('usertypeID'),
                    "photo"             => $this->upload_data['file']['file_name'],
                ];

                $this->sponsor_m->insert_sponsor($array);
                $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                redirect(base_url("sponsor/index"));
            }
        } else {
            $this->data["subview"] = "sponsor/add";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function view()
    {
        $id = htmlentities((string) escapeString($this->uri->segment(3)));
        if ((int) $id !== 0) {
            $this->data['sponsor'] = $this->sponsor_m->get_single_sponsor(array('sponsorID' => $id));

            if (customCompute($this->data['sponsor'])) {
                $this->data["subview"] = "sponsor/view";
                $this->load->view('_layout_main', $this->data);
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main');
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main');
        }
    }

    public function print_preview()
    {
        if (permissionChecker('sponsor_view')) {
            $sponsorID = htmlentities((string) escapeString($this->uri->segment(3)));
            if ((int) $sponsorID !== 0) {
                $this->data['sponsor'] = $this->sponsor_m->get_single_sponsor(array('sponsorID' => $sponsorID));

                if (customCompute($this->data['sponsor'])) {
                    $this->reportPDF('assetmodule.css', $this->data, 'sponsor/print_preview');
                } else {
                    $this->data["subview"] = "error";
                    $this->load->view('_layout_main');
                }
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main');
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main');
        }
    }

    public function edit()
    {
        $this->data['headerassets'] = array(
            'css' => array(
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css',
            ),
            'js'  => array(
                'assets/select2/select2.js',
            ),
        );

        $this->data['checked'] = 'individual';

        $id = htmlentities((string) escapeString($this->uri->segment(3)));
        if ((int) $id !== 0) {
            $this->data['sponsor'] = $this->sponsor_m->get_single_sponsor(array('sponsorID' => $id));
            if ($this->data['sponsor']) {
                $this->data['checked'] = $this->data['sponsor']->type;
                if ($_POST !== []) {
                    $schoolyearID = $this->session->userdata('defaultschoolyearID');
                    if ($this->input->post("checked") == 'organisation') {
                        $rules                 = $this->organisationrules();
                        $this->data['checked'] = 'organisation';
                    } else {
                        $rules                 = $this->individualrules();
                        $this->data['checked'] = 'individual';
                    }
                    $this->form_validation->set_rules($rules);
                    if ($this->form_validation->run() == false) {
                        $this->data["subview"] = "/sponsor/edit";
                        $this->load->view('_layout_main', $this->data);
                    } else {
                        if ($this->input->post("checked") == 'organisation') {
                            $name              = $this->input->post("sponsor_organisation_name");
                            $title             = $this->input->post("organisation_title");
                            $organisation_name = $this->input->post("contact_person_name");
                        } else {
                            $name              = $this->input->post("sponsor_person_name");
                            $title             = $this->input->post("title");
                            $organisation_name = $this->input->post("organisation_name");
                        }

                        $array = [
                            "type"              => $this->input->post('checked'),
                            "name"              => $name,
                            "title"             => $title,
                            "organisation_name" => $organisation_name,
                            "email"             => $this->input->post("email"),
                            "phone"             => $this->input->post("phone"),
                            "country"           => $this->input->post("country"),
                            "photo"             => $this->upload_data['file']['file_name'],
                            "schoolyearID"      => $schoolyearID,
                            "create_date"       => date("Y-m-d H:i:s"),
                            "modify_date"       => date("Y-m-d H:i:s"),
                            "create_userID"     => $this->session->userdata('loginuserID'),
                            "create_username"   => $this->session->userdata('username'),
                            "create_usertypeID" => $this->session->userdata('usertypeID'),
                        ];

                        $this->sponsor_m->update_sponsor($array, $id);
                        $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                        redirect(base_url("sponsor/index"));
                    }
                } else {
                    $this->data["subview"] = "/sponsor/edit";
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
            $this->data['sponsor'] = $this->sponsor_m->get_single_sponsor(array('sponsorID' => $id));
            if ($this->data['sponsor']) {
                $this->sponsor_m->delete_sponsor($id);
                $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                redirect(base_url("sponsor/index"));
            } else {
                redirect(base_url("sponsor/index"));
            }
        } else {
            redirect(base_url("sponsor/index"));
        }
    }

    public function unique_data($data)
    {
        if ($data != '' && $data == '0') {
            $this->form_validation->set_message('unique_data', 'The %s field is required.');
            return false;
        }
        return true;
    }

    public function unique_country()
    {
        if ($this->input->post('country') == '0') {
            $this->form_validation->set_message("unique_country", "The %s field is required");
            return false;
        }
        return true;
    }
}
