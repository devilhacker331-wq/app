<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Mailandsms extends Admin_Controller {
public $load;
 public $data;
 public $session;
 public $lang;
 public $input;
 public $form_validation;
 public $systemadmin_m;
 public $mailandsms_m;
 public $teacher_m;
 public $studentrelation_m;
 public $parents_m;
 public $user_m;
 public $inilabs;
 public $mailandsmstemplatetag_m;
 public $emailsetting_m;
 public $email;
 public $classes_m;
 public $section_m;
 public $studentgroup_m;
 public $subject_m;
 public $mark;
 public $mailandsmstemplate_m;
 public $clickatell;
 public $twilio;
 public $bulk;
 public $msg91;
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
	function __construct () {
		parent::__construct();
		$this->load->model('usertype_m');
		$this->load->model('systemadmin_m');
		$this->load->model('teacher_m');
		$this->load->model('student_m');
		$this->load->model('parents_m');
		$this->load->model('user_m');
		$this->load->model('classes_m');
		$this->load->model('section_m');
		$this->load->model("mark_m");
		$this->load->model("grade_m");
		$this->load->model("exam_m");
		$this->load->model('mailandsms_m');
		$this->load->model('mailandsmstemplate_m');
		$this->load->model('mailandsmstemplatetag_m');
		$this->load->model('studentgroup_m');
		$this->load->model('studentrelation_m');
		$this->load->model('emailsetting_m');
		$this->load->model('subject_m');
		$this->load->library("email");
		$this->load->library("clickatell");
		$this->load->library("twilio");
		$this->load->library("bulk");
		$this->load->library("msg91");
		$this->load->library("inilabs",$this->data);
		
		$language = $this->session->userdata('lang');
		$this->lang->load('mailandsms', $language);
	}
	
	protected function rules_mail() {
		return array(
			array(
				'field' => 'email_usertypeID',
				'label' => $this->lang->line("mailandsms_usertype"),
				'rules' => 'trim|required|xss_clean|max_length[15]|callback_check_email_usertypeID'
			),
			array(
				'field' => 'email_schoolyear',
				'label' => $this->lang->line("mailandsms_schoolyear"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'email_class',
				'label' => $this->lang->line("mailandsms_class"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'email_users',
				'label' => $this->lang->line("mailandsms_users"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'email_template',
				'label' => $this->lang->line("mailandsms_template"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'email_subject',
				'label' => $this->lang->line("mailandsms_subject"),
				'rules' => 'trim|required|xss_clean|max_length[255]'
			),
			array(
				'field' => 'email_message',
				'label' => $this->lang->line("mailandsms_message"),
				'rules' => 'trim|required|xss_clean|max_length[20000]'
			),
			array(
				'field' => 'file',
				'label' => $this->lang->line("mailandsms_attachment"),
				'rules' => 'trim|xss_clean|max_length[200]'
			)
		);
	}

	protected function rules_sms() {
		return array(
			array(
				'field' => 'sms_usertypeID',
				'label' => $this->lang->line("mailandsms_usertype"),
				'rules' => 'trim|required|xss_clean|max_length[15]|callback_check_sms_usertypeID'
			),
			array(
				'field' => 'sms_schoolyear',
				'label' => $this->lang->line("mailandsms_schoolyear"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'sms_class',
				'label' => $this->lang->line("mailandsms_select_class"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'sms_users',
				'label' => $this->lang->line("mailandsms_users"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'sms_template',
				'label' => $this->lang->line("mailandsms_template"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'sms_getway',
				'label' => $this->lang->line("mailandsms_getway"),
				'rules' => 'trim|required|xss_clean|max_length[15]|callback_check_getway'
			),
			array(
				'field' => 'sms_message',
				'label' => $this->lang->line("mailandsms_message"),
				'rules' => 'trim|required|xss_clean|max_length[20000]'
			),
		);
	}

	protected function rules_otheremail() {
		return array(
			array(
				'field' => 'otheremail_name',
				'label' => $this->lang->line("mailandsms_name"),
				'rules' => 'trim|required|xss_clean'
			),
			array(
				'field' => 'otheremail_email',
				'label' => $this->lang->line("mailandsms_email"),
				'rules' => 'trim|required|xss_clean|valid_email'
			),
			array(
				'field' => 'otheremail_subject',
				'label' => $this->lang->line("mailandsms_subject"),
				'rules' => 'trim|required|xss_clean|max_length[255]'
			),
			array(
				'field' => 'otheremail_message',
				'label' => $this->lang->line("mailandsms_message"),
				'rules' => 'trim|required|xss_clean|max_length[20000]'
			),
			array(
				'field' => 'file',
				'label' => $this->lang->line("mailandsms_attachment"),
				'rules' => 'trim|xss_clean|max_length[200]'
			)
		);
	}

	protected function rules_othersms() {
		return array(
			array(
				'field' => 'othersms_name',
				'label' => $this->lang->line("mailandsms_name"),
				'rules' => 'trim|required|xss_clean'
			),
			array(
				'field' => 'othersms_phone',
				'label' => $this->lang->line("mailandsms_phone"),
				'rules' => 'trim|required|xss_clean'
			),
			array(
				'field' => 'sms_getway',
				'label' => $this->lang->line("mailandsms_getway"),
				'rules' => 'trim|required|xss_clean|callback_unique_data|max_length[15]|callback_check_getway'
			),
			array(
				'field' => 'othersms_message',
				'label' => $this->lang->line("mailandsms_message"),
				'rules' => 'trim|required|xss_clean|max_length[20000]'
			),
		);
	}

	public function index() {
		$this->data['mailandsmss'] = $this->mailandsms_m->get_mailandsms_with_usertypeID();
		$this->data["subview"] = "mailandsms/index";
		$this->load->view('_layout_main', $this->data);
	}

	public function add() {
		$this->data['headerassets'] = array(
			'css' => array(
				'assets/select2/css/select2.css',
				'assets/select2/css/select2-bootstrap.css',
				'assets/editor/jquery-te-1.4.0.css'
			),
			'js' => array(
				'assets/select2/select2.js',
				'assets/editor/jquery-te-1.4.0.min.js'
			)
		);
		$this->data['usertypes'] = $this->usertype_m->get_usertype();
		$this->data['schoolyears'] = $this->schoolyear_m->get_schoolyear();
		$this->data['allClasses'] = $this->classes_m->general_get_classes();
        $this->data['sections'] = [];
        $classesID = $this->input->post("classesID");

        $this->data['sections'] = $classesID > 0 ? $this->section_m->get_order_by_section(array("classesID" => $classesID)) : [];


        /* Start For Email */
		$email_usertypeID = $this->input->post("email_usertypeID");
		$this->data['email_usertypeID'] = $email_usertypeID && $email_usertypeID != 'select' ? $email_usertypeID : 'select';
		/* End For Email */

		/* Start For SMS */
		$sms_usertypeID = $this->input->post("sms_usertypeID");
		$this->data['sms_usertypeID'] = $sms_usertypeID && $sms_usertypeID != 'select' ? $sms_usertypeID : 'select';
		/* End For SMS */

		if($_POST !== []) {
			$this->data['submittype'] = $this->input->post('type');
			if($this->input->post('type') == "email") {
				$rules = $this->rules_mail();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$this->data['emailUserID'] = $this->input->post('email_users');
					$this->data['emailTemplateID'] = $this->input->post('email_template');

					$this->data['allStudents'] = $this->studentrelation_m->general_get_order_by_student(array('srschoolyearID' => $this->input->post('email_schoolyear'), 'srclassesID' => $this->input->post('email_class')), TRUE);

					$this->data['smsUserID'] = 0;
					$this->data['smsTemplateID'] = 0;

					$this->data["email"] = 1;
					$this->data["sms"] = 0;
					$this->data["otheremail"] = 0;
					$this->data["othersms"] = 0;

					$this->data["subview"] = "mailandsms/add";
					$this->load->view('_layout_main', $this->data);
				} else {
					$usertypeID = $this->input->post('email_usertypeID');
					$schoolyearID = $this->input->post('email_schoolyear');

					if($usertypeID == 1) { /* FOR ADMIN */
						$systemadminID = $this->input->post('email_users');
						if($systemadminID == 'select') {
							$message = $this->input->post('email_message');
							$multisystemadmins = $this->systemadmin_m->get_systemadmin();
							if(customCompute($multisystemadmins)) {
								$countusers = '';
								foreach ($multisystemadmins as $key => $multisystemadmin) {
									$this->userConfigEmail($message, $multisystemadmin, $usertypeID, $schoolyearID);
									$countusers .= $multisystemadmin->name .' ,';
								}
								$array = array(
									'usertypeID' => $usertypeID,
									'users' => $countusers,
									'type' => ucfirst((string) $this->input->post('type')),
									'message' => $this->input->post('email_message'),
									'year' => date('Y'),
									'senderusertypeID' => $this->session->userdata('usertypeID'),
									'senderID' => $this->session->userdata('loginuserID')
								);
								$this->mailandsms_m->insert_mailandsms($array);
								redirect(base_url('mailandsms/index'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						} else {
							$message = $this->input->post('email_message');
							$singlesystemadmin = $this->systemadmin_m->get_systemadmin($systemadminID);
							if(customCompute($singlesystemadmin)) {
								$this->userConfigEmail($message, $singlesystemadmin, $usertypeID);
								$array = array(
									'usertypeID' => $usertypeID,
									'users' => $singlesystemadmin->name,
									'type' => ucfirst((string) $this->input->post('type')),
									'message' => $this->input->post('email_message'),
									'year' => date('Y'),
									'senderusertypeID' => $this->session->userdata('usertypeID'),
									'senderID' => $this->session->userdata('loginuserID')
								);
								$this->mailandsms_m->insert_mailandsms($array);
								redirect(base_url('mailandsms/index'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					} elseif($usertypeID == 2) { /* FOR TEACHER */
						$teacherID = $this->input->post('email_users');
						if($teacherID == 'select') {
							$message = $this->input->post('email_message');
							$multiteachers = $this->teacher_m->general_get_teacher();
							if(customCompute($multiteachers)) {
								$countusers = '';
								foreach ($multiteachers as $key => $multiteacher) {
									$this->userConfigEmail($message, $multiteacher, $usertypeID);
									$countusers .= $multiteacher->name .' ,';
								}
								$array = array(
									'usertypeID' => $usertypeID,
									'users' => $countusers,
									'type' => ucfirst((string) $this->input->post('type')),
									'message' => $this->input->post('email_message'),
									'year' => date('Y'),
									'senderusertypeID' => $this->session->userdata('usertypeID'),
									'senderID' => $this->session->userdata('loginuserID')
								);
								$this->mailandsms_m->insert_mailandsms($array);
								redirect(base_url('mailandsms/index'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						} else {
							$message = $this->input->post('email_message');
							$singleteacher = $this->teacher_m->general_get_teacher($teacherID);
							if(customCompute($singleteacher)) {
								$this->userConfigEmail($message, $singleteacher, $usertypeID);
								$array = array(
									'usertypeID' => $usertypeID,
									'users' => $singleteacher->name,
									'type' => ucfirst((string) $this->input->post('type')),
									'message' => $this->input->post('email_message'),
									'year' => date('Y'),
									'senderusertypeID' => $this->session->userdata('usertypeID'),
									'senderID' => $this->session->userdata('loginuserID')
								);
								$this->mailandsms_m->insert_mailandsms($array);
								redirect(base_url('mailandsms/index'));

							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					} elseif($usertypeID == 3) { /* FOR STUDENT */
						$studentID = $this->input->post('email_users');
						if($studentID == 'select') {
							$class = $this->input->post('email_class');
							if($class == 'select') {
								/* Multi School Year */
								$schoolyear = $this->input->post('email_schoolyear');
								if($schoolyear == 'select') {
									$message = $this->input->post('email_message');
									$multiSchoolYearStudents = $this->studentrelation_m->general_get_student(TRUE);
									if(customCompute($multiSchoolYearStudents)) {
										$countusers = '';
										foreach ($multiSchoolYearStudents as $key => $multiSchoolYearStudent) {
											$this->userConfigEmail($message, $multiSchoolYearStudent, $usertypeID, $multiSchoolYearStudent->srschoolyearID);
											$countusers .= $multiSchoolYearStudent->srname .' ,';
										}
										$array = array(
											'usertypeID' => $usertypeID,
											'users' => $countusers,
											'type' => ucfirst((string) $this->input->post('type')),
											'message' => $this->input->post('email_message'),
											'year' => date('Y'),
											'senderusertypeID' => $this->session->userdata('usertypeID'),
											'senderID' => $this->session->userdata('loginuserID')
										);
										$this->mailandsms_m->insert_mailandsms($array);
										redirect(base_url('mailandsms/index'));
									} else {
										$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
										redirect(base_url('mailandsms/add'));
									}
								} else {
									/* Single school Year Student */
									$message = $this->input->post('email_message');
									$singleSchoolYear = $this->input->post('email_schoolyear');
									$singleSchoolYearStudents = $this->studentrelation_m->general_get_order_by_student(array('srschoolyearID' => $singleSchoolYear), TRUE);
									if(customCompute($singleSchoolYearStudents)) {
										$countusers = '';
										foreach ($singleSchoolYearStudents as $key => $singleSchoolYearStudent) {
											$this->userConfigEmail($message, $singleSchoolYearStudent, $usertypeID, $schoolyearID);
											$countusers .= $singleSchoolYearStudent->srname .' ,';
										}
										$array = array(
											'usertypeID' => $usertypeID,
											'users' => $countusers,
											'type' => ucfirst((string) $this->input->post('type')),
											'message' => $this->input->post('email_message'),
											'year' => date('Y'),
											'senderusertypeID' => $this->session->userdata('usertypeID'),
											'senderID' => $this->session->userdata('loginuserID')
										);
										$this->mailandsms_m->insert_mailandsms($array);
										redirect(base_url('mailandsms/index'));
									} else {
										$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
										redirect(base_url('mailandsms/add'));
									}
								}
							} else {
								/* Single Class Student */
								$message = $this->input->post('email_message');
								$singleClass = $this->input->post('email_class');
								$singleSection = $this->input->post('email_section');
								if((int)$singleSection !== 0){
                                    $singleClassStudents = $this->studentrelation_m->general_get_order_by_student(array('srclassesID' => $singleClass,'srsectionID' => $singleSection, 'srschoolyearID' => $schoolyearID), TRUE);

                                }else {
                                    $singleClassStudents = $this->studentrelation_m->general_get_order_by_student(array('srclassesID' => $singleClass, 'srschoolyearID' => $schoolyearID), TRUE);
                                }

								if(customCompute($singleClassStudents)) {
									$countusers = '';
									foreach ($singleClassStudents as $key => $singleClassStudent) {
										$this->userConfigEmail($message, $singleClassStudent, $usertypeID, $schoolyearID);
										$countusers .= $singleClassStudent->srname .' ,';
									}
									$array = array(
										'usertypeID' => $usertypeID,
										'users' => $countusers,
										'type' => ucfirst((string) $this->input->post('type')),
										'message' => $this->input->post('email_message'),
										'year' => date('Y'),
										'senderusertypeID' => $this->session->userdata('usertypeID'),
										'senderID' => $this->session->userdata('loginuserID')
									);
									$this->mailandsms_m->insert_mailandsms($array);
									redirect(base_url('mailandsms/index'));
								} else {
									$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
									redirect(base_url('mailandsms/add'));
								}
							}
						} else {
							/* Single Student */
							$message = $this->input->post('email_message');
							$singlestudent = $this->studentrelation_m->general_get_single_student(array('srstudentID' => $studentID, 'srschoolyearID' => $schoolyearID), TRUE);
							if(customCompute($singlestudent)) {
								$this->userConfigEmail($message, $singlestudent, $usertypeID, $schoolyearID);
								$array = array(
									'usertypeID' => $usertypeID,
									'users' => $singlestudent->srname,
									'type' => ucfirst((string) $this->input->post('type')),
									'message' => $this->input->post('email_message'),
									'year' => date('Y'),
									'senderusertypeID' => $this->session->userdata('usertypeID'),
									'senderID' => $this->session->userdata('loginuserID')
								);

								$this->mailandsms_m->insert_mailandsms($array);
								redirect(base_url('mailandsms/index'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					} elseif($usertypeID == 4) { /* FOR PARENTS */
						$parentsID = $this->input->post('email_users');
						if($parentsID == 'select') {
							$message = $this->input->post('email_message');
							$multiparents = $this->parents_m->get_parents();
							if(customCompute($multiparents)) {
								$countusers = '';
								foreach ($multiparents as $key => $multiparent) {
									$this->userConfigEmail($message, $multiparent, $usertypeID);
									$countusers .= $multiparent->name .' ,';
								}
								$array = array(
									'usertypeID' => $usertypeID,
									'users' => $countusers,
									'type' => ucfirst((string) $this->input->post('type')),
									'message' => $this->input->post('email_message'),
									'year' => date('Y'),
									'senderusertypeID' => $this->session->userdata('usertypeID'),
									'senderID' => $this->session->userdata('loginuserID')
								);
								$this->mailandsms_m->insert_mailandsms($array);
								redirect(base_url('mailandsms/index'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						} else {
							$message = $this->input->post('email_message');
							$singleparent = $this->parents_m->get_parents($parentsID);
							if(customCompute($singleparent)) {
								$this->userConfigEmail($message, $singleparent, $usertypeID);
								$array = array(
									'usertypeID' => $usertypeID,
									'users' => $singleparent->name,
									'type' => ucfirst((string) $this->input->post('type')),
									'message' => $this->input->post('email_message'),
									'year' => date('Y'),
									'senderusertypeID' => $this->session->userdata('usertypeID'),
									'senderID' => $this->session->userdata('loginuserID')
								);
								$this->mailandsms_m->insert_mailandsms($array);
								redirect(base_url('mailandsms/index'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					} else { /* FOR ALL USERS */
						$userID = $this->input->post('email_users');
						if($userID == 'select') {
							$message = $this->input->post('email_message');
							$multiusers = $this->user_m->get_order_by_user(array('usertypeID' => $usertypeID));
							if(customCompute($multiusers)) {
								$countusers = '';
								foreach ($multiusers as $key => $multiuser) {
									$this->userConfigEmail($message, $multiuser, $usertypeID);
									$countusers .= $multiuser->name .' ,';
								}
								$array = array(
									'usertypeID' => $usertypeID,
									'users' => $countusers,
									'type' => ucfirst((string) $this->input->post('type')),
									'message' => $this->input->post('email_message'),
									'year' => date('Y'),
									'senderusertypeID' => $this->session->userdata('usertypeID'),
									'senderID' => $this->session->userdata('loginuserID')
								);
								$this->mailandsms_m->insert_mailandsms($array);
								redirect(base_url('mailandsms/index'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						} else {
							$message = $this->input->post('email_message');
							$singleuser = $this->user_m->get_user($userID);
							if(customCompute($singleuser)) {
								$this->userConfigEmail($message, $singleuser, $usertypeID);
								$array = array(
									'usertypeID' => $usertypeID,
									'users' => $singleuser->name,
									'type' => ucfirst((string) $this->input->post('type')),
									'message' => $this->input->post('email_message'),
									'year' => date('Y'),
									'senderusertypeID' => $this->session->userdata('usertypeID'),
									'senderID' => $this->session->userdata('loginuserID')
								);
								$this->mailandsms_m->insert_mailandsms($array);
								redirect(base_url('mailandsms/index'));
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					}
				}
			} elseif($this->input->post('type') == "sms") {
				$rules = $this->rules_sms();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$this->data['smsUserID'] = $this->input->post('sms_users');
					$this->data['smsTemplateID'] = $this->input->post('sms_template');

					$this->data['allStudents'] = $this->studentrelation_m->get_order_by_student(array('srschoolyearID' => $this->input->post('sms_schoolyear'), 'srclassesID' => $this->input->post('sms_class')));

					$this->data['emailUserID'] = 0;
					$this->data['emailTemplateID'] = 0;

					$this->data["email"] = 0;
					$this->data["sms"] = 1;
					$this->data["otheremail"] = 0;
					$this->data["othersms"] = 0;

					$this->data["subview"] = "mailandsms/add";
					$this->load->view('_layout_main', $this->data);
				} else {
					$getway = $this->input->post('sms_getway');
					$usertypeID = $this->input->post('sms_usertypeID');
					$schoolyearID = $this->input->post('sms_schoolyear');

					if($usertypeID == 1) { /* FOR ADMIN */
						$systemadminID = $this->input->post('sms_users');
						if($systemadminID == 'select') {
							$countusers = '';
							$retval = 1;
							$retmess = '';

							$message = $this->input->post('sms_message');
							$multisystemadmins = $this->systemadmin_m->get_systemadmin();
							if(customCompute($multisystemadmins)) {

								foreach ($multisystemadmins as $key => $multisystemadmin) {
									$status = $this->userConfigSMS($message, $multisystemadmin, $usertypeID, $getway);
									$countusers .= $multisystemadmin->name .' ,';

									if($status['check'] == FALSE) {
										$retval = 0;
										$retmess = $status['message'];
										break;
									}

								}
								if($retval == 1) {
									$array = array(
										'usertypeID' => $usertypeID,
										'users' => $countusers,
										'type' => ucfirst((string) $this->input->post('type')),
										'message' => $this->input->post('sms_message'),
										'year' => date('Y'),
										'senderusertypeID' => $this->session->userdata('usertypeID'),
										'senderID' => $this->session->userdata('loginuserID')
									);
									$this->mailandsms_m->insert_mailandsms($array);
									redirect(base_url('mailandsms/index'));
								} else {
									$this->session->set_flashdata('error', $retmess);
									redirect(base_url("mailandsms/add"));
								}
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						} else {
							$retval = 1;
							$retmess = '';
							$message = $this->input->post('sms_message');
							$singlesystemadmin = $this->systemadmin_m->get_systemadmin($systemadminID);
							if(customCompute($singlesystemadmin)) {
								$status = $this->userConfigSMS($message, $singlesystemadmin, $usertypeID, $getway);
								if($status['check'] == FALSE) {
									$retval = 0;
									$retmess = $status['message'];
								}

								if($retval == 1) {
									$array = array(
										'usertypeID' => $usertypeID,
										'users' => $singlesystemadmin->name,
										'type' => ucfirst((string) $this->input->post('type')),
										'message' => $this->input->post('sms_message'),
										'year' => date('Y'),
										'senderusertypeID' => $this->session->userdata('usertypeID'),
										'senderID' => $this->session->userdata('loginuserID')
									);
									$this->mailandsms_m->insert_mailandsms($array);
									redirect(base_url('mailandsms/index'));
								} else {
									$this->session->set_flashdata('error', $retmess);
									redirect(base_url("mailandsms/add"));
								}
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					} elseif($usertypeID == 2) { /* FOR TEACHER */
						$teacherID = $this->input->post('sms_users');
						if($teacherID == 'select') {
							$message = $this->input->post('sms_message');
							$multiteachers = $this->teacher_m->general_get_teacher();
							if(customCompute($multiteachers)) {
								$countusers = '';
								$retval = 1;
								$retmess = '';
								foreach ($multiteachers as $key => $multiteacher) {
									$status = $this->userConfigSMS($message, $multiteacher, $usertypeID, $getway);
									$countusers .= $multiteacher->name .' ,';

									if($status['check'] == FALSE) {
										$retval = 0;
										$retmess = $status['message'];
										break;
									}

								}
								if($retval == 1) {
									$array = array(
										'usertypeID' => $usertypeID,
										'users' => $countusers,
										'type' => ucfirst((string) $this->input->post('type')),
										'message' => $this->input->post('sms_message'),
										'year' => date('Y'),
										'senderusertypeID' => $this->session->userdata('usertypeID'),
										'senderID' => $this->session->userdata('loginuserID')
									);
									$this->mailandsms_m->insert_mailandsms($array);
									redirect(base_url('mailandsms/index'));
								} else {
									$this->session->set_flashdata('error', $retmess);
									redirect(base_url("mailandsms/add"));
								}
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						} else {
							$retval = 1;
							$retmess = '';
							$message = $this->input->post('sms_message');
							$singleteacher = $this->teacher_m->general_get_teacher($teacherID);
							if(customCompute($singleteacher)) {
								$status = $this->userConfigSMS($message, $singleteacher, $usertypeID, $getway);
								if($status['check'] == FALSE) {
									$retval = 0;
									$retmess = $status['message'];
								}

								if($retval == 1) {
									$array = array(
										'usertypeID' => $usertypeID,
										'users' => $singleteacher->name,
										'type' => ucfirst((string) $this->input->post('type')),
										'message' => $this->input->post('sms_message'),
										'year' => date('Y'),
										'senderusertypeID' => $this->session->userdata('usertypeID'),
										'senderID' => $this->session->userdata('loginuserID')
									);
									$this->mailandsms_m->insert_mailandsms($array);
									redirect(base_url('mailandsms/index'));
								} else {
									$this->session->set_flashdata('error', $retmess);
									redirect(base_url("mailandsms/add"));
								}
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					} elseif($usertypeID == 3) { /* FOR STUDENT */

						$studentID = $this->input->post('sms_users');
						if($studentID == 'select') {
							$class = $this->input->post('sms_class');
							if($class == 'select') {
								/* Multi School Year */
								$countusers = '';
								$retval = 1;
								$retmess = '';

								$schoolyear = $this->input->post('sms_schoolyear');
								if($schoolyear == 'select') {
									$message = $this->input->post('sms_message');
									$multiSchoolYearStudents = $this->studentrelation_m->general_get_student(TRUE);
									if(customCompute($multiSchoolYearStudents)) {
										foreach ($multiSchoolYearStudents as $key => $multiSchoolYearStudent) {
											$status = $this->userConfigSMS($message, $multiSchoolYearStudent, $usertypeID, $getway, $multiSchoolYearStudent->srschoolyearID);
											$countusers .= $multiSchoolYearStudent->srname .' ,';
											if($status['check'] == FALSE) {
												$retval = 0;
												$retmess = $status['message'];
												break;
											}
										}

										if($retval == 1) {
											$array = array(
												'usertypeID' => $usertypeID,
												'users' => $countusers,
												'type' => ucfirst((string) $this->input->post('type')),
												'message' => $this->input->post('sms_message'),
												'year' => date('Y'),
												'senderusertypeID' => $this->session->userdata('usertypeID'),
												'senderID' => $this->session->userdata('loginuserID')
											);
											$this->mailandsms_m->insert_mailandsms($array);
											redirect(base_url('mailandsms/index'));
										} else {
											$this->session->set_flashdata('error', $retmess);
											redirect(base_url('mailandsms/add'));
										}
									} else {
										$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
										redirect(base_url('mailandsms/add'));
									}
								} else {
									/* Single school Year Student */
									$countusers = '';
									$retval = 1;
									$retmess = '';
									$message = $this->input->post('sms_message');
									$singleSchoolYear = $this->input->post('sms_schoolyear');
									$singleSchoolYearStudents = $this->studentrelation_m->general_get_order_by_student(array('srschoolyearID' => $singleSchoolYear), TRUE);
									if(customCompute($singleSchoolYearStudents)) {
										foreach ($singleSchoolYearStudents as $key => $singleSchoolYearStudent) {
											$status = $this->userConfigSMS($message, $singleSchoolYearStudent, $usertypeID, $getway, $schoolyearID);
											$countusers .= $singleSchoolYearStudent->srname .' ,';
											if($status['check'] == FALSE) {
												$retval = 0;
												$retmess = $status['message'];
												break;
											}
										}
										if($retval == 1) {
											$array = array(
												'usertypeID' => $usertypeID,
												'users' => $countusers,
												'type' => ucfirst((string) $this->input->post('type')),
												'message' => $this->input->post('sms_message'),
												'year' => date('Y'),
												'senderusertypeID' => $this->session->userdata('usertypeID'),
												'senderID' => $this->session->userdata('loginuserID')
											);
											$this->mailandsms_m->insert_mailandsms($array);
											redirect(base_url('mailandsms/index'));
										} else {
											$this->session->set_flashdata('error', $retmess);
											redirect(base_url("mailandsms/add"));
										}
									} else {
										$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
										redirect(base_url('mailandsms/add'));
									}
								}
							} else {
								/* Single Class Student */
								$countusers = '';
								$retval = 1;
								$retmess = '';

								$message = $this->input->post('sms_message');
								$singleClass = $this->input->post('sms_class');
                                $singleSection = $this->input->post('sms_section');
                                if((int)$singleSection !== 0){
                                    $singleClassStudents = $this->studentrelation_m->general_get_order_by_student(array('srclassesID' => $singleClass,'srsectionID' => $singleSection, 'srschoolyearID' => $schoolyearID), TRUE);

                                }else {
                                    $singleClassStudents = $this->studentrelation_m->general_get_order_by_student(array('srclassesID' => $singleClass, 'srschoolyearID' => $schoolyearID), TRUE);
                                }
								if(customCompute($singleClassStudents)) {
									$countusers = '';
									foreach ($singleClassStudents as $key => $singleClassStudent) {
										$status = $this->userConfigSMS($message, $singleClassStudent, $usertypeID, $getway, $schoolyearID);
										$countusers .= $singleClassStudent->srname .' ,';
										if($status['check'] == FALSE) {
											$retval = 0;
											$retmess = $status['message'];
											break;
										}
									}

									if($retval == 1) {
										$array = array(
											'usertypeID' => $usertypeID,
											'users' => $countusers,
											'type' => ucfirst((string) $this->input->post('type')),
											'message' => $this->input->post('sms_message'),
											'year' => date('Y'),
											'senderusertypeID' => $this->session->userdata('usertypeID'),
											'senderID' => $this->session->userdata('loginuserID')
										);
										$this->mailandsms_m->insert_mailandsms($array);
										redirect(base_url('mailandsms/index'));
									} else {
										$this->session->set_flashdata('error', $retmess);
										redirect(base_url("mailandsms/add"));
									}
								} else {
									$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
									redirect(base_url('mailandsms/add'));
								}
							}
						} else {
							/* Single Student */
							$retval = 1;
							$retmess = '';

							$message = $this->input->post('sms_message');
							$singlestudent = $this->studentrelation_m->general_get_single_student(array('srstudentID' => $studentID, 'srschoolyearID' => $schoolyearID), TRUE);
							if(customCompute($singlestudent)) {
								$status = $this->userConfigSMS($message, $singlestudent, $usertypeID, $getway, $schoolyearID);
								if($status['check'] == FALSE) {
									$retval = 0;
									$retmess = $status['message'];
								}
								if($retval == 1) {
									$array = array(
										'usertypeID' => $usertypeID,
										'users' =>  $singlestudent->srname,
										'type' => ucfirst((string) $this->input->post('type')),
										'message' => $this->input->post('sms_message'),
										'year' => date('Y'),
										'senderusertypeID' => $this->session->userdata('usertypeID'),
										'senderID' => $this->session->userdata('loginuserID')
									);
									$this->mailandsms_m->insert_mailandsms($array);
									redirect(base_url('mailandsms/index'));
								} else {
									$this->session->set_flashdata('error', $retmess);
									redirect(base_url("mailandsms/add"));
								}
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					} elseif($usertypeID == 4) { /* FOR PARENTS */
						$parentsID = $this->input->post('sms_users');
						if($parentsID == 'select') {
							$countusers = '';
							$retval = 1;
							$retmess = '';

							$message = $this->input->post('sms_message');
							$multiparents = $this->parents_m->get_parents();
							if(customCompute($multiparents)) {

								foreach ($multiparents as $key => $multiparent) {
									$status = $this->userConfigSMS($message, $multiparent, $usertypeID, $getway);
									$countusers .= $multiparent->name .' ,';

									if($status['check'] == FALSE) {
										$retval = 0;
										$retmess = $status['message'];
										break;
									}
								}

								if($retval == 1) {
									$array = array(
										'usertypeID' => $usertypeID,
										'users' => $countusers,
										'type' => ucfirst((string) $this->input->post('type')),
										'message' => $this->input->post('sms_message'),
										'year' => date('Y'),
										'senderusertypeID' => $this->session->userdata('usertypeID'),
										'senderID' => $this->session->userdata('loginuserID')
									);
									$this->mailandsms_m->insert_mailandsms($array);
									redirect(base_url('mailandsms/index'));
								} else {
									$this->session->set_flashdata('error', $retmess);
									redirect(base_url("mailandsms/add"));
								}
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						} else {
							$retval = 1;
							$retmess = '';

							$message = $this->input->post('sms_message');
							$singleparent = $this->parents_m->get_parents($parentsID);
							if(customCompute($singleparent)) {
								$status = $this->userConfigSMS($message, $singleparent, $usertypeID, $getway);
								if($status['check'] == FALSE) {
									$retval = 0;
									$retmess = $status['message'];

								}

								if($retval == 1) {
									$array = array(
										'usertypeID' => $usertypeID,
										'users' => $singleparent->name,
										'type' => ucfirst((string) $this->input->post('type')),
										'message' => $this->input->post('sms_message'),
										'year' => date('Y'),
										'senderusertypeID' => $this->session->userdata('usertypeID'),
										'senderID' => $this->session->userdata('loginuserID')
									);
									$this->mailandsms_m->insert_mailandsms($array);
									redirect(base_url('mailandsms/index'));
								} else {
									$this->session->set_flashdata('error', $retmess);
									redirect(base_url("mailandsms/add"));
								}

							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					} else { /* FOR ALL USERS */
						$userID = $this->input->post('sms_users');
						if($userID == 'select') {
							$countusers = '';
							$retval = 1;
							$retmess = '';
							$message = $this->input->post('sms_message');
							$multiusers = $this->user_m->get_order_by_user(array('usertypeID' => $usertypeID));
							if(customCompute($multiusers)) {
								foreach ($multiusers as $key => $multiuser) {
									$status = $this->userConfigSMS($message, $multiuser, $usertypeID, $getway);
									$countusers .= $multiuser->name .' ,';

									if($status['check'] == FALSE) {
										$retval = 0;
										$retmess = $status['message'];
										break;
									}
								}

								if($retval == 1) {
									$array = array(
										'usertypeID' => $usertypeID,
										'users' => $countusers,
										'type' => ucfirst((string) $this->input->post('type')),
										'message' => $this->input->post('sms_message'),
										'year' => date('Y'),
										'senderusertypeID' => $this->session->userdata('usertypeID'),
										'senderID' => $this->session->userdata('loginuserID')
									);
									$this->mailandsms_m->insert_mailandsms($array);
									redirect(base_url('mailandsms/index'));
								} else {
									$this->session->set_flashdata('error', $retmess);
									redirect(base_url("mailandsms/add"));
								}
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						} else {
							$retval = 1;
							$retmess = '';
							$message = $this->input->post('sms_message');
							$singleuser = $this->user_m->get_user($userID);
							if(customCompute($singleuser)) {
								$status = $this->userConfigSMS($message, $singleuser, $usertypeID, $getway);
								if($status['check'] == FALSE) {
									$retval = 0;
									$retmess = $status['message'];
								}

								if($retval == 1) {
									$array = array(
										'usertypeID' => $usertypeID,
										'users' => $singleuser->name,
										'type' => ucfirst((string) $this->input->post('type')),
										'message' => $this->input->post('sms_message'),
										'year' => date('Y'),
										'senderusertypeID' => $this->session->userdata('usertypeID'),
										'senderID' => $this->session->userdata('loginuserID')
									);
									$this->mailandsms_m->insert_mailandsms($array);
									redirect(base_url('mailandsms/index'));
								} else {
									$this->session->set_flashdata('error', $retmess);
									redirect(base_url("mailandsms/add"));
								}
							} else {
								$this->session->set_flashdata('error', $this->lang->line('mailandsms_notfound_error'));
								redirect(base_url('mailandsms/add'));
							}
						}
					}
				}
			} elseif($this->input->post('type') == "otheremail") {
				$rules = $this->rules_otheremail();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					
					$this->data['emailUserID'] = 0;
					$this->data['emailTemplateID'] = 0;
					$this->data['allStudents'] = [];
					$this->data['smsUserID'] = 0;
					$this->data['smsTemplateID'] = 0;

					$this->data["email"] = 0;
					$this->data["sms"] = 0;
					$this->data["otheremail"] = 1;
					$this->data["othersms"] = 0;

					$this->data["subview"] = "mailandsms/add";
					$this->load->view('_layout_main', $this->data);
				} else {
					$email   = $this->input->post('otheremail_email');
					$subject = $this->input->post('otheremail_subject');
					$message = $this->input->post('otheremail_message');

					$file_name = $_FILES["file"]['name'];
					$random = random19();
					$makeRandom = hash('sha512', $random.(strtotime(date('Y-m-d H:i:s'))). config_item("encryption_key"));
					$file_name_rename = $makeRandom;
					$explode = explode('.', (string) $file_name);
					if(customCompute($explode) >= 2) {
					$new_file = $file_name_rename.'.'.end($explode);
					$config['upload_path'] = "./uploads/attachment";
					$config['allowed_types'] = "gif|jpg|png|jpeg|pdf|doc|xml|docx|GIF|JPG|PNG|JPEG|PDF|DOC|XML|DOCX|xls|xlsx|txt|ppt|csv";
					$config['file_name'] = $new_file;
					$config['max_size'] = '5120';
					$config['max_width'] = '10000';
					$config['max_height'] = '10000';
					$this->load->library('upload', $config);
						if ($this->upload->do_upload('file')) {
							$upload_data = $this->upload->data();
							$attachment_path = $upload_data['full_path'];
						} else {
							$attachment_path = null;
						}
					}
					$result  = $this->inilabs->sendMailSystem($email, $subject, $message, $attachment_path);
					if($result) {
						$array = array(
							'usertypeID' => '0',
							'users' => $this->input->post('otheremail_name'),
							'type' => ucfirst((string) $this->lang->line('mailandsms_otheremail')),
							'message' => $this->input->post('otheremail_message'),
							'year' => date('Y'),
							'senderusertypeID' => $this->session->userdata('usertypeID'),
							'senderID' => $this->session->userdata('loginuserID')
						);
						$this->mailandsms_m->insert_mailandsms($array);
						if ($attachment_path !== null) {
							unlink($attachment_path);
						}
						$this->session->set_flashdata('success', $this->lang->line('mail_success'));
						redirect(base_url('mailandsms/index'));
					} else {
						$this->session->set_flashdata('error', $this->lang->line('mail_error'));
						redirect(base_url("mailandsms/add"));
					}
				}
			} elseif($this->input->post('type') == "othersms") {
				$rules = $this->rules_othersms();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {

					$this->data['emailUserID'] = 0;
					$this->data['emailTemplateID'] = 0;
					$this->data['allStudents'] = [];
					$this->data['smsUserID'] = 0;
					$this->data['smsTemplateID'] = 0;

					$this->data["email"] = 0;
					$this->data["sms"] = 0;
					$this->data["otheremail"] = 0;
					$this->data["othersms"] = 1;

					$this->data["subview"] = "mailandsms/add";
					$this->load->view('_layout_main', $this->data);
				} else {
					$to = $this->input->post('othersms_phone');
					$getway = $this->input->post('sms_getway');
					$message = $this->input->post('othersms_message');

					$result = $this->allgetway_send_message($getway, $to, $message);
					if($result['check']) {
						$array = array(
							'usertypeID' => '0',
							'users' => $this->input->post('othersms_name'),
							'type' => ucfirst((string) $this->lang->line('mailandsms_othersms')),
							'message' => $this->input->post('othersms_message'),
							'year' => date('Y'),
							'senderusertypeID' => $this->session->userdata('usertypeID'),
							'senderID' => $this->session->userdata('loginuserID')
						);
						$this->mailandsms_m->insert_mailandsms($array);
						redirect(base_url('mailandsms/index'));
					} else {
						$retmess = isset($result['message']) ? $result['message'] : $this->lang->line('mailandsms_error');
						$this->session->set_flashdata('error', $retmess);
						redirect(base_url("mailandsms/add"));
					}
				}
			} else {
				redirect('mainandsms/add');
			}
		} else {
			$this->data['emailUserID'] = 0;
			$this->data['emailTemplateID'] = 0;

			$this->data['smsUserID'] = 0;
			$this->data['smsTemplateID'] = 0;

			$this->data["email"] = 1;
			$this->data["sms"] = 0;
			$this->data["otheremail"] = 0;
			$this->data["othersms"] = 0;
			$this->data['submittype'] = 'none';

			$this->data['allStudents'] = array();
			$this->data["subview"] = "mailandsms/add";
			$this->load->view('_layout_main', $this->data);
		}
	}

	private function userConfigEmail($message, $user, $usertypeID, $schoolyearID = 1) {
		if($user && $usertypeID) {
			$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => $usertypeID));

			if($usertypeID == 2) {
				$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => 2));
			} elseif($usertypeID == 3) {
				$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => 3));
			} elseif($usertypeID == 4) {
				$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => 4));
			} else {
				$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => 1));
			}

			$message = $this->tagConvertor($userTags, $user, $message, 'email', $schoolyearID);

			if($user->email) {
				$subject = $this->input->post('email_subject');
				$email = $user->email;

				$emailsetting = $this->emailsetting_m->get_emailsetting();
				$this->email->set_mailtype("html");
				if(customCompute($emailsetting)) {
					if($emailsetting->email_engine == 'smtp') {
						$config = array(
						    'protocol'  => 'smtp',
						    'smtp_host' => $emailsetting->smtp_server,
						    'smtp_port' => $emailsetting->smtp_port,
						    'smtp_user' => $emailsetting->smtp_username,
						    'smtp_pass' => $emailsetting->smtp_password,
						    'mailtype'  => 'html',
						    'charset'   => 'utf-8'
						);
						$this->email->initialize($config);
						$this->email->set_newline("\r\n");
					}

					$file_name = $_FILES["file"]['name'];
					$random = random19();
					$makeRandom = hash('sha512', $random.(strtotime(date('Y-m-d H:i:s'))). config_item("encryption_key"));
					$file_name_rename = $makeRandom;
					$explode = explode('.', (string) $file_name);
					if(customCompute($explode) >= 2) {
					$new_file = $file_name_rename.'.'.end($explode);
					$config['upload_path'] = "./uploads/attachment";
					$config['allowed_types'] = "gif|jpg|png|jpeg|pdf|doc|xml|docx|GIF|JPG|PNG|JPEG|PDF|DOC|XML|DOCX|xls|xlsx|txt|ppt|csv";
					$config['file_name'] = $new_file;
					$config['max_size'] = '5120';
					$config['max_width'] = '10000';
					$config['max_height'] = '10000';
					$this->load->library('upload', $config);
						if ($this->upload->do_upload('file')) {
							$upload_data = $this->upload->data();
							$attachment_path = $upload_data['full_path'];
						} else {
							$attachment_path = null;
						}
					}
					$this->email->to($email);
					$this->email->from($this->data['siteinfos']->email, $this->data['siteinfos']->sname);
					$this->email->subject($subject);
					$this->email->message($message);

					if ($attachment_path !== null) {
						$this->email->attach($attachment_path);
					}
					if($this->email->send()) {
						if ($attachment_path !== null) {
							unlink($attachment_path);
						}
						$this->session->set_flashdata('success', $this->lang->line('mail_success'));
					} else {
						$this->session->set_flashdata('error', $this->lang->line('mail_error'));
					}
				}
			}
		}
	}

	private function userConfigSMS($message, $user, $usertypeID, $getway, $schoolyearID = 1) {
		if($user && $usertypeID) {
			$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => $usertypeID));

			if($usertypeID == 2) {
				$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => 2));
			} elseif($usertypeID == 3) {
				$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => 3));
			} elseif($usertypeID == 4) {
				$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => 4));
			} else {
				$userTags = $this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => 1));
			}

			$message = $this->tagConvertor($userTags, $user, $message, 'SMS', $schoolyearID);
			if($user->phone) {
				return $this->allgetway_send_message($getway, $user->phone, $message);
			} else {
				return array('check' => TRUE);
			}
		}
	}

	private function tagConvertor($userTags, $user, $message, $sendType, $schoolyearID) {
		if(customCompute($userTags)) {
			foreach ($userTags as $key => $userTag) {
				if ($userTag->tagname == '[name]') {
        $message = $user->name ? str_replace('[name]', $user->name, (string) $message) : str_replace('[name]', ' ', (string) $message);
    } elseif($userTag->tagname == '[designation]') {
					if($user->designation) {
						$message = str_replace('[designation]', $user->designation, (string) $message);
					} else {
						$message = str_replace('[designation]', ' ', (string) $message);
					}
				} elseif($userTag->tagname == '[dob]') {
					if($user->dob) {
						$dob =  date("d M Y", strtotime((string) $user->dob));
						$message = str_replace('[dob]', $dob, (string) $message);
					} else {
						$message = str_replace('[dob]', ' ', (string) $message);
					}
				} elseif ($userTag->tagname == '[gender]') {
        $message = $user->sex ? str_replace('[gender]', $user->sex, (string) $message) : str_replace('[gender]', ' ', (string) $message);
    } elseif($userTag->tagname == '[religion]') {
					if($user->religion) {
						$message = str_replace('[religion]', $user->religion, (string) $message);
					} else {
						$message = str_replace('[religion]', ' ', (string) $message);
					}
				} elseif ($userTag->tagname == '[email]') {
        $message = $user->email ? str_replace('[email]', $user->email, (string) $message) : str_replace('[email]', ' ', (string) $message);
    } elseif ($userTag->tagname == '[phone]') {
        $message = $user->phone ? str_replace('[phone]', $user->phone, (string) $message) : str_replace('[phone]', ' ', (string) $message);
    } elseif($userTag->tagname == '[address]') {
					if($user->address) {
						$message = str_replace('[address]', $user->address, (string) $message);
					} else {
						$message = str_replace('[address]', ' ', (string) $message);
					}
				} elseif($userTag->tagname == '[jod]') {
					if($user->jod) {
						$jod =  date("d M Y", strtotime((string) $user->jod));
						$message = str_replace('[jod]', $jod, (string) $message);
					} else {
						$message = str_replace('[jod]', ' ', (string) $message);
					}
				} elseif($userTag->tagname == '[username]') {
					if($user->username) {
						$message = str_replace('[username]', $user->username, (string) $message);
					} else {
						$message = str_replace('[username]', ' ', (string) $message);
					}
				} elseif($userTag->tagname == "[father's_name]") {
					if($user->father_name) {
						$message = str_replace("[father's_name]", $user->father_name, (string) $message);
					} else {
						$message = str_replace("[father's_name]", ' ', (string) $message);
					}
				} elseif($userTag->tagname == "[mother's_name]") {
					if($user->mother_name) {
						$message = str_replace("[mother's_name]", $user->mother_name, (string) $message);
					} else {
						$message = str_replace("[mother's_name]", ' ', (string) $message);
					}
				} elseif($userTag->tagname == "[father's_profession]") {
					if($user->father_profession) {
						$message = str_replace("[father's_profession]", $user->father_profession, (string) $message);
					} else {
						$message = str_replace("[father's_profession]", ' ', (string) $message);
					}
				} elseif($userTag->tagname == "[mother's_profession]") {
					if($user->mother_profession) {
						$message = str_replace("[mother's_profession]", $user->mother_profession, (string) $message);
					} else {
						$message = str_replace("[mother's_profession]", ' ', (string) $message);
					}
				} elseif($userTag->tagname == '[class]') {
					$classes = $this->classes_m->general_get_classes($user->srclassesID);
					if(customCompute($classes)) {
						$message = str_replace('[class]', $classes->classes, (string) $message);
					} else {
						$message = str_replace('[class]', ' ', (string) $message);
					}
				} elseif ($userTag->tagname == '[roll]') {
        $message = $user->srroll ? str_replace("[roll]", $user->srroll, (string) $message) : str_replace("[roll]", ' ', (string) $message);
    } elseif($userTag->tagname == '[country]') {
					if($user->country) {
						if(isset($this->data['allcountry'][$user->country])) {
							$message = str_replace("[country]", $this->data['allcountry'][$user->country], (string) $message);
						} else {
							$message = str_replace("[country]", ' ', (string) $message);
						}
					} else {
						$message = str_replace("[country]", ' ', (string) $message);
					}
				} elseif ($userTag->tagname == '[state]') {
        $message = $user->state ? str_replace("[state]", $user->state, (string) $message) : str_replace("[state]", ' ', (string) $message);
    } elseif($userTag->tagname == '[register_no]') {
					if($user->srregisterNO) {
						$message = str_replace("[register_no]", $user->srregisterNO, (string) $message);
					} else {
						$message = str_replace("[register_no]", ' ', (string) $message);
					}
				} elseif($userTag->tagname == '[section]') {
					if($user->srsectionID) {
						$section = $this->section_m->general_get_section($user->srsectionID);
						if(customCompute($section)) {
							$message = str_replace('[section]', $section->section, (string) $message);
						} else {
							$message = str_replace('[section]',' ', (string) $message);
						}
					} else {
						$message = str_replace("[section]", ' ', (string) $message);
					}
				} elseif($userTag->tagname == '[blood_group]') {
					if($user->bloodgroup && $user->bloodgroup != '0') {
						$message = str_replace("[blood_group]", $user->bloodgroup, (string) $message);
					} else {
						$message = str_replace("[blood_group]", ' ', (string) $message);
					}
				} elseif($userTag->tagname == '[group]') {
					if($user->srstudentgroupID && $user->srstudentgroupID != 0) {
						$group = $this->studentgroup_m->get_studentgroup($user->srstudentgroupID);
						if(customCompute($group)) {
							$message = str_replace('[group]', $group->group, (string) $message);
						} else {
							$message = str_replace('[group]',' ', (string) $message);
						}
					} else {
						$message = str_replace('[group]',' ', (string) $message);
					}
				} elseif($userTag->tagname == '[optional_subject]') {
					if($user->sroptionalsubjectID && $user->sroptionalsubjectID != 0) {
						$subject = $this->subject_m->general_get_single_subject(array('subjectID' => $user->sroptionalsubjectID));
						if(customCompute($subject)) {
							$message = str_replace('[optional_subject]', $subject->subject, (string) $message);
						} else {
							$message = str_replace('[optional_subject]',' ', (string) $message);
						}
					} else {
						$message = str_replace('[optional_subject]',' ', (string) $message);
					}
				} elseif($userTag->tagname == '[extra_curricular_activities]') {
					if($user->extracurricularactivities) {
						$message = str_replace("[extra_curricular_activities]", $user->extracurricularactivities, (string) $message);
					} else {
						$message = str_replace("[extra_curricular_activities]", ' ', (string) $message);
					}
				} elseif($userTag->tagname == '[remarks]') {
					if($user->remarks) {
						$message = str_replace("[remarks]", $user->remarks, (string) $message);
					} else {
						$message = str_replace("[remarks]", ' ', (string) $message);
					}
				} elseif($userTag->tagname == '[date]') {
					$message = str_replace("[date]", (date("d M Y")), (string) $message);
				} elseif($userTag->tagname == '[result_table]') {
					if($sendType == 'email') {
						if($user->usertypeID == 3) {
							$this->load->library('mark', ['studentID'=> $user->srstudentID, 'classesID'=> $user->srclassesID, 'schoolyearID'=> $schoolyearID, 'data'=> $this->data['siteinfos']]);
							$result = $this->mark->mail();
						} else {
							$result = '';
						}
						$message = str_replace("[result_table]", $result, (string) $message);
					} elseif($sendType == 'SMS') {
						if($user->usertypeID == 3) {
							$this->load->library('mark', ['studentID'=> $user->srstudentID, 'classesID'=> $user->srclassesID, 'schoolyearID'=> $schoolyearID, 'data'=> $this->data['siteinfos']]);
							$result = $this->mark->sms();
						} else {
							$result = '';
						}
						$message = str_replace("[result_table]", $result, (string) $message);
					}
				}
			}
		}
		return $message;
	}

	public function alltemplate() {
		if($this->input->post('usertypeID') == 'select') {
			echo '<option value="select">'.$this->lang->line('mailandsms_select_template').'</option>';
		} else {
			$usertypeID = $this->input->post('usertypeID');
			$type = $this->input->post('type');

			$templates = $this->mailandsmstemplate_m->get_order_by_mailandsmstemplate(array('usertypeID' => $usertypeID, 'type' => $type));
			echo '<option value="select">'.$this->lang->line('mailandsms_select_template').'</option>';
			if(customCompute($templates)) {
				foreach ($templates as $key => $template) {
					echo '<option value="'.$template->mailandsmstemplateID.'">'. $template->name  .'</option>';
				}
			}
		}
	}

	public function allusers() {
		if($this->input->post('usertypeID') == 'select') {
			echo '<option value="select">'.$this->lang->line('mailandsms_all_users').'</option>';
		} else {
			$usertypeID = $this->input->post('usertypeID');
			$userID = $this->input->post('userID');

			if($usertypeID == 1) {
				$systemadmins = $this->systemadmin_m->get_systemadmin();
				if(customCompute($systemadmins)) {
					echo "<option value='select'>".$this->lang->line('mailandsms_all_users')."</option>";
					foreach ($systemadmins as $key => $systemadmin) {
						echo "<option value='".$systemadmin->systemadminID."'>".$systemadmin->name.'</option>';
					}
				} else {
					echo '<option value="select">'.$this->lang->line('mailandsms_all_users').'</option>';
				}
			} elseif($usertypeID == 2) {
				$teachers = $this->teacher_m->general_get_teacher();
				if(customCompute($teachers)) {
					echo "<option value='select'>".$this->lang->line('mailandsms_all_users')."</option>";
					foreach ($teachers as $key => $teacher) {
						echo "<option value='".$teacher->teacherID."'>".$teacher->name.'</option>';
					}
				} else {
					echo '<option value="select">'.$this->lang->line('mailandsms_all_users').'</option>';
				}
			} elseif($usertypeID == 3) {
				$classes = $this->classes_m->general_get_classes();
				if(customCompute($classes)) {
					echo "<option value='select'>".$this->lang->line('mailandsms_all_class')."</option>";
					foreach ($classes as $key => $classm) {
						echo "<option value='".$classm->classesID."'>".$classm->classes.'</option>';
					}
				} else {
					echo '<option value="select">'.$this->lang->line('mailandsms_all_class').'</option>';
				}
			} elseif($usertypeID == 4) {
				$parents = $this->parents_m->get_parents();
				if(customCompute($parents)) {
					echo "<option value='select'>".$this->lang->line('mailandsms_all_users')."</option>";
					foreach ($parents as $key => $parent) {
						echo "<option value='".$parent->parentsID."'>".$parent->name.'</option>';
					}
				} else {
					echo '<option value="select">'.$this->lang->line('mailandsms_all_users').'</option>';
				}
			} else {
				$users = $this->user_m->get_order_by_user(array('usertypeID' => $usertypeID));
				if(customCompute($users)) {
					echo "<option value='select'>".$this->lang->line('mailandsms_all_users')."</option>";
					foreach ($users as $key => $user) {
						echo "<option value='".$user->userID."'>".$user->name.'</option>';
					}
				} else {
					echo '<option value="select">'.$this->lang->line('mailandsms_all_users').'</option>';
				}
			}
		}
	}

	public function allstudent() {
		$schoolyearID = $this->input->post('schoolyear');
		$classesID = $this->input->post('classes');
		$sectionID = $this->input->post('section');
		if((int)$schoolyearID && (int)$classesID) {
		    if ((int)$sectionID !== 0){
                $students = $this->studentrelation_m->get_order_by_student(array('srschoolyearID' => $schoolyearID,'srsectionID' => $sectionID, 'srclassesID' => $classesID));
            }else {
                $students = $this->studentrelation_m->get_order_by_student(array('srschoolyearID' => $schoolyearID, 'srclassesID' => $classesID));
            }
			if(customCompute($students)) {
				echo '<option value="select">'.$this->lang->line('mailandsms_all_users').'</option>';
				foreach ($students as $key => $student) {
					echo '<option value="'.$student->srstudentID.'">'.$student->srname.'</option>';
				}
			} else {
				echo '<option value="select">'.$this->lang->line('mailandsms_all_users').'</option>';
			}
		} else {
			echo '<option value="select">'.$this->lang->line('mailandsms_all_users').'</option>';
		}
	}

    public function allsection() {
        $classesID = $this->input->post('classes');
        if((int)$classesID !== 0) {
            $allsection = $this->section_m->general_get_order_by_section(array('classesID' => $classesID));
            echo "<option value='select'>", $this->lang->line("mailandsms_all_section"),"</option>";
            foreach ($allsection as $value) {
                echo "<option value=\"$value->sectionID\">",$value->section,"</option>";
            }
        }
    }

	public function check_email_usertypeID() {
		if($this->input->post('email_usertypeID') == 'select') {
			$this->form_validation->set_message("check_email_usertypeID", "The %s field is required");
	     	return FALSE;
		} else {
			return TRUE;
		}
	}

	public function alltemplatedesign() {
		if((int)$this->input->post('templateID') !== 0) {
			$templateID = $this->input->post('templateID');
			$templates = $this->mailandsmstemplate_m->get_mailandsmstemplate($templateID);
			if(customCompute($templates)) {
				echo $templates->template;
			}
		} else {
			echo '';
		}
	}

	public function check_sms_usertypeID() {
		if($this->input->post('sms_usertypeID') == 'select') {
			$this->form_validation->set_message("check_sms_usertypeID", "The %s field is required");
	     	return FALSE;
		} else {
			return TRUE;
		}
	}

	public function check_getway() {
		if($this->input->post('sms_getway') == 'select') {
			$this->form_validation->set_message("check_getway", "The %s field is required");
	     	return FALSE;
		} else {

			$getway = $this->input->post('sms_getway');
			$arrgetway = array('clickatell', 'twilio', 'bulk', 'msg91');
			if(in_array($getway, $arrgetway)) {
				if ($getway == "clickatell") {
        return $this->clickatell->ping() == TRUE;
    } elseif($getway == 'twilio') {
					$get = $this->twilio->get_twilio();
					$ApiVersion = $get['version'];
					$AccountSid = $get['accountSID'];
					$check = $this->twilio->request("/$ApiVersion/Accounts/$AccountSid/Calls");

					if($check->IsError) {
						$this->form_validation->set_message("check_getway", $check->ErrorMessage);
	     				return FALSE;
					}
					return TRUE;
				} elseif($getway == 'bulk') {
					if($this->bulk->ping() == TRUE) {
						return TRUE;
					} else {
						$this->form_validation->set_message("check_getway", 'Invalid Username or Password');
	     				return FALSE;
					}
				} elseif($getway == 'msg91') {
                    return true;
				}
			} else {
				$this->form_validation->set_message("check_getway", "The %s field is required");
	     		return FALSE;
			}
		}
	}

	private function allgetway_send_message($getway, $to, $message) {
		$result = [];
		if($getway == "clickatell") {
			if($to) {
				$this->clickatell->send_message($to, $message);
				$result['check'] = TRUE;
				return $result;
			}
		} elseif($getway == 'twilio') {
			$get = $this->twilio->get_twilio();
			$from = $get['number'];
			if($to) {
				$response = $this->twilio->sms($from, $to, $message);
				if($response->IsError) {
					$result['check'] = FALSE;
					$result['message'] = $response->ErrorMessage;
					return $result;
				} else {
					$result['check'] = TRUE;
					return $result;
				}

			}
		} elseif($getway == 'bulk') {
			if($to) {
				if($this->bulk->send($to, $message) == TRUE)  {
					$result['check'] = TRUE;
					return $result;
				} else {
					$result['check'] = FALSE;
					$result['message'] = "Check your bulk account";
					return $result;
				}
			}
		} elseif($getway == 'msg91') {
			if($to) {
				if($this->msg91->send($to, $message) == TRUE)  {
					$result['check'] = TRUE;
					return $result;
				} else {
					$result['check'] = FALSE;
					$result['message'] = "Check your msg91 account";
					return $result;
				}
			}
		}
	}

	public function view() {
		$id = htmlentities((string) escapeString($this->uri->segment(3)));
		if((int)$id !== 0) {
			$this->data['mailandsms'] = $this->mailandsms_m->get_mailandsms($id);
			if($this->data['mailandsms']) {
				$this->data["subview"] = "mailandsms/view";
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

	public function unique_data($data) {
		if ($data != "" && $data == "select") {
      $this->form_validation->set_message('unique_data', 'The %s field is required.');
      return FALSE;
  } 
		return TRUE;
	}
}
