<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Meritstagereport extends Admin_Controller {
public $load;
 public $session;
 public $lang;
 public $data;
 public $input;
 public $form_validation;
 public $exam_m;
 public $studentrelation_m;
 public $mark_m;
 public $subject_m;
 public $marksetting_m;
 public $uri;
 public $section_m;
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
		$this->load->model("classes_m");
		$this->load->model('section_m');
		$this->load->model("studentrelation_m");
		$this->load->model("exam_m");
		$this->load->model("markpercentage_m");
		$this->load->model("subject_m");
		$this->load->model("setting_m");
		$this->load->model("mark_m");
		$this->load->model("marksetting_m");

		$language = $this->session->userdata('lang');
		$this->lang->load('meritstagereport', $language);
	}

	protected function rules() {
		return array(
			array(
				'field' => 'examID',
				'label' => $this->lang->line("meritstagereport_exam"),
				'rules' => 'trim|required|xss_clean|callback_unique_data'
			),
			array(
				'field' => 'classesID',
				'label' => $this->lang->line("meritstagereport_class"),
				'rules' => 'trim|required|xss_clean|callback_unique_data'
			),
			array(
				'field' => 'sectionID',
				'label' => $this->lang->line("meritstagereport_section"),
				'rules' => 'trim|xss_clean'
			)
		);
	} 

	protected function send_pdf_to_mail_rules() {
		return array(
			array(
				'field' => 'examID',
				'label' => $this->lang->line("meritstagereport_exam"),
				'rules' => 'trim|required|xss_clean|callback_unique_data'
			),
			array(
				'field' => 'classesID',
				'label' => $this->lang->line("meritstagereport_class"),
				'rules' => 'trim|required|xss_clean|callback_unique_data'
			),
			array(
				'field' => 'sectionID',
				'label' => $this->lang->line("meritstagereport_section"),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'to',
				'label' => $this->lang->line("meritstagereport_to"),
				'rules' => 'trim|required|xss_clean|valid_email'
			),
			array(
				'field' => 'subject',
				'label' => $this->lang->line("meritstagereport_subject"),
				'rules' => 'trim|required|xss_clean'
			),
			array(
				'field' => 'message',
				'label' => $this->lang->line("meritstagereport_message"),
				'rules' => 'trim|xss_clean'
			),
		);
	}
	
 	public function index() {
		$this->data['headerassets'] = array(
			'css' => array(
				'assets/select2/css/select2.css',
				'assets/select2/css/select2-bootstrap.css',
				'assets/custom-scrollbar/jquery.mCustomScrollbar.css',
			),
			'js' => array(
				'assets/select2/select2.js',
				'assets/custom-scrollbar/jquery.mCustomScrollbar.concat.min.js',
			)
		);

		$this->data['exams'] = $this->exam_m->get_exam();
		$this->data['classes'] = $this->classes_m->general_get_classes();
		$this->data["subview"] = "report/meritstage/MeritstageReportView";
		$this->load->view('_layout_main', $this->data);
	}

	public function getmeritstagereport () {
		$retArray['status'] = FALSE;
		$retArray['render'] = '';
		if(permissionChecker('meritstagereport')) {
			if($_POST !== []) {
				$examID       = $this->input->post('examID');
				$classesID    = $this->input->post('classesID');
				$sectionID    = $this->input->post('sectionID');
				$schoolyearID = $this->session->userdata('defaultschoolyearID');
				$rules = $this->rules();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$retArray = $this->form_validation->error_array();
					$retArray['status'] = FALSE;
				    echo json_encode($retArray);
				    exit;
				} else {
					$this->data['examID']    = $examID;
					$this->data['classesID'] = $classesID;
					$this->data['sectionID'] = $sectionID;

					$queryArray        = [];
					$studentQueryArray = [];
					$queryArray['schoolyearID']           = $schoolyearID;
					$studentQueryArray['srschoolyearID']  = $schoolyearID;

					if((int)$examID > 0) {
						$queryArray['examID'] = $examID;
					} 
					if((int)$classesID > 0) {
						$queryArray['classesID']          = $classesID;
						$studentQueryArray['srclassesID'] = $classesID;
					} 
					if((int)$sectionID > 0) {
						$queryArray['sectionID']          = $sectionID;
						$studentQueryArray['srsectionID'] = $sectionID;
					}

					$this->data['studentLists'] = pluck($this->studentrelation_m->general_get_order_by_student($studentQueryArray),'obj','srstudentID');
					$this->data['subjects']     = $this->subject_m->general_get_order_by_subject(array('classesID'=>$classesID));

					$exams                  = $this->exam_m->get_single_exam(['examID'=> $examID]);
					$this->data['examName'] = $exams->exam;
					$this->data['classes']  = pluck($this->classes_m->general_get_classes(),'classes','classesID');
					$this->data['sections'] = pluck($this->section_m->general_get_section(),'section','sectionID');

					$students               = $this->studentrelation_m->general_get_order_by_student(array('srclassesID' => $classesID, 'srschoolyearID' => $schoolyearID));
					$marks                  = $this->mark_m->student_all_mark_array($queryArray);
					$mandatorySubjects      = $this->subject_m->general_get_order_by_subject(array('classesID' => $classesID, 'type' => 1));
					
					$markpercentagesmainArr = $this->marksetting_m->get_marksetting_markpercentages();
					$markpercentagesArr     = isset($markpercentagesmainArr[$classesID][$examID]) ? $markpercentagesmainArr[$classesID][$examID] : [];
					$settingmarktypeID      = $this->data['siteinfos']->marktypeID;

					$retMark = [];
					if(customCompute($marks)) {
						foreach ($marks as $mark) {
							$retMark[$mark->studentID][$mark->subjectID][$mark->markpercentageID] = $mark->mark;
						}
					}

					$studentPositon            = [];
					$studentChecker            = [];
					$studentClassPositionArray = [];
					if(customCompute($students)) {
						foreach ($students as $student) {
							$opuniquepercentageArr = [];
							if($student->sroptionalsubjectID > 0) {
								$opuniquepercentageArr = isset($markpercentagesArr[$student->sroptionalsubjectID]) ? $markpercentagesArr[$student->sroptionalsubjectID] : [];
							}

							$studentPositon[$student->srstudentID]['totalSubjectMark'] = 0;
							if(customCompute($mandatorySubjects)) {
								foreach ($mandatorySubjects as $mandatorySubject) {
									$uniquepercentageArr = isset($markpercentagesArr[$mandatorySubject->subjectID]) ? $markpercentagesArr[$mandatorySubject->subjectID] : [];
									$markpercentages = $uniquepercentageArr[(($settingmarktypeID==4) || ($settingmarktypeID==6)) ? 'unique' : 'own'];
									if(customCompute($markpercentages)) {
										foreach ($markpercentages as $markpercentageID) {

											$f = false;
                                            if(isset($uniquepercentageArr['own']) && in_array($markpercentageID, $uniquepercentageArr['own'])) {
                                                $f = true;
                                            }

											if (isset($studentPositon[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID])) {
               if(isset($retMark[$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID]) && $f) {
   													$studentPositon[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] += $retMark[$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID];
   												} else {
   													$studentPositon[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] += 0;
   												}
           } elseif (isset($retMark[$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID]) && $f) {
               $studentPositon[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] = $retMark[$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID];
           } else {
													$studentPositon[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] = 0;
												}

											$f = false;
											if(customCompute($opuniquepercentageArr) && (isset($opuniquepercentageArr['own']) && in_array($markpercentageID, $opuniquepercentageArr['own']))) {
	                                            $f = true;
											}

											if(!isset($studentChecker['subject'][$student->srstudentID][$markpercentageID]) && $f) {
												if($student->sroptionalsubjectID != 0) {
													if (isset($studentPositon[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID])) {
                 if(isset($retMark[$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID])) {
   															$studentPositon[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] += $retMark[$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID];
   														} else {
   															$studentPositon[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] += 0;
   														}
             } elseif (isset($retMark[$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID])) {
                 $studentPositon[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] = $retMark[$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID];
             } else {
															$studentPositon[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] = 0;
														}

												}
												$studentChecker['subject'][$student->srstudentID][$markpercentageID] = TRUE;
											}
										}
									}

									$studentPositon[$student->srstudentID]['totalSubjectMark'] += $studentPositon[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID];

									if(!isset($studentChecker['totalSubjectMark'][$student->srstudentID])) {
										if($student->sroptionalsubjectID != 0) {
											$studentPositon[$student->srstudentID]['totalSubjectMark'] += $studentPositon[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID];
										}
										$studentChecker['totalSubjectMark'][$student->srstudentID] = TRUE;
									}
								}
							}	

							$studentPositon[$student->srstudentID]['classPositionMark'] = ($studentPositon[$student->srstudentID]['totalSubjectMark'] / customCompute($studentPositon[$student->srstudentID]['subjectMark']));
							$studentClassPositionArray[$student->srstudentID] = $studentPositon[$student->srstudentID]['classPositionMark'];
						}
					}

					arsort($studentClassPositionArray);
					$studentPositon['studentClassPositionArray'] = $studentClassPositionArray;
					$this->data['studentPosition']               = $studentPositon;

					$retArray['render'] = $this->load->view('report/meritstage/MeritstageReport',$this->data,true);
					$retArray['status'] = TRUE;
					echo json_encode($retArray);
					exit();
				}
			} else {
				echo json_encode($retArray);
				exit;
			}
		} else {
			$retArray['render'] =  $this->load->view('report/reporterror', $this->data, true);
			$retArray['status'] = TRUE;
			echo json_encode($retArray);
			exit;
		}
	}

	public function pdf() {
		if(permissionChecker('meritstagereport')) {
			$examID       = htmlentities((string) escapeString($this->uri->segment(3)));
			$classesID    = htmlentities((string) escapeString($this->uri->segment(4)));
			$sectionID    = htmlentities((string) escapeString($this->uri->segment(5)));
			$schoolyearID = $this->session->userdata('defaultschoolyearID');
			if((int)$examID && (int)$classesID && ((int)$sectionID || $sectionID >= 0)) {
				$this->data['examID']    = $examID;
				$this->data['classesID'] = $classesID;
				$this->data['sectionID'] = $sectionID;

				$queryArray         = [];
				$studentQueryArray  = [];
				$queryArray['schoolyearID']          = $schoolyearID;
				$studentQueryArray['srschoolyearID'] = $schoolyearID;

				if((int)$examID > 0) {
					$queryArray['examID'] = $examID;
				} 
				if((int)$classesID > 0) {
					$queryArray['classesID'] = $classesID;
					$studentQueryArray['srclassesID'] = $classesID;
				} 
				if((int)$sectionID > 0) {
					$queryArray['sectionID'] = $sectionID;
					$studentQueryArray['srsectionID'] = $sectionID;
				}

				$this->data['studentLists'] = pluck($this->studentrelation_m->general_get_order_by_student($studentQueryArray),'obj','srstudentID');
				$this->data['subjects']     = $this->subject_m->general_get_order_by_subject(array('classesID'=>$classesID));

				$exams                  = $this->exam_m->get_single_exam(['examID'=> $examID]);
				$this->data['examName'] = $exams->exam;
				$this->data['classes']  = pluck($this->classes_m->general_get_classes(),'classes','classesID');
				$this->data['sections'] = pluck($this->section_m->general_get_section(),'section','sectionID');

				$students               = $this->studentrelation_m->general_get_order_by_student(array('srclassesID' => $classesID, 'srschoolyearID' => $schoolyearID));
				$marks                  = $this->mark_m->student_all_mark_array($queryArray);
				$mandatorySubjects      = $this->subject_m->general_get_order_by_subject(array('classesID' => $classesID, 'type' => 1));
				
				$markpercentagesmainArr = $this->marksetting_m->get_marksetting_markpercentages();
				$markpercentagesArr     = isset($markpercentagesmainArr[$classesID][$examID]) ? $markpercentagesmainArr[$classesID][$examID] : [];
				$settingmarktypeID      = $this->data['siteinfos']->marktypeID;

				$retMark = [];
				if(customCompute($marks)) {
					foreach ($marks as $mark) {
						$retMark[$mark->studentID][$mark->subjectID][$mark->markpercentageID] = $mark->mark;
					}
				}

				$studentPositon            = [];
				$studentChecker            = [];
				$studentClassPositionArray = [];
				if(customCompute($students)) {
					foreach ($students as $student) {
						$opuniquepercentageArr = [];
						if($student->sroptionalsubjectID > 0) {
							$opuniquepercentageArr = isset($markpercentagesArr[$student->sroptionalsubjectID]) ? $markpercentagesArr[$student->sroptionalsubjectID] : [];
						}

						$studentPositon[$student->srstudentID]['totalSubjectMark'] = 0;
						if(customCompute($mandatorySubjects)) {
							foreach ($mandatorySubjects as $mandatorySubject) {
								$uniquepercentageArr = isset($markpercentagesArr[$mandatorySubject->subjectID]) ? $markpercentagesArr[$mandatorySubject->subjectID] : [];
								$markpercentages = $uniquepercentageArr[(($settingmarktypeID==4) || ($settingmarktypeID==6)) ? 'unique' : 'own'];
								if(customCompute($markpercentages)) {
									foreach ($markpercentages as $markpercentageID) {

										$f = false;
                                        if(isset($uniquepercentageArr['own']) && in_array($markpercentageID, $uniquepercentageArr['own'])) {
                                            $f = true;
                                        }

										if (isset($studentPositon[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID])) {
              if(isset($retMark[$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID]) && $f) {
   												$studentPositon[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] += $retMark[$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID];
   											} else {
   												$studentPositon[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] += 0;
   											}
          } elseif (isset($retMark[$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID]) && $f) {
              $studentPositon[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] = $retMark[$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID];
          } else {
												$studentPositon[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] = 0;
											}

										$f = false;
										if(customCompute($opuniquepercentageArr) && (isset($opuniquepercentageArr['own']) && in_array($markpercentageID, $opuniquepercentageArr['own']))) {
                                            $f = true;
										}

										if(!isset($studentChecker['subject'][$student->srstudentID][$markpercentageID]) && $f) {
											if($student->sroptionalsubjectID != 0) {
												if (isset($studentPositon[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID])) {
                if(isset($retMark[$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID])) {
   														$studentPositon[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] += $retMark[$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID];
   													} else {
   														$studentPositon[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] += 0;
   													}
            } elseif (isset($retMark[$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID])) {
                $studentPositon[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] = $retMark[$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID];
            } else {
														$studentPositon[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] = 0;
													}

											}
											$studentChecker['subject'][$student->srstudentID][$markpercentageID] = TRUE;
										}
									}
								}

								$studentPositon[$student->srstudentID]['totalSubjectMark'] += $studentPositon[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID];

								if(!isset($studentChecker['totalSubjectMark'][$student->srstudentID])) {
									if($student->sroptionalsubjectID != 0) {
										$studentPositon[$student->srstudentID]['totalSubjectMark'] += $studentPositon[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID];
									}
									$studentChecker['totalSubjectMark'][$student->srstudentID] = TRUE;
								}
							}
						}	

						$studentPositon[$student->srstudentID]['classPositionMark'] = ($studentPositon[$student->srstudentID]['totalSubjectMark'] / customCompute($studentPositon[$student->srstudentID]['subjectMark']));
						$studentClassPositionArray[$student->srstudentID] = $studentPositon[$student->srstudentID]['classPositionMark'];
					}
				}

				arsort($studentClassPositionArray);
				$studentPositon['studentClassPositionArray'] = $studentClassPositionArray;
				$this->data['studentPosition']               = $studentPositon;

				$this->reportPDF('meritstagereport.css', $this->data, 'report/meritstage/MeritstageReportPDF');
			} else {
				$this->data["subview"] = "error";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			$this->data["subview"] = "errorpermission";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function send_pdf_to_mail() {
		$retArray['status'] = FALSE;
		$retArray['message'] = '';

		if(permissionChecker('terminalreport')) {
			if($_POST !== []) {
				$to           = $this->input->post('to');
				$subject      = $this->input->post('subject');
				$message      = $this->input->post('message');
				$examID       = $this->input->post('examID');
				$classesID    = $this->input->post('classesID');
				$sectionID    = $this->input->post('sectionID');
				$schoolyearID = $this->session->userdata('defaultschoolyearID');

				$rules = $this->send_pdf_to_mail_rules();
				$this->form_validation->set_rules($rules);
				if ($this->form_validation->run() == FALSE) {
					$retArray = $this->form_validation->error_array();
					$retArray['status'] = FALSE;
				    echo json_encode($retArray);
				    exit;
				} else {
					$this->data['examID']    = $examID;
					$this->data['classesID'] = $classesID;
					$this->data['sectionID'] = $sectionID;

					$queryArray        = [];
					$studentQueryArray = [];
					$queryArray['schoolyearID']          = $schoolyearID;
					$studentQueryArray['srschoolyearID'] = $schoolyearID;

					if((int)$examID > 0) {
						$queryArray['examID'] = $examID;
					} 
					if((int)$classesID > 0) {
						$queryArray['classesID']          = $classesID;
						$studentQueryArray['srclassesID'] = $classesID;
					}
					if((int)$sectionID > 0) {
						$queryArray['sectionID']          = $sectionID;
						$studentQueryArray['srsectionID'] = $sectionID;
					}

					$this->data['studentLists'] = pluck($this->studentrelation_m->general_get_order_by_student($studentQueryArray),'obj','srstudentID');
					$this->data['subjects']     = $this->subject_m->general_get_order_by_subject(array('classesID'=>$classesID));

					$exams                  = $this->exam_m->get_single_exam(['examID'=> $examID]);
					$this->data['examName'] = $exams->exam;
					$this->data['classes']  = pluck($this->classes_m->general_get_classes(),'classes','classesID');
					$this->data['sections'] = pluck($this->section_m->general_get_section(),'section','sectionID');

					$students               = $this->studentrelation_m->general_get_order_by_student(array('srclassesID' => $classesID, 'srschoolyearID' => $schoolyearID));
					$marks                  = $this->mark_m->student_all_mark_array($queryArray);
					$mandatorySubjects      = $this->subject_m->general_get_order_by_subject(array('classesID' => $classesID, 'type' => 1));
					
					$markpercentagesmainArr = $this->marksetting_m->get_marksetting_markpercentages();
					$markpercentagesArr     = isset($markpercentagesmainArr[$classesID][$examID]) ? $markpercentagesmainArr[$classesID][$examID] : [];
					$settingmarktypeID      = $this->data['siteinfos']->marktypeID;

					$retMark = [];
					if(customCompute($marks)) {
						foreach ($marks as $mark) {
							$retMark[$mark->studentID][$mark->subjectID][$mark->markpercentageID] = $mark->mark;
						}
					}

					$studentPositon            = [];
					$studentChecker            = [];
					$studentClassPositionArray = [];
					if(customCompute($students)) {
						foreach ($students as $student) {
							$opuniquepercentageArr = [];
							if($student->sroptionalsubjectID > 0) {
								$opuniquepercentageArr = isset($markpercentagesArr[$student->sroptionalsubjectID]) ? $markpercentagesArr[$student->sroptionalsubjectID] : [];
							}

							$studentPositon[$student->srstudentID]['totalSubjectMark'] = 0;
							if(customCompute($mandatorySubjects)) {
								foreach ($mandatorySubjects as $mandatorySubject) {
									$uniquepercentageArr = isset($markpercentagesArr[$mandatorySubject->subjectID]) ? $markpercentagesArr[$mandatorySubject->subjectID] : [];
									$markpercentages = $uniquepercentageArr[(($settingmarktypeID==4) || ($settingmarktypeID==6)) ? 'unique' : 'own'];
									if(customCompute($markpercentages)) {
										foreach ($markpercentages as $markpercentageID) {

											$f = false;
	                                        if(isset($uniquepercentageArr['own']) && in_array($markpercentageID, $uniquepercentageArr['own'])) {
	                                            $f = true;
	                                        }

											if (isset($studentPositon[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID])) {
               if(isset($retMark[$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID]) && $f) {
   													$studentPositon[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] += $retMark[$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID];
   												} else {
   													$studentPositon[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] += 0;
   												}
           } elseif (isset($retMark[$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID]) && $f) {
               $studentPositon[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] = $retMark[$student->srstudentID][$mandatorySubject->subjectID][$markpercentageID];
           } else {
													$studentPositon[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID] = 0;
												}

											$f = false;
											if(customCompute($opuniquepercentageArr) && (isset($opuniquepercentageArr['own']) && in_array($markpercentageID, $opuniquepercentageArr['own']))) {
	                                            $f = true;
											}

											if(!isset($studentChecker['subject'][$student->srstudentID][$markpercentageID]) && $f) {
												if($student->sroptionalsubjectID != 0) {
													if (isset($studentPositon[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID])) {
                 if(isset($retMark[$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID])) {
   															$studentPositon[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] += $retMark[$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID];
   														} else {
   															$studentPositon[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] += 0;
   														}
             } elseif (isset($retMark[$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID])) {
                 $studentPositon[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] = $retMark[$student->srstudentID][$student->sroptionalsubjectID][$markpercentageID];
             } else {
															$studentPositon[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID] = 0;
														}

												}
												$studentChecker['subject'][$student->srstudentID][$markpercentageID] = TRUE;
											}
										}
									}

									$studentPositon[$student->srstudentID]['totalSubjectMark'] += $studentPositon[$student->srstudentID]['subjectMark'][$mandatorySubject->subjectID];

									if(!isset($studentChecker['totalSubjectMark'][$student->srstudentID])) {
										if($student->sroptionalsubjectID != 0) {
											$studentPositon[$student->srstudentID]['totalSubjectMark'] += $studentPositon[$student->srstudentID]['subjectMark'][$student->sroptionalsubjectID];
										}
										$studentChecker['totalSubjectMark'][$student->srstudentID] = TRUE;
									}
								}
							}	

							$studentPositon[$student->srstudentID]['classPositionMark'] = ($studentPositon[$student->srstudentID]['totalSubjectMark'] / customCompute($studentPositon[$student->srstudentID]['subjectMark']));
							$studentClassPositionArray[$student->srstudentID] = $studentPositon[$student->srstudentID]['classPositionMark'];
						}
					}
					arsort($studentClassPositionArray);
					$studentPositon['studentClassPositionArray'] = $studentClassPositionArray;
					$this->data['studentPosition']               = $studentPositon;

					$this->reportSendToMail('meritstagereport.css', $this->data, 'report/meritstage/MeritstageReportPDF',$to, $subject,$message);
					$retArray['status'] = TRUE;
					echo json_encode($retArray);
    				exit;
				}
			} else {
				$retArray['message'] = $this->lang->line('meritstagereport_permissionmethod');
				echo json_encode($retArray);
				exit;
			}
		} else {
			$retArray['message'] = $this->lang->line('meritstagereport_permission');
			echo json_encode($retArray);
			exit;
		}
	}

	public function getExam() {
		$classesID = $this->input->post('classesID');
		echo "<option value='0'>", $this->lang->line("meritstagereport_please_select"),"</option>";
		if((int)$classesID !== 0) {
			$exams    = pluck($this->marksetting_m->get_exam($this->data['siteinfos']->marktypeID, $classesID), 'obj', 'examID');
			if(customCompute($exams)) {
				foreach ($exams as $exam) {
					echo "<option value=".$exam->examID.">".$exam->exam."</option>";
				}
			}
		}
	}

	public function getSection() {
		$classesID = $this->input->post('classesID');
		if((int)$classesID !== 0) {
			$sections = $this->section_m->get_order_by_section(array('classesID' => $classesID));
			echo "<option value='0'>", $this->lang->line("meritstagereport_please_select"),"</option>";
			if(customCompute($sections)) {
				foreach ($sections as $section) {
					echo "<option value=\"$section->sectionID\">".$section->section."</option>";
				}
			}
		}
	}

	public function unique_data($data) {
		if ($data != "" && $data === "0") {
      $this->form_validation->set_message('unique_data', 'The %s field is required.');
      return FALSE;
  } 
		return TRUE;
	}
}
