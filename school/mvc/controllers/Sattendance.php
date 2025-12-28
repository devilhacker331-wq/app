<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Sattendance extends Admin_Controller {
public $load;
 public $data;
 public $session;
 public $lang;
 public $form_validation;
 public $uri;
 public $section_m;
 public $schoolyear_m;
 public $input;
 public $subject_m;
 public $studentrelation_m;
 public $subjectattendance_m;
 public $sattendance_m;
 public $mailandsmstemplate_m;
 public $parents_m;
 public $mailandsmstemplatetag_m;
 public $email;
 public $classes_m;
 public $studentgroup_m;
 public $markrelation_m;
 public $mark_m;
 public $markpercentage_m;
 public $setting_m;
 public $clickatell;
 public $twilio;
 public $bulk;
 public $msg91;
 public $leaveapplication_m;
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
	public function __construct() {
		parent::__construct();
		$this->load->model("student_m");
		$this->load->model("parents_m");
		$this->load->model("sattendance_m");
		$this->load->model("teacher_m");
		$this->load->model("classes_m");
		$this->load->model("user_m");
		$this->load->model("usertype_m");
		$this->load->model("section_m");
		$this->load->model("setting_m");
		$this->load->model('studentgroup_m');
		$this->load->model('subject_m');
		$this->load->model('schoolyear_m');
		$this->load->model('mailandsmstemplate_m');
		$this->load->model('mailandsmstemplatetag_m');
		$this->load->model('markpercentage_m');
		$this->load->model('mark_m');
		$this->load->model('grade_m');
		$this->load->model('exam_m');
		$this->load->model('studentrelation_m');
		$this->load->model('leaveapplication_m');

		$this->load->library("email");
		$this->load->library('clickatell');
		$this->load->library('twilio');
		$this->load->library('bulk');
		$this->load->library('msg91');

		$this->data['setting'] = $this->setting_m->get_setting();

		if($this->data['setting']->attendance == "subject") {
			$this->load->model("subjectattendance_m");
		}
		$language = $this->session->userdata('lang');
		$this->lang->load('sattendance', $language);
	}

	protected function rules() {
		return array(
			array(
				'field' => 'classesID',
				'label' => $this->lang->line("attendance_classes"),
				'rules' => 'trim|required|xss_clean|max_length[11]|callback_check_classes'
			),
			array(
				'field' => 'sectionID',
				'label' => $this->lang->line("attendance_section"),
				'rules' => 'trim|required|xss_clean|max_length[11]|callback_check_section'
			),
			array(
				'field' => 'date',
				'label' => $this->lang->line("attendance_date"),
				'rules' => 'trim|required|max_length[10]|xss_clean|callback_date_valid|callback_valid_future_date|callback_check_holiday|callback_check_weekendday|callback_check_session_year_date'
			)
		);
	}

	protected function attendance_rules() {
		return array(
			array(
				'field' => 'day',
				'label' => $this->lang->line("sattendance_day"),
				'rules' => 'trim|required|numeric|xss_clean|max_length[11]'
			),
			array(
				'field' => 'classes',
				'label' => $this->lang->line("sattendance_classes"),
				'rules' => 'trim|required|xss_clean|max_length[11]'
			),
			array(
				'field' => 'section',
				'label' => $this->lang->line("sattendance_section"),
				'rules' => 'trim|required|max_length[10]|xss_clean'
			),
			array(
				'field' => 'subject',
				'label' => $this->lang->line("sattendance_subject"),
				'rules' => 'trim|required|max_length[10]|xss_clean'
			),
			array(
				'field' => 'monthyear',
				'label' => $this->lang->line("sattendance_monthyear"),
				'rules' => 'trim|required|max_length[10]|xss_clean'
			),
			array(
				'field' => 'attendance[]',
				'label' => $this->lang->line("sattendance_attendance"),
				'rules' => 'trim|required|xss_clean'
			)
		);
	}

	protected function subject_rules() {
		return array(
			array(
				'field' => 'classesID',
				'label' => $this->lang->line("attendance_classes"),
				'rules' => 'trim|required|xss_clean|max_length[11]|callback_check_classes'
			),
			array(
				'field' => 'sectionID',
				'label' => $this->lang->line("attendance_section"),
				'rules' => 'trim|required|xss_clean|max_length[11]|callback_check_section'
			),
			array(
				'field' => 'subjectID',
				'label' => $this->lang->line("attendance_subject"),
				'rules' => 'trim|required|xss_clean|max_length[11]|callback_check_subject'
			),
			array(
				'field' => 'date',
				'label' => $this->lang->line("attendance_date"),
				'rules' => 'trim|required|max_length[10]|xss_clean|callback_date_valid|callback_valid_future_date|callback_check_holiday|callback_check_weekendday'
			)
		);
	}

	public function send_mail_rules() {
		return array(
			array(
				'field' => 'to',
				'label' => $this->lang->line("sattendance_to"),
				'rules' => 'trim|required|max_length[60]|valid_email|xss_clean'
			),
			array(
				'field' => 'subject',
				'label' => $this->lang->line("sattendance_subject"),
				'rules' => 'trim|required|xss_clean'
			),
			array(
				'field' => 'message',
				'label' => $this->lang->line("sattendance_message"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'id',
				'label' => $this->lang->line("sattendance_studentID"),
				'rules' => 'trim|required|max_length[10]|xss_clean|callback_unique_data'
			),
			array(
				'field' => 'set',
				'label' => $this->lang->line("sattendance_classesID"),
				'rules' => 'trim|required|max_length[10]|xss_clean|callback_unique_data'
			)
		);
	}

	public function unique_data($data) {
		if($data != '') {
			if($data == '0') {
				$this->form_validation->set_message('unique_data', 'The %s field is required.');
				return FALSE;
			}
			return TRUE;
		}
		return TRUE;
	}

	public function index() {
		$this->data['headerassets'] = array(
			'css' => array(
				'assets/select2/css/select2.css',
				'assets/select2/css/select2-bootstrap.css',
				'assets/custom-scrollbar/jquery.mCustomScrollbar.css'
			),
			'js' => array(
				'assets/select2/select2.js',
				'assets/custom-scrollbar/jquery.mCustomScrollbar.concat.min.js'
			)
		);

		$this->data['holidays'] =  $this->getHolidaysSession();
		$this->data['getWeekendDays'] =  $this->getWeekendDaysSession();

		$myProfile = false;
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		if($this->session->userdata('usertypeID') == 3) {
			$id = $this->data['myclass'];
			if(!permissionChecker('sattendance_view')) {
				$myProfile = true;
			}
		} else {
			$id = htmlentities((string) escapeString($this->uri->segment(3)));
		}

		if ($this->session->userdata('usertypeID') == 3 && $myProfile) {
      $url = $id;
      $id = $this->session->userdata('loginuserID');
      $this->view($id, $url);
  } elseif ((int)$id !== 0) {
      $this->data['set'] = $id;
      $this->data['classes'] = $this->classes_m->get_classes();
      $this->data['students'] = $this->studentrelation_m->get_order_by_student(array('srclassesID' => $id, 'srschoolyearID' => $schoolyearID));
      $fetchClass = pluck($this->data['classes'], 'classesID', 'classesID');
      if(isset($fetchClass[$id])) {
  					if(customCompute($this->data['students'])) {
  						$sections = $this->section_m->general_get_order_by_section(array("classesID" => $id));
  						$this->data['sections'] = $sections;
  						foreach ($sections as $key => $section) {
  							$this->data['allsection'][$section->section] = $this->studentrelation_m->get_order_by_student(array('srclassesID' => $id, "srsectionID" => $section->sectionID, 'srschoolyearID' => $schoolyearID));
  						}
  					} else {
  						$this->data['students'] = [];
  					}
  					$this->data["subview"] = "sattendance/index";
  					$this->load->view('_layout_main', $this->data);
  				} else {
  					$this->data['set'] = 0;
  					$this->data['students'] = [];
  					$this->data['classes'] = $this->student_m->get_classes();
  					$this->data["subview"] = "sattendance/index";
  					$this->load->view('_layout_main', $this->data);
  				}
  } else {
				$this->data['set'] = 0;
				$this->data['students'] = [];
				$this->data['classes'] = $this->student_m->get_classes();
				$this->data["subview"] = "sattendance/index";
				$this->load->view('_layout_main', $this->data);
			}
	}

	public function add() {
		if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID') || $this->session->userdata('usertypeID') == 1)) {
			$this->schoolyear_m->get_obj_schoolyear();
			$this->data['headerassets'] = array(
				'css' => array(
					'assets/select2/css/select2.css',
					'assets/select2/css/select2-bootstrap.css',
					'assets/datepicker/datepicker.css'
				),
				'js' => array(
					'assets/select2/select2.js',
					'assets/datepicker/datepicker.js'
				)
			);

			$this->data['sattendanceinfo'] = array();
			$this->data['set'] = 0;
			$this->data['date'] = date("d-m-Y");
			$this->data['day'] = 0;
			$this->data['monthyear'] = 0;

			$this->data['get_all_holidays'] = $this->getHolidayssession();
			$this->data['classes'] = $this->classes_m->get_classes();
			$this->data['students'] = [];
			$classesID = $this->input->post("classesID");

			if($classesID != 0 && $this->data['setting']->attendance == "subject") {
				$this->data['subjects'] = $this->subject_m->get_order_by_subject(array("classesID" => $classesID));
			} else {
				$this->data['subjects'] = [];
			}

			if($classesID != 0) {
				$this->data['sections'] = $this->section_m->get_order_by_section(array("classesID" => $classesID));
			} else {
				$this->data['sections'] = [];
			}

			$this->data['subjectID'] = 0;
			$this->data['sectionID'] = 0;

			if ($_POST !== []) {
       $rules = $this->data['setting']->attendance == "subject" ? $this->subject_rules() : $this->rules();
       $this->form_validation->set_rules($rules);
       if ($this->form_validation->run() == FALSE) {
   					$this->data["subview"] = "sattendance/add";
   					$this->load->view('_layout_main', $this->data);
   				} else {
   					$classesID = $this->input->post("classesID");
   					$sectionID = $this->input->post("sectionID");
   					$schoolyearID = $this->session->userdata('defaultschoolyearID');
   					$userID = $this->session->userdata('loginuserID');
   					$usertype = $this->session->userdata('usertype');
   
   					if($this->data['setting']->attendance == "subject") {
   						$subjectID = $this->input->post("subjectID");
   						$this->data['subjectID'] = $subjectID;
   						$subjectInfo =  $this->subject_m->get_subject($subjectID);
   						$this->data['sattendanceinfo']['subject'] = $subjectInfo->subject;
   					}
   
   					if($sectionID != 0) {
   						$this->data['sectionID'] = $sectionID;
   					}
   
   					$date = $this->input->post("date");
   					$this->data['set'] = $classesID;
   					$this->data['date'] = $date;
   					$explode_date = explode("-", (string) $date);
   					$monthyear = $explode_date[1]."-".$explode_date[2];
   
   					$students = $this->studentrelation_m->get_order_by_student(array('srschoolyearID' => $schoolyearID, "srclassesID" => $classesID, 'srsectionID' => $sectionID));
   
   					$studentArray = [];
   					$this->data['attendances'] = [];
   					if(customCompute($students)) {
   						if($this->data['setting']->attendance == "subject") {
   							$attendance_monthyear = pluck($this->subjectattendance_m->get_order_by_sub_attendance(array('schoolyearID' => $schoolyearID, "classesID" => $classesID, 'sectionID' => $sectionID, "subjectID" => $subjectID, "monthyear" => $monthyear)), 'obj', 'studentID');
   						} else {
   							$attendance_monthyear = pluck($this->sattendance_m->get_order_by_attendance(array('schoolyearID' => $schoolyearID, "classesID" => $classesID, 'sectionID' => $sectionID, "monthyear" => $monthyear)), 'obj', 'studentID');
   						}
   
   						foreach ($students as $student) {
   							if(!isset($attendance_monthyear[$student->studentID])) {
   								if($this->data['setting']->attendance == "subject") {
   									if ($subjectInfo->type === '1') {
                $studentArray[] = array(
      											"studentID" => $student->studentID,
      											'schoolyearID' => $schoolyearID,
      											"classesID" => $classesID,
      											'sectionID' => $sectionID,
      											"subjectID" => $subjectID,
      											"userID" => $userID,
      											"usertype" => $usertype,
      											"monthyear" => $monthyear
      										);
            } elseif ($student->sroptionalsubjectID == $this->input->post("subjectID")) {
                $studentArray[] = array(
     												"studentID" => $student->studentID,
     												'schoolyearID' => $schoolyearID,
     												"classesID" => $classesID,
     												'sectionID' => $sectionID,
     												"subjectID" => $subjectID,
     												"userID" => $userID,
     												"usertype" => $usertype,
     												"monthyear" => $monthyear
     											);
            }
   								} else {
   									$studentArray[] = array(
   										"studentID" => $student->studentID,
   										'schoolyearID' => $schoolyearID,
   										"classesID" => $classesID,
   										'sectionID' => $sectionID,
   										"userID" => $userID,
   										"usertype" => $usertype,
   										"monthyear" => $monthyear
   									);
   								}
   							}
   						}
   
   						if(customCompute($studentArray)) {
   							if($this->data['setting']->attendance == "subject") {
   								$this->subjectattendance_m->insert_batch_sub_attendance($studentArray);
   							} else {
   								$this->sattendance_m->insert_batch_attendance($studentArray);
   							}
   						}
   
   						if($this->data['setting']->attendance == "subject") {
   							$this->data['attendances'] = pluck($this->subjectattendance_m->get_order_by_sub_attendance(array('classesID' => $classesID, 'sectionID' => $sectionID, 'subjectID' => $subjectID, 'schoolyearID' => $schoolyearID, 'monthyear' => $monthyear)), 'obj', 'studentID');
   						} else {
   							$this->data['attendances'] = pluck($this->sattendance_m->get_order_by_attendance(array('classesID' => $classesID, 'sectionID' => $sectionID, 'schoolyearID' => $schoolyearID, 'monthyear' => $monthyear)), 'obj', 'studentID');
   						}
   					}
   
   					$this->data['students'] = $students;
   
   					$this->data['monthyear'] = $monthyear;
   					$this->data['day'] = $explode_date[0];
   					$this->data['sattendanceinfo']['class'] = $this->classes_m->get_classes($classesID)->classes;
   					$this->data['sattendanceinfo']['section'] = $this->section_m->get_section($sectionID)->section;
   					$this->data['sattendanceinfo']['day'] = date('l', strtotime((string) $date));
   					$this->data['sattendanceinfo']['date'] = date('jS F Y', strtotime((string) $date));
   
   					$this->data["subview"] = "sattendance/add";
   					$this->load->view('_layout_main', $this->data);
   				}
   } else {
				$this->data["subview"] = "sattendance/add";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function student_list() {
		$classID = $this->input->post('id');
		if((int)$classID !== 0) {
			$string = base_url("sattendance/index/$classID");
			echo $string;
		} else {
			redirect(base_url("sattendance/index"));
		}
	}

	public function save_attendace() {
		$retArray['status'] = FALSE;
		$retArray['message'] = '';

		if($_POST !== []) {
			$day = $this->input->post('day');
			$classes = $this->input->post('classes');
			$sectionID = $this->input->post('section');
			$subjectID = $this->input->post('subject');
			$monthyear = $this->input->post('monthyear');
			$attendance = $this->input->post('attendance');
			$schoolyearID = $this->session->userdata('defaultschoolyearID');

			$rules = $this->attendance_rules();
			$this->form_validation->set_rules($rules);
			if ($this->form_validation->run() == FALSE) {
				$retArray = $this->form_validation->error_array();
				$retArray['status'] = FALSE;
			    echo json_encode($retArray);
			    exit;
			} else {
				$messageType = 'none';
				$f = FALSE;
				
				if($this->data['setting']->attendance_notification == 'email') {
					$messageType = 'email';
					$f = TRUE;
				} elseif($this->data['setting']->attendance_notification == 'sms') {
					$messageType = 'sms';
					$f = TRUE;
				}
				
				$update_attendance = 0;

				$updateArray = [];
				if(is_array($attendance) && customCompute($attendance)) {
					foreach($attendance as $key => $singleAttendance) {
						$id = str_replace("attendance", "", $key);
						$updateArray[] = array(
							'attendanceID' 	=> $id,
							'a'.abs($day) 	=> $singleAttendance
						);
					}
				}

				$updateStatus = FALSE;
				if(customCompute($updateArray)) {
					if($this->data['setting']->attendance == "subject") {
						$update_attendance = $this->subjectattendance_m->update_batch_sub_attendance($updateArray, 'attendanceID');
						$updateStatus = TRUE;
					} else {
						$update_attendance = $this->sattendance_m->update_batch_attendance($updateArray, 'attendanceID');
						$updateStatus = TRUE;
					}
				}

				if($this->data['setting']->attendance == "subject") {
					$data = array('a'.abs($day) => "A",'schoolyearID' => $schoolyearID,'classesID'=>$classes,'sectionID' => $sectionID,'monthyear'=>$monthyear,'subjectID'=>$subjectID);
					$students = $this->subjectattendance_m->get_order_by_sub_attendance($data);
				} else {
					$data = array('a'.abs($day) => "A",'schoolyearID' => $schoolyearID,'classesID'=>$classes,'sectionID' => $sectionID,'monthyear'=>$monthyear);
					$students = $this->sattendance_m->get_order_by_attendance($data);
				}

				if($f && customCompute($students)) {
					if($messageType == 'email') {
						$this->sendAbsentEmail($students, $schoolyearID, $classes, $sectionID);
					} elseif($messageType == 'sms') {
						$this->sendAbsentSMS($students, $schoolyearID, $classes, $sectionID);
					}
				}

				if($updateStatus) {
					$retArray['message'] = "Success";
					$retArray['status'] = TRUE;
					echo json_encode($retArray);
				    exit;
				} else {
					$retArray['message'] = "Attendance data does not found";
					$retArray['status'] = FALSE;
					echo json_encode($retArray);
					exit;
				}
			}
		} else {
			$retArray['message'] = "The post method does not found";
			$retArray['status'] = FALSE;
			echo json_encode($retArray);
			exit;
		}
	}

	private function sendAbsentEmail($students, $schoolyearID, $classesID, $sectionID) {
		$templateID = $this->data['setting']->attendance_notification_template;
		$mailandsmstemplate = $this->mailandsmstemplate_m->get_mailandsmstemplate($templateID);
		$objStudents = pluck($this->studentrelation_m->get_order_by_student(array('srschoolyearID'=> $schoolyearID, 'srclassesID'=> $classesID,'srsectionID'=> $sectionID), TRUE),'obj','srstudentID');

		$parents = pluck($this->parents_m->get_parents(),'email','parentsID');

		foreach($students as $student) {
			$studentID = $student->studentID;
			$user = isset($objStudents[$studentID]) ? $objStudents[$studentID] : [];
			$parentsID = isset($objStudents[$studentID]) ? $objStudents[$studentID]->parentID : 0;
			$parentsEmail = isset($parents[$parentsID]) ? $parents[$parentsID] : '';
			
			if(customCompute($user) && $parentsID > 0 && $parentsEmail != '') {
				$user->email = $parentsEmail;
				$message = $mailandsmstemplate->template;
				$this->userConfigEmail($message, $user, 3, $schoolyearID);
			} 
		}
	}

	private function sendAbsentSMS($students, $schoolyearID, $classesID, $sectionID) {
		$attendance_smsgateway = $this->data['setting']->attendance_smsgateway;
		$templateID = $this->data['setting']->attendance_notification_template;
		$mailandsmstemplate = $this->mailandsmstemplate_m->get_mailandsmstemplate($templateID);
		$objStudents = pluck($this->studentrelation_m->get_order_by_student(array('srschoolyearID' => $schoolyearID, 'srclassesID' => $classesID, 'srsectionID' => $sectionID), TRUE),'obj','srstudentID');

		$parents = pluck($this->parents_m->get_parents(),'phone','parentsID');

		foreach($students as $student) {
			$studentID = $student->studentID;
			$user = isset($objStudents[$studentID]) ? $objStudents[$studentID] : [];
			$parentsID = isset($objStudents[$studentID]) ? $objStudents[$studentID]->parentID : 0;
			$parentsPhonenumber = isset($parents[$parentsID]) ? $parents[$parentsID] : '';
			if(customCompute($user) && $parentsID > 0 && $parentsPhonenumber != '') {
				$user->phone = $parentsPhonenumber;
				$message = $mailandsmstemplate->template;
				$this->userConfigSMS($message, $user, 3, $attendance_smsgateway, $schoolyearID);
			}
		}
	}

	private function userConfigEmail($message, $user, $usertypeID, $schoolyearID) {
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
				$this->email->set_mailtype("html");
				$this->email->from($this->data['siteinfos']->email, $this->data['siteinfos']->sname);
				$this->email->to($email);
				$this->email->subject($subject);
				$this->email->message($message);
				$this->email->send();
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
					$classes = $this->classes_m->get_classes($user->srclassesID);
					if(customCompute($classes)) {
						$message = str_replace('[class]', $classes->classes, (string) $message);
					} else {
						$message = str_replace('[class]', ' ', (string) $message);
					}
				} elseif ($userTag->tagname == '[roll]') {
        $message = $user->srroll ? str_replace("[roll]", $user->srroll, (string) $message) : str_replace("[roll]", ' ', (string) $message);
    } elseif($userTag->tagname == '[country]') {
					if($user->country) {
						$message = str_replace("[country]", $this->data['allcountry'][$user->country], (string) $message);
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
						$section = $this->section_m->get_section($user->srsectionID);
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
						$subject = $this->subject_m->get_single_subject(array('subjectID' => $user->sroptionalsubjectID));
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
					$message = str_replace("[date]", date('d M Y'), (string) $message);
				} elseif($userTag->tagname == '[result_table]') {
					if ($sendType == 'email') {
         $result = $user->usertypeID == 3 ? $this->resultTableEmail($user->srstudentID, $user->srclassesID, $schoolyearID) : '';
         $message = str_replace("[result_table]", $result, (string) $message);
     } elseif ($sendType == 'SMS') {
         $result = $user->usertypeID == 3 ? $this->resultTableSMS($user->srstudentID, $user->srclassesID, $schoolyearID) : '';
         $message = str_replace("[result_table]", $result, (string) $message);
     }
				}
			}
		}
		return $message;
	}

	private function resultTableEmail($studentID, $classesID, $schoolyearID) {
		$string = '<br>';
		$markArray = $this->getMarkArray($studentID, $classesID, $schoolyearID);
		
		$subjectCount = customCompute($this->data['generate']['mandatorysubjects']);
		if($this->data['generate']["student"]->sroptionalsubjectID > 0) {
			$subjectCount++;
		}

		if(customCompute($markArray['separatedMarks'])) {
			if(customCompute($markArray['exams'])) { 
				foreach ($markArray['exams'] as $markArrayExamKey => $markArrayExam) { 
					if (isset($markArray['validExam'][$markArrayExam->examID]) && customCompute($markArray['separatedMarks']) && isset($markArray['separatedMarks'][$markArrayExam->examID])) {
         $string.= '<div style="border: 1px solid #ddd;margin:5px;display:inline-block">';
         $string.= '<h3 style="padding:10px;margin:0px">'.$markArrayExam->exam.'</h3>';
         if(customCompute($markArray['markpercentages'])) {
										$string.= '<table class="table-bordered">';
											$string.= '<tr style="border: 1px solid #ddd;">';
												$string.= '<td style="border: 1px solid #ddd;padding: 8px;line-height: 1.428571429;vertical-align: top;">';
													$string.= $this->lang->line('sattendance_subject');
												$string.= '</td>';

												foreach ($markArray['markpercentages'] as $markArrayMarkPercentageKey => $markArrayMarkPercentage) {
													$string.= '<td style="border: 1px solid #ddd;padding: 8px;line-height: 1.428571429;vertical-align: top;">';
														$string.= $markArrayMarkPercentage;
													$string.= '</td>';
												}

												$string.= '<td style="border: 1px solid #ddd;padding: 8px;line-height: 1.428571429;vertical-align: top;">';
													$string.= $this->lang->line('sattendance_total_mark');
												$string.= '</td>';

												$string.= '<td style="border: 1px solid #ddd;padding: 8px;line-height: 1.428571429;vertical-align: top;">';
													$string.= $this->lang->line('sattendance_point');
												$string.= '</td>';

												$string.= '<td style="border: 1px solid #ddd;padding: 8px;line-height: 1.428571429;vertical-align: top;">';
													$string.= $this->lang->line('sattendance_grade');
												$string.= '</td>';
											$string.= '</tr>';
										foreach ($markArray['separatedMarks'][$markArrayExam->examID] as $markArraySeparatedMarkKey => $markArraySeparatedMark) {
											if(customCompute($markArraySeparatedMark)) {
												$string.= '<tr>';
													$string.= '<td style="border: 1px solid #ddd;padding: 8px;line-height: 1.428571429;vertical-align: top;">'; 
														if(isset($markArraySeparatedMark['subjectID'])) {
															$string .= isset($this->data['generate']['allsubjects'][$markArraySeparatedMark['subjectID']]) ? $this->data['generate']['allsubjects'][$markArraySeparatedMark['subjectID']] : $markArraySeparatedMark['subject'];
														}
													$string.= '</td>';

													$totalMark = 0;
													foreach ($markArraySeparatedMark as $markArrayPercentageKey => $markArrayPercentage) {
														if ($markArrayPercentageKey != 'subject' && isset($markArray['markpercentages'][$markArrayPercentageKey])) {
                 $string.= '<td style="border: 1px solid #ddd;padding: 8px;line-height: 1.428571429;vertical-align: top;">';
                 $string.= $markArrayPercentage;
                 $string.= '</td>';
                 $totalMark += $markArrayPercentage;
             }
													}

													$string.= '<td style="border: 1px solid #ddd;padding: 8px;line-height: 1.428571429;vertical-align: top;">';
														$string.= $totalMark;
													$string.= '</td>';

													$string.= '<td style="border: 1px solid #ddd;padding: 8px;line-height: 1.428571429;vertical-align: top;">';
														if(customCompute($markArray['grades'])) {
															foreach ($markArray['grades'] as $markArrayGradeKey => $markArrayGrade) {
																if(floor($totalMark) >= $markArrayGrade->gradefrom  && floor($totalMark) <= $markArrayGrade->gradeupto) {
																	$string.= $markArrayGrade->point;
																}
															}
														}
													$string.= '</td>';

													$string.= '<td style="border: 1px solid #ddd;padding: 8px;line-height: 1.428571429;vertical-align: top;">';
														if(customCompute($markArray['grades'])) {
															foreach ($markArray['grades'] as $markArrayGradeKey => $markArrayGrade) {
																if(floor($totalMark) >= $markArrayGrade->gradefrom  && floor($totalMark) <= $markArrayGrade->gradeupto) {
																	$string.= $markArrayGrade->grade;
																}
															}
														}
													$string.= '</td>';
												$string.= '</tr>';
											}
										}
										$string.= '</table>';
									}
         $string.= '</div><br>';
         $string.= '<div class="box-footer" style="padding-left:0px;">';
         $totalAverageMark = ($totalMark == 0) ? 0 :  (($subjectCount > 0) ? ($totalMark/$subjectCount) : 0);
         $string.= '<p class="text-black">'. $this->lang->line('sattendance_total_marks').' : <span class="text-red text-bold">'. number_format((float)($totalMark), 2, '.', '').'</span>';
         $string.= '&nbsp;&nbsp;'.$this->lang->line('sattendance_average_marks').' : <span class="text-red text-bold">'. number_format((float)($totalAverageMark), 2, '.', '').'</span>';
         if(customCompute($markArray['grades'])) {
										foreach ($markArray['grades'] as $markArrayGradeKey => $markArrayGrade) {
											if(floor($totalAverageMark) >= $markArrayGrade->gradefrom  && floor($totalAverageMark) <= $markArrayGrade->gradeupto) {
	                                            $string.= '&nbsp;&nbsp;'.$this->lang->line('sattendance_average_grade').' : <span class="text-red text-bold">'.$markArrayGrade->grade.'</span>';
												$string.= '&nbsp;&nbsp;'.$this->lang->line('sattendance_average_point').' : <span class="text-red text-bold">'.$markArrayGrade->point.'</span>';
	                                            break;
											}
										}
									}
         $string.= '</div><br>';
     }
				}
			}
			return $string;
		} else {
			return ' '.$this->lang->line('sattendance_not_found');
		}
	}

	private function resultTableSMS($studentID, $classesID, $schoolyearID) {
		$string = ' ';
		$markArray = $this->getMarkArray($studentID, $classesID, $schoolyearID);
		
		$subjectCount = customCompute($this->data['generate']['mandatorysubjects']);
		if($this->data['generate']["student"]->sroptionalsubjectID > 0) {
			$subjectCount++;
		}

		if(customCompute($markArray['separatedMarks'])) {
			if(customCompute($markArray['exams'])) { 
				foreach ($markArray['exams'] as $markArrayExamKey => $markArrayExam) { 
					if (isset($markArray['validExam'][$markArrayExam->examID]) && customCompute($markArray['separatedMarks']) && isset($markArray['separatedMarks'][$markArrayExam->examID])) {
         $string.= $markArrayExam->exam;
         if(customCompute($markArray['markpercentages'])) {
 									$totalSubjectMark = 0;
 									foreach ($markArray['separatedMarks'][$markArrayExam->examID] as $markArraySeparatedMarkKey => $markArraySeparatedMark) {
 										if(customCompute($markArraySeparatedMark)) {
 											$totalMark = 0;
 											foreach ($markArraySeparatedMark as $markArrayPercentageKey => $markArrayPercentage) {
 												if($markArrayPercentageKey != 'subject' && isset($markArray['markpercentages'][$markArrayPercentageKey])) {
 													$totalMark +=  $markArrayPercentage;										
 												}
 											}
 											$totalSubjectMark += $totalMark;
 										}
 									}
 
 
 									if($subjectCount > 0) {
 										$div = ($totalSubjectMark /$subjectCount);
 										$div = floor($div);
 
 										if(customCompute($markArray['grades'])) {
 											foreach ($markArray['grades'] as $grade) {
                                                if($grade->gradefrom <= $div && $grade->gradeupto >= $div) {
                                                    $string .= ' ['.$grade->point.', '.$grade->grade.'], ';
                                                }
                                            }
 										}
 									}
 								}
     }
				}
			}
			return strip_tags($string);
		} else {
			return ' '.$this->lang->line('mailandsms_not_found');
		}
	}

	private function getMarkArray($studentID, $classesID, $schoolyearID) {
		$this->load->model('markpercentage_m');
		$this->load->model('markrelation_m');

		if((int)$studentID && (int)$classesID) {
			$this->data['generate']["student"] 				= $this->studentrelation_m->get_single_student(array('srstudentID' => $studentID, 'srschoolyearID' => $schoolyearID), TRUE);
			$this->data['generate']["classes"] 				= $this->classes_m->get_single_classes(array('classesID' => $classesID));
			if($this->data['generate']["student"] && $this->data['generate']["classes"]) {
				$this->data['generate']['set'] 				= $classesID;
				$this->data['generate']["exams"] 			= $this->exam_m->get_exam();
				$this->data['generate']["grades"] 			= $this->grade_m->get_grade();
				$this->data['generate']['markpercentages']	= $this->markpercentage_m->get_markpercentage();
				$this->data['generate']['mandatorysubjects'] = $this->subject_m->general_get_order_by_subject(array('classesID' => $classesID, 'type' => 1));
				$this->data['generate']['allsubjects'] = pluck($this->subject_m->general_get_order_by_subject(array('classesID' => $classesID)), 'subject', 'subjectID');

				$allMarkWithRelation = $this->markrelation_m->get_all_mark_with_relation($classesID, $schoolyearID);
				$studentMarkPercentage = array();
				foreach ($allMarkWithRelation as $key => $value) {
					$studentMarkPercentage[$value->studentID][$value->examID]['markpercentage'][] = $value->markpercentageID;
					$studentMarkPercentage[$value->studentID][$value->examID][$value->subjectID] = $value->markID;
				}

				$examDataFound = [];
				$markpercentages = pluck($this->data['generate']['markpercentages'], 'markpercentageID');
				foreach ($this->data['generate']["exams"] as $exam) {
					$studentPercentage = isset($studentMarkPercentage[$studentID][$exam->examID]['markpercentage']) ? $studentMarkPercentage[$studentID][$exam->examID]['markpercentage'] : [] ;
					if(customCompute($studentPercentage)) {

						if(!in_array($exam->examID, $examDataFound)) {
							$examDataFound[$exam->examID] = $exam->examID;
						}

						$diffMarkPercentage = array_diff($markpercentages, $studentMarkPercentage[$studentID][$exam->examID]['markpercentage']);
						foreach ($diffMarkPercentage as $item) {
							if(isset($studentMarkPercentage[$studentID][$exam->examID]) && customCompute($studentMarkPercentage[$studentID][$exam->examID])) {
								foreach ($studentMarkPercentage[$studentID][$exam->examID] as $subjectID => $markID) {
									if ($subjectID == 'markpercentage') {
             continue;
         }
									$markRelation = [
										"markID" => $markID,
										"markpercentageID" => $item
									];
									$this->markrelation_m->insert($markRelation);
								}
							}
						}
					}
				}

				$this->data['generate']['validExam'] = $examDataFound;

				$this->data['generate']['markpercentages'] = $this->get_setting_mark_percentage();
				$marks = $this->mark_m->student_all_mark_array(array('classesID' => $classesID, 'schoolyearID' => $schoolyearID, 'studentID' => $studentID));

				$this->data['generate']['marks'] = $marks;
				$separatedMarks = array();
				foreach ($marks as $key => $value) {
					$separatedMarks[$value->examID][$value->subjectID]['subject'] = $value->subject;
					$separatedMarks[$value->examID][$value->subjectID]['subjectID'] = $value->subjectID;
					$separatedMarks[$value->examID][$value->subjectID][$value->markpercentageID]= $value->mark;
				}
				$this->data['generate']['separatedMarks'] = $separatedMarks;


				$this->data['generate']["section"] = $this->section_m->get_section($this->data['generate']['student']->srsectionID);


				$this->data['markArray']['validExam'] 		= $this->data['generate']['validExam'];
				$this->data['markArray']['separatedMarks'] 	= $this->data['generate']['separatedMarks'];
				$this->data['markArray']['exams'] 			= $this->data['generate']["exams"];
				$this->data['markArray']['grades'] 			= $this->data['generate']["grades"];
				$this->data['markArray']['markpercentages'] = pluck($this->data['generate']['markpercentages'], 'markpercentagetype', 'markpercentageID');

				return $this->data['markArray'];
			}
		} else {
			$this->data['markArray'] = array();
			return $this->data['markArray'];
		}
	}

	private function get_setting_mark_percentage() {
		$markpercentagesDatabases = $this->markpercentage_m->get_markpercentage();
		$markpercentagesSettings = $this->setting_m->get_markpercentage();
		$markpercentages = [];
		$array = [];
		if(customCompute($markpercentagesSettings)) {
			foreach ($markpercentagesSettings as $key => $markpercentagesSetting) {
				$expfieldname = explode('_', (string) $markpercentagesSetting->fieldoption);
				$array[] = (int)$expfieldname[1];
			}
		}

		if(customCompute($markpercentagesDatabases)) {
			foreach ($markpercentagesDatabases as $key => $markpercentagesDatabase) {
				if(in_array($markpercentagesDatabase->markpercentageID, $array)) {
					$markpercentages[] = $markpercentagesDatabase;
				}
			}
		}
		return $markpercentages;
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

	public function view($id = null, $url = null) {
		$this->data['headerassets'] = array(
			'css' => array(
				'assets/custom-scrollbar/jquery.mCustomScrollbar.css'
			),
			'js' => array(
				'assets/custom-scrollbar/jquery.mCustomScrollbar.concat.min.js'
			)
		);
		
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		$usertypeID = $this->session->userdata("usertypeID");

		$this->data['optionalSubjects'] = pluck($this->subject_m->general_get_order_by_subject(array('type' => 0)), 'subject', 'subjectID');

		$this->data['holidays'] =  $this->getHolidaysSession();
		$this->data['getWeekendDays'] =  $this->getWeekendDaysSession();
		if((int)$id && (int)$url) {
			$this->data['leaveapplications'] = $this->leave_applications_date_list_by_user_and_schoolyear($id,$schoolyearID);
			
			if($this->data['setting']->attendance == "subject") {
				$this->data["subjects"] = $this->subject_m->general_get_order_by_subject(array("classesID" => $url));
			}

			$this->data["student"] = $this->studentrelation_m->get_single_student(array('srstudentID' => $id, 'srclassesID' => $url, 'srschoolyearID' => $schoolyearID));
			$this->data["classes"] = $this->classes_m->get_single_classes(array('classesID' => $url));
			if(customCompute($this->data["student"]) && customCompute($this->data["classes"])) {
				$this->data['set'] = $url;
				$this->data["usertype"] = $this->usertype_m->get_single_usertype(array('usertypeID' => $this->data["student"]->usertypeID));
				if($this->data['setting']->attendance == "subject") {
					$attendances = $this->subjectattendance_m->get_order_by_sub_attendance(array("studentID" => $id, "classesID" => $url,'schoolyearID'=> $schoolyearID));
					$this->data['attendances_subjectwisess'] = pluck_multi_array_key($attendances, 'obj', 'subjectID', 'monthyear');
				} else {
					$attendances = $this->sattendance_m->get_order_by_attendance(array("studentID" => $id, "classesID" => $url,'schoolyearID'=> $schoolyearID));
					$this->data['attendancesArray'] = pluck($attendances,'obj','monthyear');
				}

				$this->data["section"] = $this->section_m->general_get_single_section(array('sectionID' => $this->data['student']->srsectionID));
				$this->data["subview"] = "sattendance/view";
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

	public function print_preview($id = null, $url = null) {
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		$usertypeID = $this->session->userdata("usertypeID");
		if((int)$id && (int)$url) {
			if(permissionChecker('sattendance_view') || (($this->session->userdata('usertypeID') == 3) && permissionChecker('sattendance') && ($this->session->userdata('loginuserID') == $id))) {

				$this->data['optionalSubjects'] = pluck($this->subject_m->general_get_order_by_subject(array('type' => 0)), 'subject', 'subjectID');
				$this->data['holidays'] =  $this->getHolidaysSession();
				$this->data['getWeekendDays'] =  $this->getWeekendDaysSession();

				if($this->data['setting']->attendance == "subject") {
					$this->data["subjects"] = $this->subject_m->general_get_order_by_subject(array("classesID" => $url));
				}


				if ((int)$id && (int)$url) {
					$this->data['leaveapplications'] = $this->leave_applications_date_list_by_user_and_schoolyear($id,$schoolyearID);
					
					$this->data["student"] = $this->studentrelation_m->get_single_student(array('srstudentID' => $id, 'srclassesID' => $url, 'srschoolyearID' => $schoolyearID));
					$this->data["classes"] = $this->classes_m->get_single_classes(array('classesID' => $url));
					if(customCompute($this->data["student"]) && customCompute($this->data["classes"])) {
						$this->data["usertype"] = $this->usertype_m->get_single_usertype(array('usertypeID' => $this->data["student"]->usertypeID));
						if($this->data['setting']->attendance == "subject") {
							$attendances = $this->subjectattendance_m->get_order_by_sub_attendance(array("studentID" => $id, "classesID" => $url,'schoolyearID'=> $schoolyearID));
							$this->data['attendances_subjectwisess'] = pluck_multi_array_key($attendances, 'obj', 'subjectID', 'monthyear');
						} else {
							$attendances = $this->sattendance_m->get_order_by_attendance(array("studentID" => $id, "classesID" => $url,'schoolyearID'=> $schoolyearID));
							$this->data['attendancesArray'] = pluck($attendances,'obj','monthyear');
						}
						$this->data["section"] = $this->section_m->general_get_single_section(array('sectionID' => $this->data['student']->srsectionID));
						$this->data["class"] = $this->classes_m->get_single_classes(array('classesID' => $this->data['student']->srclassesID));
						$this->reportPDF('sattendancemodule.css', $this->data, 'sattendance/print_preview');
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
		} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main', $this->data);
		}
	}

	private function leave_applications_date_list_by_user_and_schoolyear($studentID, $schoolyearID) {
		$leaveapplications = $this->leaveapplication_m->get_order_by_leaveapplication(array('create_userID'=>$studentID,'create_usertypeID'=>3,'schoolyearID'=>$schoolyearID,'status'=>1));
		
		$retArray = [];
		if(customCompute($leaveapplications)) {
			$oneday    = 60*60*24;
			foreach($leaveapplications as $leaveapplication) {
			    for($i=strtotime((string) $leaveapplication->from_date); $i<= strtotime((string) $leaveapplication->to_date); $i += $oneday) {
			        $retArray[] = date('d-m-Y', $i);
			    }
			}
		}
		return $retArray;
	}

	public function check_classes() {
		if($this->input->post('classesID') == 0) {
			$this->form_validation->set_message("check_classes", "The %s field is required");
	     	return FALSE;
		}
		return TRUE;
	}

	public function check_section() {
		if($this->input->post('sectionID') == 0) {
			$this->form_validation->set_message("check_section", "The %s field is required");
	     	return FALSE;
		}
		return TRUE;
	}

	public function check_subject() {
		if($this->input->post('subjectID') == 0) {
			$this->form_validation->set_message("check_subject", "The %s field is required");
	     	return FALSE;
		}
		return TRUE;
	}

	public function date_valid($date) {
   		if(strlen((string) $date) < 10) {
			$this->form_validation->set_message("date_valid", "%s is not valid dd-mm-yyyy");
	     	return FALSE;
		} else {
	   		$arr = explode("-", (string) $date);
	        $dd = $arr[0];
	        $mm = $arr[1];
	        $yyyy = $arr[2];
	      	if(checkdate($mm, $dd, $yyyy)) {
	      		return TRUE;
	      	} else {
	      		$this->form_validation->set_message("date_valid", "%s is not valid dd-mm-yyyy");
	     		return FALSE;
	      	}
	    }
	}

	public function send_mail() {
		$retArray['ustatus'] = FALSE;
		$retArray['message'] = '';
		if(permissionChecker('sattendance_view') || (($this->session->userdata('usertypeID') == 3) && permissionChecker('sattendance') && ($this->session->userdata('loginuserID') == $this->input->post('id')))) {
			if($_POST !== []) {
				$rules = $this->send_mail_rules();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$retArray = $this->form_validation->error_array();
					$retArray['status'] = FALSE;
				    echo json_encode($retArray);
				    exit;
				} else {
					$this->data['optionalSubjects'] = pluck($this->subject_m->general_get_order_by_subject(array('type' => 0)), 'subject', 'subjectID');
					$id = $this->input->post('id');
					$url = $this->input->post('set');
					$schoolyearID = $this->session->userdata('defaultschoolyearID');
					if ((int)$id && (int)$url) {
						$this->data['leaveapplications'] = $this->leave_applications_date_list_by_user_and_schoolyear($id,$schoolyearID);
						$this->data['holidays'] =  $this->getHolidaysSession();
						$this->data['getWeekendDays'] =  $this->getWeekendDaysSession();
						$this->data["student"] = $this->studentrelation_m->get_single_student(array('srstudentID' => $id, 'srclassesID' => $url, 'srschoolyearID' => $schoolyearID));
						$this->data["classes"] = $this->classes_m->get_single_classes(array('classesID' => $url));
						if(customCompute($this->data["student"]) && customCompute($this->data["classes"])) {
							$this->data["usertype"] = $this->usertype_m->get_single_usertype(array('usertypeID' => $this->data["student"]->usertypeID));
							if($this->data['setting']->attendance == "subject") {
								$this->data["subjects"] = $this->subject_m->general_get_order_by_subject(array("classesID" => $url));
								$attendances = $this->subjectattendance_m->get_order_by_sub_attendance(array("studentID" => $id, "classesID" => $url,'schoolyearID'=> $schoolyearID));
								$this->data['attendances_subjectwisess'] = pluck_multi_array_key($attendances, 'obj', 'subjectID', 'monthyear');
							} else {
								$attendances = $this->sattendance_m->get_order_by_attendance(array("studentID" => $id, "classesID" => $url,'schoolyearID'=> $schoolyearID));
								$this->data['attendancesArray'] = pluck($attendances,'obj','monthyear');
							}

							$this->data["section"] = $this->section_m->general_get_single_section(array('sectionID' => $this->data['student']->srsectionID));
							$this->data["class"] = $this->classes_m->get_single_classes(array('classesID' => $this->data['student']->srclassesID));
							$email = $this->input->post('to');
							$subject = $this->input->post('subject');
							$message = $this->input->post('message');
							$this->reportSendToMail('sattendancemodule.css', $this->data, 'sattendance/print_preview', $email, $subject, $message);
							$retArray['message'] = "Message";
							$retArray['status'] = TRUE;
							echo json_encode($retArray);
						    exit;
						} else {
							$retArray['message'] = $this->lang->line('sattendance_data_not_found');
							echo json_encode($retArray);
							exit;
						}
					} else {
						$retArray['message'] = $this->lang->line('sattendance_data_not_found');
						echo json_encode($retArray);
						exit;
					}
				}
			} else {
				$retArray['message'] = $this->lang->line('sattendance_permissionmethod');
				echo json_encode($retArray);
				exit;
			}
		} else {
			$retArray['message'] = $this->lang->line('sattendance_permission');
			echo json_encode($retArray);
			exit;
		}
	}

	public function valid_future_date($date) {
		$presentdate = date('Y-m-d');
		$date = date("Y-m-d", strtotime((string) $date));
		if($date > $presentdate) {
			$this->form_validation->set_message('valid_future_date','The %s field does not given future date.');
			return FALSE;
		}
		return TRUE;
	}

	public function subjectall() {
		$id = $this->input->post('id');
		if((int)$id !== 0) {
			$allsubject = $this->subject_m->get_order_by_subject(array("classesID" => $id));
			echo "<option value='0'>", $this->lang->line("attendance_select_subject"),"</option>";
			foreach ($allsubject as $value) {
				echo "<option value=\"$value->subjectID\">",$value->subject,"</option>";
			}
		}
	}

	public function sectionall() {
		$classesID = $this->input->post('id');
		if((int)$classesID !== 0) {
			$sections = $this->section_m->get_order_by_section(array('classesID' => $classesID));
			echo "<option value='0'>", $this->lang->line("attendance_select_section"),"</option>";
			if(customCompute($sections)) {
				foreach ($sections as $key => $section) {
					echo "<option value=\"$section->sectionID\">",$section->section,"</option>";
				}
			}
		}
	}

	public function check_holiday($date) {
		$getHolidays = $this->getHolidays();
		$getHolidaysArray = explode('","', (string) $getHolidays);

		if(customCompute($getHolidaysArray)) {
			if(in_array($date, $getHolidaysArray)) {
				$this->form_validation->set_message('check_holiday','The %s field given holiday.');
				return FALSE;
			} else {
				return TRUE;
			}
		}
		return TRUE;
	}

	public function check_weekendday($date) {
		$getWeekendDays = $this->getWeekendDays();
		if(customCompute($getWeekendDays)) {
			if(in_array($date, $getWeekendDays)) {
				$this->form_validation->set_message('check_weekendday','The %s field given weekenday.');
				return FALSE;
			} else {
				return TRUE;
			}
		}
		return TRUE;
	}

	public function check_session_year_date() {
		$date = strtotime((string) $this->input->post('date'));
		$startingdate = strtotime((string) $this->data['schoolyearsessionobj']->startingdate);
		$endingdate   = strtotime((string) $this->data['schoolyearsessionobj']->endingdate);

		if($date < $startingdate || $date > $endingdate) {
			$this->form_validation->set_message('check_session_year_date','The %s field given not exits.');
			return FALSE;
		} 
		return TRUE;
	}
}