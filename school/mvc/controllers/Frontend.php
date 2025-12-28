<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Frontend extends Frontend_Controller
{
    protected $_addmissionDisplay = null;
    protected $_aboutDisplay      = null;
    protected $_teachersDisplay   = null;
    protected $_eventsDisplay     = null;
    protected $_galleryDisplay    = null;
    protected $_sliders           = null;
    public $load;
    public $uri;
    public $data;
    public $site_m;
    public $pages_m;
    public $student_m;
    public $teacher_m;
    public $event_m;
    public $slider_m;
    public $media_gallery_m;
    public $bladeView;
    public $posts_m;
    public $input;
    public $session;
    public $eventcounter_m;
    public $notice_m;
    public $email;
    protected $_pageName;
    protected $_templateName;
    protected $_homepage;
    protected $_allPages;
    protected $_pageData;
    protected $_siteInfo;
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



    public function __construct()
    {
        parent::__construct();
        $this->load->model('pages_m');
        $this->load->model('media_gallery_m');
        $this->load->model('slider_m');
        $this->load->model('student_m');
        $this->load->model('teacher_m');
        $this->load->model('event_m');
        $this->load->model('notice_m');
        $this->load->model('posts_m');
        $this->load->model("site_m");
        $this->load->model("parents_m");
        $this->load->model("onlineadmission_m");
        $this->_allPages = $this->pages_m->get_pages();
        $this->_siteInfo = $this->site_m->get_site();
        $language = $this->session->userdata('lang');
        $this->lang->load('topbar_menu_lang', $language);
    }

    public function index()
    {
        $type = $this->uri->segment(3);
        $url  = $this->uri->segment(4);

        if ($type && $url) {
            redirect(base_url('frontend/' . htmlentities($type) . '/' . htmlentities($url)));
            return;
        }
        if (customCompute($this->data['homepage'])) {

            $page = $this->data['homepage'];

            if (isset($page->pagesID)) {
                $this->home($page->url);
            } elseif (isset($page->postsID)) {
                $this->post($page->url);
            } else {
                $this->session->set_flashdata('error', $this->lang->line('setup_page_notitfication'));
                redirect(base_url('signin/index'));  
            }
        } else {
            $this->session->set_flashdata('error', $this->lang->line('setup_page_notitfication'));
            redirect(base_url('signin/index')); 
        }
    }

    public function home(){
        if (customCompute($this->_allPages)) {
            foreach ($this->_allPages as $page) {
                if ($page->template === 'home'  && $page->status == '1') {
                    $this->_sliders =  $this->slider_m->get_slider_join_with_media_gallery($page->pagesID);
                }

                if ($page->template == 'admission'  && $page->status == '1') {
                    $this->_addmissionDisplay = (object) ["addmission_url" => base_url('frontend/page/' . $page->url)];
                }

                if ($page->template == 'about' && $page->status == '1') {
                    $about_featured_image = $this->media_gallery_m->get_single_media_gallery(['media_galleryID' => $page->featured_image]);
                    $this->_aboutDisplay        = (object) [
                        "about_url"            => base_url('frontend/page/' . $page->url),
                        "about_content"        => $page->content,
                        "about_featured_image" => $about_featured_image ? $about_featured_image->file_name : '',
                        "total_student"        => $this->student_m->count_all_students(),
                        "total_teacher"        => $this->teacher_m->count_all_teachers(),
                        "total_parent"         => $this->parents_m->count_all_patents(),
                    ];
                }

                if ($page->template == 'teacher' && $page->status == '1') {
                    $this->_teachersDisplay = (object) [
                        "teacher_url"  => base_url('frontend/page/' . $page->url),
                        "teachers"     => $this->teacher_m->general_get_order_by_teacher()
                    ];
                }

                if ($page->template == 'event' && $page->status == '1') {
                    $this->_eventsDisplay = (object) [
                        "event_url"  => base_url('frontend/page/' . $page->url),
                        "events"     => $this->event_m->get_order_by_event()
                    ];
                }

                if ($page->template == 'gallery' && $page->status == '1') {
                    $gallery_url = base_url('frontend/page/' . $page->url);
                    $images      = $this->pages_m->get_single_pages(['url' => $page->url]);
                    $content     = $images->content;
                    preg_match_all('/<img[^>]+src="([^">]+)"/i', $content, $matches);
                    $imageUrls             = array_slice($matches[1], 0, 10);
                    $this->_galleryDisplay = (object) [
                        "gallery_page" => $imageUrls,
                        "gallery_url"  => $gallery_url,
                    ];
                }
            }

            $this->_pageData = (object) [
                'addmission_display' => $this->_addmissionDisplay,
                'about_display'      => $this->_aboutDisplay,
                'teachers_display'   => $this->_teachersDisplay,
                'events_display'     => $this->_eventsDisplay,
                'gallery_display'    => $this->_galleryDisplay
            ];
            $this->bladeView->render(
                'views/templates/home',
                ['pages_data' => $this->_pageData, 'sliders' => $this->_sliders, 'all_pages' => $this->_allPages]
            );
        } else { 
            $this->session->set_flashdata('error', $this->lang->line('setup_page_notitfication'));
            redirect(base_url('signin/index'));
        } 
    }


    public function page($url = null)
    {

        $gallery = [];
        if (!$url) {
            $this->renderPage404();
            return;
        }

        if ($url == 'login') {
            redirect(base_url('signin/index'));
        }

        $page     = $this->pages_m->get_single_pages(['url' => $url]);
        if (!$page) {
            $this->renderPage404();
            return;
        }
        $this->_pageName     = $page->title;
        $this->_templateName = $page->template;
        $featured_image      = !empty($page->featured_image) ? $this->media_gallery_m->get_single_media_gallery(['media_galleryID' => $page->featured_image]) : [];
        if ($page->template == 'home') {
            redirect(base_url('/'));
        }
        if ($page->template == 'blog') {
            $posts = $this->posts_m->get_posts_with_limit_offset(['status' => 1], 7);
            $featured_image = customCompute($posts) ? pluck($this->media_gallery_m->get_order_by_media_gallery(['media_gallery_type' => 1]), 'obj', 'media_galleryID') : [];
        }
        if ($page->template == 'gallery') {
            $content     = $page->content;
            preg_match_all('/<img[^>]+src="([^">]+)"/i', $content, $matches);
            $gallery = $matches[1];
        }
        $this->bladeView->render(
            $this->_templateName === 'none' ? 'views/templates/none' : 'views/templates/' . $this->_templateName,
            ['page' => $page, 'posts' => $posts ?? null, 'featured_image' => $featured_image, 'siteInfo' => $this->_siteInfo, 'all_pages' => $this->_allPages, 'gallery' => $gallery]
        );
    }


    public function post($url = null)
    {
        if ($url) {
            if ($url == 'login') {
                redirect(base_url('signin/index'));
            }
            $this->_templateName = 'blogdetails';
            $post = $this->posts_m->get_single_posts(['url' => $url]);
            $postImage = $post && !empty($post->featured_image) ? $this->media_gallery_m->get_single_media_gallery(['media_galleryID' => $post->featured_image]) : (object)[];
            $recentPosts = $this->posts_m->get_posts_with_limit_offset(['status' => 1]);
            $recentPostimage = pluck($this->media_gallery_m->get_order_by_media_gallery(['media_gallery_type' => 1]), 'obj', 'media_galleryID');
            $this->bladeView->render(
                'views/templates/' . $this->_templateName,
                ['post' => $post, 'recentPosts' => $recentPosts, 'postImage' => $postImage, 'recentPostimage' => $recentPostimage]
            );
        } else {
            $this->_templateName = 'page404';
            $this->bladeView->render('views/templates/' . $this->_templateName);
        }
    }

    public function event()
    {
        $id = htmlentities((string) escapeString($this->uri->segment(3)));
        if (is_numeric($id) && (int) $id !== 0) {
            $event = $this->event_m->get_single_event(['eventID' => $id]);
            if (customCompute($event)) {
                $this->bladeView->render('views/templates/eventdetails', ['event' => $event, 'latestEvents' => $this->data['latestevents']]);
                return;
            }
        }
        $this->renderPage404();
    }

    public function eventGoing()
    {
        $status = false;
        $id = htmlentities((string) escapeString($this->input->post('id')));
        if ((int) $id !== 0) {
            if ($this->session->userdata('loggedin')) {
                $event = $this->event_m->get_single_event(['eventID' => $id]);
                if (customCompute($event)) {
                    $username = $this->session->userdata("username");
                    $usertype = $this->session->userdata("usertype");
                    $photo = $this->session->userdata("photo");
                    $name = $this->session->userdata("name");

                    $this->load->model('eventcounter_m');
                    $have = $this->eventcounter_m->get_order_by_eventcounter([
                        "eventID" => $id,
                        "username" => $username,
                        "type" => $usertype
                    ], true);

                    if (customCompute($have)) {
                        $array = ['status' => 1];
                        $this->eventcounter_m->update($array, $have[0]->eventcounterID);
                        $status = true;
                        $message = 'You are add this event';
                    } else {
                        $array = [
                            'eventID' => $id,
                            'username' => $username,
                            'type' => $usertype,
                            'photo' => $photo,
                            'name' => $name,
                            'status' => 1
                        ];
                        $this->eventcounter_m->insert($array);
                        $status = true;
                        $message = 'You are add this event';
                    }
                } else {
                    $message = 'Event id does not found';
                }
            } else {
                $message = 'Please login';
            }
        } else {
            $message = 'ID is not int';
        }

        $json = [
            "message" => $message,
            'status' => $status,
        ];
        header("Content-Type: application/json", true);
        echo json_encode($json);
        exit;
    }

    public function notice()
    {
        $id = htmlentities((string) escapeString($this->uri->segment(3)));
        if ((int) $id !== 0) {
            $notice = $this->notice_m->get_single_notice(['noticeID' => $id]);
            if (customCompute($notice)) {
                $this->bladeView->render('views/templates/noticedetails', ['notice' => $notice]);
                return;
            }
        }
        $this->renderPage404();
    }

    public function contactMailSend()
    {
        $name    = $this->input->post('name');
        $email   = $this->input->post('email');
        $subject = $this->input->post('subject');
        $message = $this->input->post('message');
        if ($name && $email && $subject && $message) {
            $this->load->library('email');
            $this->email->set_mailtype("html");
            if (frontendData::get_backend('email')) {
                $this->email->from($email, frontendData::get_backend('sname'));
                $this->email->to(frontendData::get_backend('email'));
                $this->email->subject($subject);
                $this->email->message($message);
                $this->email->send();
                $this->session->set_flashdata('success', 'Email send successfully!');
                echo 'success';
            } else {
                $this->session->set_flashdata('error', 'Set your email in general setting');
            }
        } else {
            $this->session->set_flashdata('error', 'oops! Email not send!');
        }
    }

    public function admission()
    {
        $id = htmlentities((string) escapeString($this->uri->segment(3)));
        if (is_numeric($id) && (int) $id !== 0) {
            $admission = $this->onlineadmission_m->get_single_onlineadmission(['onlineadmissionID' => $id]);
            $classes   = pluck($this->classes_m->general_get_classes(), 'obj', 'classesID');
            if (customCompute($admission)) {
                $this->bladeView->render('views/templates/admissionslip', ['admission' => $admission, 'siteinfos' => $this->data['backend_setting'], 'classes' => $classes, 'country' => $this->data['allcountry']]);
                return;
            }
        }
        $this->renderPage404();
    }

    public function load_more_data()
    {
        $data['items'] = $this->teacher_m->get_teachers_with_limit_offset($this->input->post('limit'), $this->input->post('offset'));
        echo json_encode($data);
    }

    public function load_more_event()
    {
        $events = $this->event_m->get_events_with_limit_offset($this->input->post('offset'),$this->input->post('limit'),['schoolyearID' =>  $this->data['backend_setting']->school_year]);
        echo json_encode($events);
    }

    public function load_more_notice()
    {
        $notice = $this->notice_m->get_notice_with_limit_offset($this->input->post('offset'),$this->input->post('limit'),['schoolyearID' =>  $this->data['backend_setting']->school_year]);
        echo json_encode($notice);
    }

    public function load_more_post()
    {
        $posts  = $this->posts_m->get_posts_with_limit_offset(['status' => 1], $this->input->post('limit'), $this->input->post('offset'));
        $images = pluck($this->media_gallery_m->get_order_by_media_gallery(['media_gallery_type' => 1]), 'obj', 'media_galleryID');
        $data = [
            'posts'  => $posts,
            'images' => $images,
        ];
        echo json_encode($data);
    }

    public function load_by()
    {
        $data = $this->input->post('post');
        $user = getNameByUsertypeIDAndUserID($data['create_usertypeID'], $data['create_userID']);
        echo json_encode($user);
    }

    private function renderPage404()
    {
        $this->_templateName = 'page404';
        $this->bladeView->render('views/templates/' . $this->_templateName);
    }

    protected function admission_rules()
    {
        return array(
            array(
                'field' => 'onlineadmissionID',
                'label' => 'onlineadmissionID',
                'rules' => 'trim|required|xss_clean|max_length[60]'
            ),
            array(
                'field' => 'phone',
                'label' => 'phone',
                'rules' => 'trim|required|xss_clean|min_length[5]|max_length[25]'
            ),
        );
    }
}
