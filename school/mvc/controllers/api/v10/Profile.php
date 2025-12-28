<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') || exit('No direct script access allowed');

class Profile extends Api_Controller {

	public $load;
 public $session;
 public $systemadmin_m;
 public $teacher_m;
 public $studentrelation_m;
 public $parents_m;
 public $user_m;
 public $retdata;
 public $manage_salary_m;
 public $db;
 public $tattendance_m;
 public $subjectattendance_m;
 public $sattendance_m;
 public $uattendance_m;
 public $data;
 public $classes_m;
 public $exam_m;
 public $grade_m;
 public $mark_m;
 public $markpercentage_m;
 public $subject_m;
 public $marksetting_m;
 public $payment_m;
 public $weaverandfine_m;
 public $leaveapplication_m;
 function __construct() {
        parent::__construct();
		$this->load->model('usertype_m');
		$this->load->model('section_m');
		$this->load->model("student_m");
		$this->load->model("parents_m");
		$this->load->model("teacher_m");
		$this->load->model("user_m");
		$this->load->model("systemadmin_m");
		$this->load->model('studentrelation_m');
		$this->load->model('studentgroup_m');
		$this->load->model('manage_salary_m');
		$this->load->model('salary_template_m');
		$this->load->model('salaryoption_m');
		$this->load->model('uattendance_m');
		$this->load->model('make_payment_m');
		$this->load->model('tattendance_m');
		$this->load->model('routine_m');
		$this->load->model('subject_m');
		$this->load->model('sattendance_m');
		$this->load->model('payment_m');
		$this->load->model('exam_m');
		$this->load->model('grade_m');
		$this->load->model('mark_m');
		$this->load->model('markpercentage_m');
		$this->load->model('invoice_m');
		$this->load->model('weaverandfine_m');
		$this->load->model('feetypes_m');
		$this->load->model('document_m');
		$this->load->model('hourly_template_m');
		$this->load->model('subjectattendance_m');
		$this->load->model('leaveapplication_m');
		$this->load->model('marksetting_m');

	}

    public function index_get() {

		$usertypeID = $this->session->userdata("usertypeID");
		$loginuserID = $this->session->userdata('loginuserID');
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		if($usertypeID == 1) {
			$user = $this->systemadmin_m->get_single_systemadmin(array('systemadminID' => $loginuserID));
		} elseif($usertypeID == 2) {
			$user = $this->teacher_m->get_single_teacher(array('teacherID' => $loginuserID));
		} elseif($usertypeID == 3) {
			$user = $this->studentrelation_m->get_single_student(array('srstudentID' => $loginuserID, 'srschoolyearID' => $schoolyearID), TRUE);
		} elseif($usertypeID == 4) {
			$user = $this->parents_m->get_single_parents(array("parentsID" => $loginuserID));
		} else {
			$user = $this->user_m->get_single_user(array("userID" => $loginuserID, 'usertypeID' => $usertypeID));
		}
		
		$this->retdata['leaveapplications'] = $this->leave_applications_date_list_by_user_and_schoolyear($loginuserID,$schoolyearID,$usertypeID);
		
		$this->getView($user);
	}
	
	private function getView($getUser) {
		if(customCompute($getUser)) {
			$this->pluckInfo();
			$this->basicInfo($getUser);
			$this->salaryInfo($getUser);
			$this->attendanceInfo($getUser);
			$this->paymentInfo($getUser);

			if($getUser->usertypeID == 3) {
				$this->parentInfo($getUser);
				$this->markInfo($getUser);
				$this->invoiceInfo($getUser);
			}

			if($getUser->usertypeID == 4) {
				$this->childrenInfo($getUser);
			}

			$this->routineInfo($getUser);
			$this->documentInfo($getUser);
			
			if(customCompute($getUser)) {
                $this->response([
                    'status'    => true,
                    'message'   => 'Success',
                    'data'      => $this->retdata
                ], REST_Controller::HTTP_OK);
			} else {
                $this->response([
                    'status' => false,
                    'message' => 'Error 404',
                    'data' => []
                ], REST_Controller::HTTP_NOT_FOUND);
			}
		} else {
            $this->response([
                'status' => false,
                'message' => 'Error 404',
                'data' => []
            ], REST_Controller::HTTP_NOT_FOUND);
		}
	}

	private function pluckInfo() {
		$this->retdata['usertypes'] = pluck($this->usertype_m->get_usertype(),'usertype','usertypeID');
		$this->retdata['classess'] = pluck($this->classes_m->general_get_classes(), 'classes', 'classesID');
		$this->retdata['sections'] = pluck($this->section_m->get_section(), 'section', 'sectionID');
		$this->retdata['subjects'] = pluck($this->subject_m->general_get_subject(), 'subject', 'subjectID');
		$this->retdata['feetypes'] = pluck($this->feetypes_m->get_feetypes(), 'feetypes', 'feetypesID');
		$this->retdata['teachers'] = pluck($this->teacher_m->get_teacher(), 'name', 'teacherID');
	}

	private function basicInfo($getUser) {
		if(customCompute($getUser)) {
			$this->retdata['profile'] = $getUser;
			if($getUser->usertypeID == 3) {
				$this->retdata['usertype'] = $this->usertype_m->get_single_usertype(array('usertypeID' => $getUser->usertypeID));
				$this->retdata['class'] = $this->classes_m->get_single_classes(array('classesID' => $getUser->srclassesID));
				$this->retdata['sectionn'] = $this->section_m->get_single_section(array('sectionID' => $getUser->srsectionID));
				$this->retdata['group'] = $this->studentgroup_m->get_single_studentgroup(array('studentgroupID' => $getUser->srstudentgroupID));
				$this->retdata['optionalsubject'] = $this->subject_m->get_single_subject(array('subjectID' => $getUser->sroptionalsubjectID));
			}

		} else {
			$this->retdata['profile'] = [];
		}
	}

	private function salaryInfo($getUser) {
		if(customCompute($getUser)) {
			if($getUser->usertypeID == 1) {
            	$manageSalary = $this->manage_salary_m->get_single_manage_salary(array('usertypeID' => $getUser->usertypeID, 'userID' => $getUser->systemadminID));
			} elseif($getUser->usertypeID == 2) {
            	$manageSalary = $this->manage_salary_m->get_single_manage_salary(array('usertypeID' => $getUser->usertypeID, 'userID' => $getUser->teacherID));
			} elseif($getUser->usertypeID == 3) {
				$manageSalary = [];
			} elseif($getUser->usertypeID == 4) {
				$manageSalary = [];
			} else {
            	$manageSalary = $this->manage_salary_m->get_single_manage_salary(array('usertypeID' => $getUser->usertypeID, 'userID' => $getUser->userID));
			}
            if(customCompute($manageSalary)) {
                $this->retdata['manage_salary'] = $manageSalary;
                if($manageSalary->salary == 1) {
                    $this->retdata['salary_template'] = $this->salary_template_m->get_single_salary_template(array('salary_templateID' => $manageSalary->template));
                    if($this->retdata['salary_template']) {
                        $this->db->order_by("salary_optionID", "asc");
                        $this->retdata['salaryoptions'] = $this->salaryoption_m->get_order_by_salaryoption(array('salary_templateID' => $manageSalary->template));

                        $grosssalary = 0;
                        $totaldeduction = 0;
                        $netsalary = $this->retdata['salary_template']->basic_salary;
                        $orginalNetsalary = $this->retdata['salary_template']->basic_salary;
                        $grosssalarylist = array();
                        $totaldeductionlist = array();

                        if(customCompute($this->retdata['salaryoptions'])) {
                            foreach ($this->retdata['salaryoptions'] as $salaryOptionKey => $salaryOption) {
                                if($salaryOption->option_type == 1) {
                                    $netsalary += $salaryOption->label_amount;
                                    $grosssalary += $salaryOption->label_amount;
                                    $grosssalarylist[$salaryOption->label_name] = $salaryOption->label_amount;
                                } elseif($salaryOption->option_type == 2) {
                                    $netsalary -= $salaryOption->label_amount;
                                    $totaldeduction += $salaryOption->label_amount;
                                    $totaldeductionlist[$salaryOption->label_name] = $salaryOption->label_amount;
                                }
                            }
                        }

                        $this->retdata['grosssalary'] = ($orginalNetsalary+$grosssalary);
                        $this->retdata['totaldeduction'] = $totaldeduction;
                        $this->retdata['netsalary'] = $netsalary;
                    } else {
                        $this->retdata['salary_template'] = [];
                        $this->retdata['salaryoptions'] = [];
                        $this->retdata['grosssalary'] = 0;
                        $this->retdata['totaldeduction'] = 0;
                        $this->retdata['netsalary'] = 0;
                    }
                } elseif($manageSalary->salary == 2) {
                    $this->retdata['hourly_salary'] = $this->hourly_template_m->get_single_hourly_template(array('hourly_templateID'=> $manageSalary->template));
                    if(customCompute($this->retdata['hourly_salary'])) {
                        $this->retdata['grosssalary'] = 0;
                        $this->retdata['totaldeduction'] = 0;
                        $this->retdata['netsalary'] = $this->retdata['hourly_salary']->hourly_rate;
                    } else {
                    	$this->retdata['hourly_salary'] = [];
                        $this->retdata['grosssalary'] = 0;
                        $this->retdata['totaldeduction'] = 0;
                        $this->retdata['netsalary'] = 0;
                    }
                }
            } else {
            	$this->retdata['manage_salary'] = [];
            	$this->retdata['salary_template'] = [];
            	$this->retdata['salaryoptions'] = [];
            	$this->retdata['hourly_salary'] = [];
            	$this->retdata['grosssalary'] = 0;
                $this->retdata['totaldeduction'] = 0;
                $this->retdata['netsalary'] = 0;
            }
        } else {
        	$this->retdata['manage_salary'] = [];
        	$this->retdata['salary_template'] = [];
        	$this->retdata['salaryoptions'] = [];
        	$this->retdata['hourly_salary'] = [];
        	$this->retdata['grosssalary'] = 0;
            $this->retdata['totaldeduction'] = 0;
            $this->retdata['netsalary'] = 0;
        }
	}

	public function attendanceInfo($getUser) {
		if(customCompute($getUser)) {
			$this->retdata['holidays'] =  $this->getHolidaysSession();
			$this->retdata['getWeekendDays'] =  $this->getWeekendDaysSession();
			$schoolyearID = $this->session->userdata('defaultschoolyearID');

			if($getUser->usertypeID == 1) {
				$uattendances = [];
			} elseif($getUser->usertypeID == 2) {
				$uattendances = $this->tattendance_m->get_order_by_tattendance(array("teacherID" => $getUser->teacherID, 'schoolyearID' => $schoolyearID));
			} elseif($getUser->usertypeID == 3) {
				$this->retdata['setting'] = $this->setting_m->get_setting();

				if($this->retdata['setting']->attendance == "subject") {
					$this->retdata["attendancesubjects"] = $this->subject_m->get_order_by_subject(array("classesID" => $getUser->srclassesID));
					$uattendances = $this->subjectattendance_m->get_order_by_sub_attendance(array("studentID" => $getUser->srstudentID, "classesID" => $getUser->srclassesID, 'schoolyearID'=> $schoolyearID));
					$this->retdata['attendances_subjectwisess'] = pluck_multi_array_key($uattendances, 'obj', 'subjectID', 'monthyear');
				} else {
					$uattendances = $this->sattendance_m->get_order_by_attendance(array("studentID" => $getUser->srstudentID, "classesID" => $getUser->srclassesID,'schoolyearID'=> $schoolyearID));
				}
			} elseif($getUser->usertypeID == 4) {
				$uattendances = [];
			} else {
				$uattendances = $this->uattendance_m->get_order_by_uattendance(array("userID" => $getUser->userID, 'schoolyearID' => $schoolyearID));
			}
			$this->retdata['attendancesArray'] = pluck($uattendances,'obj','monthyear');
		} else {
			$this->retdata['holidays'] = [];
			$this->retdata['getWeekendDays'] = [];
			$this->retdata['attendancesArray'] = [];
		}
	}

	private function paymentInfo($getUser) {
		if(customCompute($getUser)) {
			$schoolyearID = $this->session->userdata('defaultschoolyearID');
			if($getUser->usertypeID == 1) {
				$this->retdata['make_payments'] = $this->make_payment_m->get_order_by_make_payment(array('usertypeID' => $getUser->usertypeID, 'userID' => $getUser->systemadminID,'schoolyearID'=>$schoolyearID));
			} elseif($getUser->usertypeID == 2) {
				$this->retdata['make_payments'] = $this->make_payment_m->get_order_by_make_payment(array('usertypeID' => $getUser->usertypeID, 'userID' => $getUser->teacherID, 'schoolyearID'=>$schoolyearID));
			} elseif($getUser->usertypeID == 3) {
				$this->retdata['payments'] = $this->payment_m->get_payment_with_studentrelation_by_studentID_and_schoolyearID($getUser->srstudentID, $schoolyearID);
			} elseif($getUser->usertypeID == 4) {
				$this->retdata['make_payments'] = [];
			} else {
				$this->retdata['make_payments'] = $this->make_payment_m->get_order_by_make_payment(array('usertypeID' => $getUser->usertypeID, 'userID' => $getUser->userID, 'schoolyearID'=>$schoolyearID));
			}
		} else {
			$this->retdata['make_payments'] = [];
		}
	}

	private function routineInfo($getUser) {
		$settingWeekends = [];
		if($this->data['siteinfos']->weekends != '') {
			$settingWeekends = explode(',', (string) $this->data['siteinfos']->weekends);
		}
		$this->retdata['routineweekends'] = $settingWeekends;
		$this->retdata['routines']        = [];
		if(customCompute($getUser)) {
			$schoolyearID = $this->session->userdata('defaultschoolyearID');
			if($getUser->usertypeID == 1) {
				$this->retdata['routines'] = [];
			} elseif($getUser->usertypeID == 2) {
				$this->retdata['routines'] = pluck_multi_array($this->routine_m->get_order_by_routine(array('teacherID'=>$getUser->teacherID, 'schoolyearID'=> $schoolyearID)), 'obj', 'day');
			} elseif($getUser->usertypeID == 3) {
				$this->retdata['routines'] = pluck_multi_array($this->routine_m->get_order_by_routine(array('classesID'=> $getUser->srclassesID, 'sectionID'=> $getUser->srsectionID, 'schoolyearID'=> $schoolyearID)), 'obj', 'day');
			} else {
				$this->retdata['routines'] = [];
			}
		}
	}

	private function parentInfo($getUser) {
		if(customCompute($getUser)) {
			$this->retdata['parents'] = $this->parents_m->get_single_parents(array('parentsID' => $getUser->parentID));
		} else {
			$this->retdata['parents'] = [];
		}
	}

	private function markInfo($getUser) {
		if(customCompute($getUser)) {
			$this->getMark($getUser->srstudentID, $getUser->srclassesID);
		} else {
			$this->retdata['set'] 				= [];
			$this->retdata["exams"] 			= [];
			$this->retdata["grades"] 			= [];
			$this->retdata['markpercentages']	= [];
			$this->retdata['validExam'] 		= [];
			$this->retdata['separatedMarks'] 	= [];
			$this->retdata["highestMarks"] 	= [];
			$this->retdata["section"] 			= [];
		}
	}

	private function getMark($studentID, $classesID) {
		if((int)$studentID && (int)$classesID) {
			$schoolyearID = $this->session->userdata('defaultschoolyearID');
			$student      = $this->studentrelation_m->get_single_student(array('srstudentID' => $studentID, 'srclassesID' => $classesID, 'srschoolyearID' => $schoolyearID));
			$classes      = $this->classes_m->get_single_classes(array('classesID' => $classesID));

			if(customCompute($student) && customCompute($classes)) {
				$queryArray = [
					'classesID'    => $student->srclassesID,
					'sectionID'    => $student->srsectionID,
					'studentID'    => $student->srstudentID, 
					'schoolyearID' => $schoolyearID, 
				];

				$exams             = pluck($this->exam_m->get_exam(), 'exam', 'examID');
				$grades            = $this->grade_m->get_grade();
				$marks             = $this->mark_m->student_all_mark_array($queryArray);
				$markpercentages   = $this->markpercentage_m->get_markpercentage();

				$subjects          = $this->subject_m->general_get_order_by_subject(array('classesID' => $classesID));
				$subjectArr        = [];
				$optionalsubjectArr= [];
				if(customCompute($subjects)) {
					foreach ($subjects as $subject) {
						if($subject->type == 0) {
							$optionalsubjectArr[$subject->subjectID] = $subject->subjectID;
						}
						$subjectArr[$subject->subjectID] = $subject;
					}
				}

				$retMark = [];
				if(customCompute($marks)) {
					foreach ($marks as $mark) {
						$retMark[$mark->examID][$mark->subjectID][$mark->markpercentageID] = $mark->mark;
					}
				}

				$allStudentMarks = $this->mark_m->student_all_mark_array(array('classesID' => $classesID, 'schoolyearID' => $schoolyearID));
				$highestMarks    = [];
				foreach ($allStudentMarks as $value) {
					if(!isset($highestMarks[$value->examID][$value->subjectID][$value->markpercentageID])) {
						$highestMarks[$value->examID][$value->subjectID][$value->markpercentageID] = -1;
					}
					$highestMarks[$value->examID][$value->subjectID][$value->markpercentageID] = max($value->mark, $highestMarks[$value->examID][$value->subjectID][$value->markpercentageID]);
				}
				$marksettings  = $this->marksetting_m->get_marksetting_markpercentages();

				$this->retdata['settingmarktypeID'] = $this->data['siteinfos']->marktypeID;
				$this->retdata['subjects']          = $subjectArr;
				$this->retdata['exams']             = $exams;
				$this->retdata['grades']            = $grades;
				$this->retdata['markpercentages']   = pluck($markpercentages, 'obj', 'markpercentageID');
				$this->retdata['optionalsubjectArr']= $optionalsubjectArr;
				$this->retdata['marks']             = $retMark;
				$this->retdata['highestmarks']      = $highestMarks;
				$this->retdata['marksettings']      = isset($marksettings[$classesID]) ? $marksettings[$classesID] : [];
			} else {
				$this->retdata['settingmarktypeID'] = 0;
				$this->retdata['subjects']          = [];
				$this->retdata['exams']             = [];
				$this->retdata['grades']            = [];
				$this->retdata['markpercentages']   = [];
				$this->retdata['optionalsubjectArr']= [];
				$this->retdata['marks']             = [];
				$this->retdata['highestmarks']      = [];
				$this->retdata['marksettings']      = [];
			}
		} else {
			$this->retdata['settingmarktypeID'] = 0;
			$this->retdata['subjects']          = [];
			$this->retdata['exams']             = [];
			$this->retdata['grades']            = [];
			$this->retdata['markpercentages']   = [];
			$this->retdata['optionalsubjectArr']= [];
			$this->retdata['marks']             = [];
			$this->retdata['highestmarks']      = [];
			$this->retdata['marksettings']      = [];
		}
	}

	private function invoiceInfo($getUser) {
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		if(customCompute($getUser)) {
			$this->retdata['invoices'] = $this->invoice_m->get_order_by_invoice(array('schoolyearID' => $schoolyearID, 'studentID' => $getUser->srstudentID, 'deleted_at'=>1));

			$payments = $this->payment_m->get_order_by_payment(array('schoolyearID' => $schoolyearID, 'studentID' => $getUser->srstudentID));
			$weaverandfines = $this->weaverandfine_m->get_order_by_weaverandfine(array('schoolyearID' => $schoolyearID, 'studentID' => $getUser->srstudentID));

			$this->retdata['allpaymentbyinvoice'] = $this->allPaymentByInvoice($payments);
			$this->retdata['allweaverandpaymentbyinvoice'] = $this->allWeaverAndFineByInvoice($weaverandfines);
		} else {
			$this->retdata['invoices'] = [];
			$this->retdata['allpaymentbyinvoice'] = [];
			$this->retdata['allweaverandpaymentbyinvoice'] = [];
		}
	}

	private function allPaymentByInvoice($payments) {
		$retPaymentArr = [];
		if($payments) {
			foreach ($payments as $payment) {
				if(isset($retPaymentArr[$payment->invoiceID])) {
					$retPaymentArr[$payment->invoiceID] += $payment->paymentamount;
				} else {
					$retPaymentArr[$payment->invoiceID] = $payment->paymentamount;					
				}
			}
		}
		return $retPaymentArr;
	}

	private function allWeaverAndFineByInvoice($weaverandfines) {
		$retWeaverAndFineArr = [];
		if($weaverandfines) {
			foreach ($weaverandfines as $weaverandfine) {
				if(isset($retWeaverAndFineArr[$weaverandfine->invoiceID]['weaver'])) {
					$retWeaverAndFineArr[$weaverandfine->invoiceID]['weaver'] += $weaverandfine->weaver;
				} else {
					$retWeaverAndFineArr[$weaverandfine->invoiceID]['weaver'] = $weaverandfine->weaver;					
				}

				if(isset($retWeaverAndFineArr[$weaverandfine->invoiceID]['fine'])) {
					$retWeaverAndFineArr[$weaverandfine->invoiceID]['fine'] += $weaverandfine->fine;
				} else {
					$retWeaverAndFineArr[$weaverandfine->invoiceID]['fine'] = $weaverandfine->fine;					
				}
			}
		}
		return $retWeaverAndFineArr;
	}

	private function documentInfo($getUser) {
		if(customCompute($getUser)) {
			$userID = 0;
			if($getUser->usertypeID == 1) {
				$userID = $getUser->systemadminID;
			} elseif($getUser->usertypeID == 2) {
				$userID = $getUser->teacherID;
			} elseif($getUser->usertypeID == 3) {
				$userID = $getUser->srstudentID;
			} elseif($getUser->usertypeID == 4) {
				$userID = $getUser->parentsID;
			} else {
				$userID = $getUser->userID;
			}

			$this->retdata['documentUserID'] = $userID;

			$this->retdata['documents'] = $this->document_m->get_order_by_document(array('userID' => $userID, 'usertypeID' => $getUser->usertypeID));
		} else {
			$this->retdata['documents'] = [];
		}
	}

	private function childrenInfo($getUser) {
		$this->retdata['childrens'] = [];
		if(customCompute($getUser)) {
			$schoolyearID = $this->session->userdata('defaultschoolyearID');
			$this->db->order_by('student.classesID', 'asc');
			$this->retdata['childrens'] = $this->studentrelation_m->general_get_order_by_student(array('parentID' => $getUser->parentsID, 'srschoolyearID' => $schoolyearID));
		}
	}


	private function leave_applications_date_list_by_user_and_schoolyear($userID, $schoolyearID, $usertypeID) {
		$leaveapplications = $this->leaveapplication_m->get_order_by_leaveapplication(array('create_userID'=>$userID,'create_usertypeID'=>$usertypeID,'schoolyearID'=>$schoolyearID,'status'=>1));
		
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
}
