<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Onlineexamquestionanswerreport extends Admin_Controller {
public $load;
 public $session;
 public $lang;
 public $data;
 public $form_validation;
 public $input;
 public $online_exam_question_m;
 public $online_exam_user_status_m;
 public $studentrelation_m;
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
		$this->load->model('subject_m');
		$this->load->model('online_exam_m');
		$this->load->model('online_exam_question_m');
		$this->load->model('online_exam_user_status_m');
		$this->load->model('online_exam_user_answer_m');
		$this->load->model('online_exam_user_answer_option_m');
        $this->load->model('classes_m');
        $this->load->model('section_m');
        $this->load->model('subject_m');
        $this->load->model('studentrelation_m');
        $this->load->model('question_bank_m');
		$this->load->model('question_option_m');
		$this->load->model('question_answer_m');
		$language = $this->session->userdata('lang');
		$this->lang->load('onlineexamquestionanswerreport', $language);
	}

	public function index() {
		$this->data['headerassets'] = array(
			'css' => array(
				'assets/select2/css/select2.css',
				'assets/select2/css/select2-bootstrap.css'
			),
			'js' => array(
				'assets/select2/select2.js'
			)
		);
		$schoolyearID = $this->session->userdata('defaultschoolyearID');

		$this->data['online_exams'] = $this->online_exam_m->get_order_by_online_exam(array('published'=>1, 'schoolyearID' => $schoolyearID));
        $this->data['classes'] 		= $this->classes_m->general_get_classes();
        $this->data["subview"] = "report/onlineexamquestionanswer/OnlineexamquestionanswerReportView";
		$this->load->view('_layout_main', $this->data);
	}

	protected function rules() {
		return array(
			array(
				'field' => 'onlineExamID',
				'label' => $this->lang->line('onlineexamquestionanswerreport_exam'),
				'rules' => 'trim|required|xss_clean|callback_unique_data'
			),	array(
				'field' => 'studentID',
				'label' => $this->lang->line('onlineexamquestionanswerreport_student'),
				'rules' => 'trim|required|xss_clean|callback_unique_data'
			),	array(
				'field' => 'attemptID',
				'label' => $this->lang->line('onlineexamquestionanswerreport_attempt'),
				'rules' => 'trim|required|xss_clean|callback_unique_data'
			),
		);
	}

	public function unique_data($data) {
		if($data === "0") {
			$this->form_validation->set_message('unique_data', 'The %s field is required.');
			return FALSE;
		}
		return TRUE;
	}

	public function send_pdf_to_mail_rules() {
		return array(
			array(
				'field' => 'to',
				'label' => $this->lang->line('onlineexamquestionanswerreport_to'),
				'rules' => 'trim|required|xss_clean|valid_email'
			),
			array(
				'field' => 'subject',
				'label' => $this->lang->line('onlineexamquestionanswerreport_subject'),
				'rules' => 'trim|required|xss_clean'
			),
			array(
				'field' => 'message',
				'label' => $this->lang->line('onlineexamquestionanswerreport_message'),
				'rules' => 'trim|xss_clean'
			),
			array(
				'field' => 'onlineExamID',
				'label' => $this->lang->line('onlineexamquestionanswerreport_examID'),
				'rules' => 'trim|numeric|required|xss_clean'
			),
		);
	}


	public function getQuestionList() {
		$retArray['status'] = FALSE;
		$retArray['render'] = '';
		if(permissionChecker('onlineexamquestionanswerreport')) {
			if($_POST !== []) {
				$rules = $this->rules();
				$this->form_validation->set_rules($rules);
				if($this->form_validation->run() == FALSE) {
					$retArray = $this->form_validation->error_array();
					$retArray['status'] = FALSE;
				    echo json_encode($retArray);
				    exit;
				} else {		
					$schoolyearID = $this->session->userdata('defaultschoolyearID');
					$onlineExamID 	= $this->input->post('onlineExamID');
                    $studentID 		= $this->input->post('studentID');
                    $attemptID 		= $this->input->post('attemptID');

					$this->data['onlineExamID'] = $onlineExamID;
					$this->data['studentID'] = $studentID;
					$this->data['attemptID'] = $attemptID;
					$this->data['exam'] = $this->online_exam_m->get_single_online_exam(array('onlineExamID'=>$onlineExamID,'schoolyearID'=> $schoolyearID));
					if(customCompute($this->data['exam'])) {
						$this->data['typeName'] = $this->lang->line('onlineexamquestionanswerreport_question');
                        $array = [];
                        if((int)$onlineExamID && $onlineExamID > 0) {
                            $array['onlineExamID'] = $onlineExamID;
                        }
                        if((int)$studentID && $studentID > 0) {
                            $array['userID'] = $studentID;
                        }
                        if((int)$attemptID && $attemptID > 0) {
                            $array['examtimeID'] = $attemptID;
                        }

						$examquestions = pluck($this->online_exam_question_m->get_order_by_online_exam_question(array('onlineExamID'=>$onlineExamID)),'questionID');
                        $this->data['examquestionsuseranswer']  = pluck($this->online_exam_user_answer_option_m->get_order_by_online_exam_user_answer_option($array),'obj','questionID');
						$this->data['examquestionsanswer'] = pluck($this->question_answer_m->get_question_answerArray($examquestions,'questionID'),'obj','questionID');
						$this->data['questions'] = pluck($this->question_bank_m->get_question_bank_questionArray($examquestions,'questionBankID'),'obj','questionBankID');
						$this->data['question_options'] = pluck_multi_array($this->question_option_m->get_question_option_by_questionArray($examquestions,'questionID'),'obj','questionID');

						$retArray['render'] = $this->load->view('report/onlineexamquestionanswer/OnlineexamquestionanswerReport', $this->data, true);
						$retArray['status'] = TRUE;
						echo json_encode($retArray);
					    exit;
					} else {
						$retArray['render'] = $this->load->view('report/reporterror', $this->data, true);
						$retArray['status'] = TRUE;
						echo json_encode($retArray);
						exit;
					}
				}
			} else {
				$retArray['render'] = $this->load->view('report/reporterror', $this->data, true);
				$retArray['status'] = TRUE;
				echo json_encode($retArray);
				exit;
			}
		} else {
			$retArray['render'] = $this->load->view('report/reporterror', $this->data, true);
			$retArray['status'] = TRUE;
			echo json_encode($retArray);
			exit;
		}
	}

    public function getStudent() {
        $onlineExamID  = $this->input->post('onlineExamID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');

        $array = [];
        if((int)$onlineExamID && $onlineExamID > 0) {
            $array['onlineExamID'] = $onlineExamID;
        }
        $studentsID = pluck($this->online_exam_user_status_m->get_order_by_online_exam_user_status( $array),'userID');
        $students = $this->studentrelation_m->get_studentrelation_studentArray($studentsID,'srstudentID');
        echo "<option value='0'>", $this->lang->line("onlineexamquestionanswerreport_please_select"),"</option>";
        foreach ($students as $student) {
            echo "<option value=".$student->srstudentID.">".$student->srname."</option>";
        }
    }

    public function getAttempt() {
        $studentID  = $this->input->post('studentID');
        $onlineExamID  = $this->input->post('onlineExamID');

        $array = [];
        if((int)$onlineExamID && $onlineExamID > 0) {
            $array['onlineExamID'] = $onlineExamID;
        }
        if((int)$studentID && $studentID > 0) {
            $array['userID'] = $studentID;
        }

        $onlineExamtimeID = $this->online_exam_user_status_m->get_order_by_online_exam_user_status($array);
        $attempt  = 'Attempt- ';
        echo "<option value='0'>", $this->lang->line("onlineexamquestionanswerreport_please_select"),"</option>";
        foreach ($onlineExamtimeID as $onlineAttempt) {
            echo "<option value=".$onlineAttempt->examtimeID.">".$attempt.$onlineAttempt->examtimeID."</option>";
        }
    }


    public function pdf() {
		if(permissionChecker('onlineexamquestionanswerreport')) {
			$onlineExamID = htmlentities((string) escapeString($this->uri->segment(3)));
            $studentID = htmlentities((string) escapeString($this->uri->segment(4)));
            $attemptID = htmlentities((string) escapeString($this->uri->segment(5)));
			if((int)$onlineExamID || (int)$studentID ||(int)$attemptID) {
				$schoolyearID = $this->session->userdata('defaultschoolyearID');
				$this->data['onlineExamID'] = $onlineExamID;
				$this->data['studentID'] = $studentID;
				$this->data['attemptID'] = $attemptID;
				$this->data['exam'] = $this->online_exam_m->get_single_online_exam(array('onlineExamID'=>$onlineExamID,'schoolyearID'=> $schoolyearID));
				if(customCompute($this->data['exam'])) {
					$this->data['typeName'] = $this->lang->line('onlineexamquestionanswerreport_question');

                    $array = [];
                    if((int)$onlineExamID && $onlineExamID > 0) {
                        $array['onlineExamID'] = $onlineExamID;
                    }
                    if((int)$studentID && $studentID > 0) {
                        $array['userID'] = $studentID;
                    }
                    if((int)$attemptID && $attemptID > 0) {
                        $array['examtimeID'] = $attemptID;
                    }

                    $examquestions = pluck($this->online_exam_question_m->get_order_by_online_exam_question(array('onlineExamID'=>$onlineExamID)),'questionID');
                    $this->data['examquestionsuseranswer']  = pluck($this->online_exam_user_answer_option_m->get_order_by_online_exam_user_answer_option($array),'obj','questionID');
                    $this->data['examquestionsanswer'] = pluck($this->question_answer_m->get_question_answerArray($examquestions,'questionID'),'obj','questionID');
                    $this->data['questions'] = pluck($this->question_bank_m->get_question_bank_questionArray($examquestions,'questionBankID'),'obj','questionBankID');
                    $this->data['question_options'] = pluck_multi_array($this->question_option_m->get_question_option_by_questionArray($examquestions,'questionID'),'obj','questionID');

					$this->reportPDF('onlineexamquestionanswerreport.css', $this->data, 'report/onlineexamquestionanswer/OnlineexamquestionanswerReportPDF');
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

	public function send_pdf_to_mail() {
		$retArray['status'] = FALSE;
		$retArray['message']= '';
		if(permissionChecker('onlineexamquestionanswerreport')) {
			if($_POST !== []) {
				$to           = $this->input->post('to');
				$subject      = $this->input->post('subject');
				$message 	  = $this->input->post('message');
				$onlineExamID = $this->input->post('onlineExamID');
				$studentID	      = $this->input->post('studentID');
				$attemptID	      = $this->input->post('attemptID');

				$rules = $this->send_pdf_to_mail_rules();
				$this->form_validation->set_rules($rules);
				if($this->form_validation->run() == FALSE) {
					$retArray = $this->form_validation->error_array();
					$retArray['status'] = FALSE;
				    echo json_encode($retArray);
				    exit;
				} else {
					$schoolyearID = $this->session->userdata('defaultschoolyearID');
					$this->data['onlineExamID'] = $onlineExamID;
					$this->data['exam'] = $this->online_exam_m->get_single_online_exam(array('onlineExamID'=>$onlineExamID,'schoolyearID'=> $schoolyearID));
					if(customCompute($this->data['exam'])) {
						$this->data['typeName'] =  $this->lang->line('onlineexamquestionanswerreport_question');

                        $array = [];
                        if((int)$onlineExamID && $onlineExamID > 0) {
                            $array['onlineExamID'] = $onlineExamID;
                        }
                        if((int)$studentID && $studentID > 0) {
                            $array['userID'] = $studentID;
                        }
                        if((int)$attemptID && $attemptID > 0) {
                            $array['examtimeID'] = $attemptID;
                        }

                        $examquestions = pluck($this->online_exam_question_m->get_order_by_online_exam_question(array('onlineExamID'=>$onlineExamID)),'questionID');
                        $this->data['examquestionsuseranswer']  = pluck($this->online_exam_user_answer_option_m->get_order_by_online_exam_user_answer_option($array),'obj','questionID');
                        $this->data['examquestionsanswer'] = pluck($this->question_answer_m->get_question_answerArray($examquestions,'questionID'),'obj','questionID');
                        $this->data['questions'] = pluck($this->question_bank_m->get_question_bank_questionArray($examquestions,'questionBankID'),'obj','questionBankID');
                        $this->data['question_options'] = pluck_multi_array($this->question_option_m->get_question_option_by_questionArray($examquestions,'questionID'),'obj','questionID');

                        $this->reportSendToMail('onlineexamquestionanswerreport.css', $this->data, 'report/onlineexamquestionanswer/OnlineexamquestionanswerReportPDF', $to, $subject, $message);
						$retArray['status'] = TRUE;
						echo json_encode($retArray);
					    exit;
					} else {
						$retArray['message'] = $this->lang->line("onlineexamquestionanswerreport_data_not_found");
						echo json_encode($retArray);
						exit;
					}
				}
			} else {
				$retArray['message'] = $this->lang->line("onlineexamquestionanswerreport_permisionmethod");
				echo json_encode($retArray);
				exit;
			}
		} else {
			$retArray['message'] = $this->lang->line("onlineexamquestionanswerreport_permision");
			echo json_encode($retArray);
			exit;
		}
	}

}
