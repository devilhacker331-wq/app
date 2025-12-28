<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Frontend_setting extends Admin_Controller
{
    public $load;
    public $session;
    public $lang;
    public $frontend_setting_m;
    public $upload;
    public $form_validation;
    public $upload_data;
    public $data;
    public $input;
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
        $this->load->model("frontend_setting_m");
        $this->load->helper('frontenddata');
        $language = $this->session->userdata('lang');
        $this->lang->load('frontend_setting', $language);
    }

    protected function rules()
    {
        return array(
            array(
                'field' => 'facebook',
                'label' => $this->lang->line("frontend_setting_facebook"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ),
            array(
                'field' => 'twitter',
                'label' => $this->lang->line("frontend_setting_twitter"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ),
            array(
                'field' => 'linkedin',
                'label' => $this->lang->line("frontend_setting_linkedin"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ),
            array(
                'field' => 'youtube',
                'label' => $this->lang->line("frontend_setting_youtube"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ),
            array(
                'field' => 'google',
                'label' => $this->lang->line("frontend_setting_google"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ),
            array(
                'field' => 'description',
                'label' => $this->lang->line("frontend_setting_description"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ),
            array(
                'field' => 'teacher_email_status',
                'label' => $this->lang->line("frontend_setting_teacher_email"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ),
            array(
                'field' => 'teacher_phone_status',
                'label' => $this->lang->line("frontend_setting_teacher_phone"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ),
            array(
                'field' => 'online_admission_status',
                'label' => $this->lang->line("frontend_setting_onlineadmission"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ),
            array(
                'field' => 'principle_name',
                'label' => $this->lang->line("frontend_setting_principle_name"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ),
            array(
                'field' => 'principle_message',
                'label' => $this->lang->line("frontend_setting_principle_message"),
                'rules' => 'trim|xss_clean|max_length[512]'
            ),
            [
                'field' => 'photo',
                'label' => $this->lang->line("setting_school_photo"),
                'rules' => 'trim|max_length[200]|xss_clean|callback_photoupload'
            ],
            array(
                'field' => 'hero_section_video',
                'label' => $this->lang->line("frontend_setting_hero_section_video"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ),
            array(
                'field' => 'hero_section_since',
                'label' => $this->lang->line("frontend_setting_hero_section_since"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ),
            array(
                'field' => 'message_one',
                'label' => $this->lang->line("frontend_setting_feature_one"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ),
            array(
                'field' => 'message_two',
                'label' => $this->lang->line("frontend_setting_feature_two"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ),
            array(
                'field' => 'message_three',
                'label' => $this->lang->line("frontend_setting_feature_three"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ),
            array(
                'field' => 'message_four',
                'label' => $this->lang->line("frontend_setting_feature_four"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ),
            array(
                'field' => 'school_origin',
                'label' => $this->lang->line("frontend_setting_school_origin"),
                'rules' => 'trim|xss_clean|max_length[512]'
            ),
            array(
                'field' => 'school_campus',
                'label' => $this->lang->line("frontend_setting_school_campus"),
                'rules' => 'trim|xss_clean|max_length[512]'
            ),
            array(
                'field' => 'school_success',
                'label' => $this->lang->line("frontend_setting_school_success"),
                'rules' => 'trim|xss_clean|max_length[512]'
            ),
            array(
                'field' => 'school_history',
                'label' => $this->lang->line("frontend_setting_school_history"),
                'rules' => 'trim|xss_clean|max_length[512]'
            ),
            array(
                'field' => 'admission_title',
                'label' => $this->lang->line("frontend_setting_admission_title"),
                'rules' => 'trim|xss_clean|max_length[512]'
            ),
            array(
                'field' => 'admission_description',
                'label' => $this->lang->line("frontend_setting_admission_description"),
                'rules' => 'trim|xss_clean|max_length[512]'
            ),
        );
    }

    public function photoupload()
    {
        $setting  = $this->frontend_setting_m->get_frontend_setting();
        $new_file = "principle.jpg";
        if ($_FILES["photo"]['name'] != "") {
            $file_name        = $_FILES["photo"]['name'];
            $random           = random19();
            $makeRandom       = hash('sha512', $random . config_item("encryption_key"));
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
        } else {
            if (property_exists($setting, 'photo')) {
                $this->upload_data['file'] = ['file_name' => $setting->photo];
                return true;    
            }
            $this->upload_data['file'] = ['file_name' => ''];
            return true;
            
        }
    }


    public function index()
    {
        $this->data['headerassets'] = array(
            'css' => array(
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css'
            ),
            'js' => array(
                'assets/select2/select2.js'
            )
        );

        $this->data['frontend_setting'] = $this->frontend_setting_m->get_frontend_setting();
        if ($this->data['frontend_setting']) {
            if ($_POST !== []) {
                $rules = $this->rules();
                $this->form_validation->set_rules($rules);
                if ($this->form_validation->run() == FALSE) {
                    $this->data["subview"] = "frontend_setting/index";
                    $this->load->view('_layout_main', $this->data);
                } else {
                    $array = array(
                        'login_menu_status'       => $this->input->post('login_menu_status'),
                        'teacher_email_status'    => $this->input->post('teacher_email_status'),
                        'teacher_phone_status'    => $this->input->post('teacher_phone_status'),
                        'online_admission_status' => $this->input->post('online_admission_status'),
                        'description'             => $this->input->post('description'),
                        'facebook'                => $this->input->post('facebook'),
                        'twitter'                 => $this->input->post('twitter'),
                        'linkedin'                => $this->input->post('linkedin'),
                        'youtube'                 => $this->input->post('youtube'),
                        'google'                  => $this->input->post('google'),
                        'principle_name'          => $this->input->post('principle_name'),
                        'principle_message'       => $this->input->post('principle_message'),
                        'photo'                   => $this->upload_data['file']['file_name'],
                        'hero_section_video'      => $this->input->post('hero_section_video'),
                        'hero_section_since'      => $this->input->post('hero_section_since'),
                        'embed_map'               => $this->input->post('embed_map'),
                        'message_one'             => $this->input->post('message_one'),
                        'message_two'             => $this->input->post('message_two'),
                        'message_three'           => $this->input->post('message_three'),
                        'message_four'            => $this->input->post('message_four'),
                        'school_origin'           => $this->input->post('school_origin'),
                        'school_campus'           => $this->input->post('school_campus'),
                        'school_success'          => $this->input->post('school_success'),
                        'school_vision'           => $this->input->post('school_vision'),
                        'admission_title'         => $this->input->post('admission_title'),
                        'admission_description'   => $this->input->post('admission_description'),
                    );
                    $this->frontend_setting_m->insertorupdate($array);
                    $this->session->set_flashdata('success', $this->lang->line('menu_success'));

                    frontendData::get_frontend_delete();
                    frontendData::get_frontend();
                    redirect(base_url("frontend_setting/index"));
                }
            } else {
                $this->data["subview"] = "frontend_setting/index";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }
}
