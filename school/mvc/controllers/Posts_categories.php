<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Posts_categories extends Admin_Controller {
public $load;
 public $session;
 public $lang;
 public $data;
 public $form_validation;
 public $input;
 public $posts_categories_m;
 public $uri;
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
	function __construct() {
		parent::__construct();
		$this->load->model('posts_categories_m');
		$language = $this->session->userdata('lang');
		$this->lang->load('posts_categories', $language);	
	}

	protected function rules() {
		return array(
			array(
				'field' => 'posts_categories', 
				'label' => $this->lang->line("posts_categories_name"), 
				'rules' => 'trim|required|xss_clean|max_length[40]|callback_unique_posts_categories'
			), 
			array(
				'field' => 'posts_description', 
				'label' => $this->lang->line("posts_categories_description"), 
				'rules' => 'trim|max_length[200]|xss_clean'
			)
		);
	}

	public function index() {
		$this->data['posts_categories'] = $this->posts_categories_m->get_posts_categories();
		$this->data["subview"] = "posts_categories/index";
		$this->load->view('_layout_main', $this->data);
	}

	public function add() {
		if($_POST !== []) {
			$rules = $this->rules();
			$this->form_validation->set_rules($rules);
			if ($this->form_validation->run() == FALSE) { 
				$this->data["subview"] = "posts_categories/add";
				$this->load->view('_layout_main', $this->data);			
			} else {
				$array = array(
	                'posts_categories' => $this->input->post("posts_categories"),
	                'posts_slug' => '#',
	                'posts_parent' => 0,
	                'posts_description' => $this->input->post("posts_description")
	            );

				$this->posts_categories_m->insert_posts_categories($array);

				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
				redirect(base_url("posts_categories/index"));
			}
		} else {
			$this->data["subview"] = "posts_categories/add";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function edit() {
		$id = htmlentities((string) escapeString($this->uri->segment(3)));
		if((int)$id !== 0) {
			$this->data['posts_categories'] = $this->posts_categories_m->get_posts_categories($id);
			if($this->data['posts_categories']) {
				if($_POST !== []) {
					$rules = $this->rules();
					$this->form_validation->set_rules($rules);
					if ($this->form_validation->run() == FALSE) {
						$this->data["subview"] = "posts_categories/edit";
						$this->load->view('_layout_main', $this->data);			
					} else {
						$array = array(
			                'posts_categories' => $this->input->post("posts_categories"),
			                'posts_description' => $this->input->post("posts_description")
			            );

						$this->posts_categories_m->update_posts_categories($array, $id);
						$this->session->set_flashdata('success', $this->lang->line('menu_success'));
						redirect(base_url("posts_categories/index"));
					}
				} else {
					$this->data["subview"] = "posts_categories/edit";
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

	public function delete() {
		$id = htmlentities((string) escapeString($this->uri->segment(3)));
		if((int)$id !== 0) {
			$posts_categories = $this->posts_categories_m->get_posts_categories($id);
			if(customCompute($posts_categories)) {
				$this->posts_categories_m->delete_posts_categories($id);
				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
			}

			redirect(base_url("posts_categories/index"));
		} else {
			redirect(base_url("posts_categories/index"));
		}
	}

	public function unique_posts_categories() {
		$id = htmlentities((string) escapeString($this->uri->segment(3)));
		if((int)$id !== 0) {
			$posts_categories = $this->posts_categories_m->get_order_by_posts_categories(array("posts_categories" => $this->input->post("posts_categories"), "posts_categoriesID !=" => $id));
			if(customCompute($posts_categories)) {
				$this->form_validation->set_message("unique_posts_categories", "The %s is already exists.");
				return FALSE;
			}
			return TRUE;
		} else {
			$posts_categories = $this->posts_categories_m->get_order_by_posts_categories(array("posts_categories" => $this->input->post("posts_categories")));

			if(customCompute($posts_categories)) {
				$this->form_validation->set_message("unique_posts_categories", "The %s is already exists.");
				return FALSE;
			}
			return TRUE;
		}	
	}
}