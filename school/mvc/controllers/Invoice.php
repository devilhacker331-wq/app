<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
require_once(APPPATH . 'libraries/PaymentGateway/PaymentGateway.php');
require_once(APPPATH . 'libraries/PaymentGateway/Service/PaymentService.php');

class Invoice extends Admin_Controller
{
    public $load;
    public $session;
    public $lang;
    public $student_m;
    public $data;
    public $studentrelation_m;
    public $uri;
    public $maininvoice_m;
    public $invoice_m;
    public $form_validation;
    public $input;
    public $payment_gateway_m;
    public $globalpayment_m;
    public $payment_m;
    public $feetypes_m;
    public $weaverandfine_m;
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
    protected $_amountgivenstatus = '';
    protected $_amountgivenstatuserror = [];
    /**
     * @var \PaymentGateway
     */
    public $payment_gateway;
    public $payment_gateway_array;

    function __construct()
    {
        parent::__construct();
        $this->load->model("invoice_m");
        $this->load->model("feetypes_m");
        $this->load->model('payment_m');
        $this->load->model("classes_m");
        $this->load->model("student_m");
        $this->load->model("parents_m");
        $this->load->model("section_m");
        $this->load->model('user_m');
        $this->load->model('weaverandfine_m');
        $this->load->model("payment_settings_m");
        $this->load->model("globalpayment_m");
        $this->load->model("maininvoice_m");
        $this->load->model("studentrelation_m");
        $this->load->model('payment_gateway_m');
        $this->load->model('payment_gateway_option_m');
        $language = $this->session->userdata('lang');
        $this->lang->load('student', $language);
        $this->lang->load('invoice', $language);
        $this->payment_gateway       = new PaymentGateway();
        $this->payment_gateway_array = pluck($this->payment_gateway_m->get_order_by_payment_gateway(['status' => 1]), 'status', 'slug');

        if(!empty($this->payment_gateway_array)) {
            foreach($this->payment_gateway_array as $gateway_key => $gateway) {
                $this->lang->load($gateway_key .'_rules_lang.php', $language);
            }
        }
    }

    protected function rules( $statusID = 0 )
    {
        $rules = [
            [
                'field' => 'classesID',
                'label' => $this->lang->line("invoice_classesID"),
                'rules' => 'trim|required|xss_clean|max_length[11]|numeric|callback_unique_classID'
            ],
            [
                'field' => 'studentID',
                'label' => $this->lang->line("invoice_studentID"),
                'rules' => 'trim|required|xss_clean|max_length[11]|numeric|callback_unique_studentID'
            ],
            [
                'field' => 'feetypeitems',
                'label' => $this->lang->line("invoice_feetypeitem"),
                'rules' => 'trim|xss_clean|required|callback_unique_feetypeitems'
            ],
            [
                'field' => 'statusID',
                'label' => $this->lang->line("invoice_status"),
                'rules' => 'trim|xss_clean|required|numeric|callback_unique_status'
            ],
            [
                'field' => 'date',
                'label' => $this->lang->line("invoice_date"),
                'rules' => 'trim|required|xss_clean|max_length[10]|callback_date_valid'
            ],
        ];

        if($statusID != 0) {
            $rules[] = [
                'field' => 'payment_method',
                'label' => $this->lang->line("invoice_paymentmethod"),
                'rules' => 'trim|required|xss_clean|max_length[20]|callback_unique_payment_method'
            ];
        }

        return $rules;
    }

    protected function send_mail_rules()
    {
        return [
            [
                'field' => 'id',
                'label' => $this->lang->line('invoice_id'),
                'rules' => 'trim|required|xss_clean|numeric|callback_valid_data'
            ],
            [
                'field' => 'to',
                'label' => $this->lang->line('to'),
                'rules' => 'trim|required|xss_clean|valid_email'
            ],
            [
                'field' => 'subject',
                'label' => $this->lang->line('subject'),
                'rules' => 'trim|required|xss_clean'
            ],
            [
                'field' => 'message',
                'label' => $this->lang->line('message'),
                'rules' => 'trim|xss_clean'
            ]
        ];
    }

    public function index()
    {
        $usertypeID   = $this->session->userdata("usertypeID");
        $schoolyearID = $this->session->userdata("defaultschoolyearID");
        if($usertypeID == 3) {
            $username = $this->session->userdata("username");
            $student  = $this->student_m->get_single_student(["username" => $username]);
            if(customCompute($student)) {
                $this->data['maininvoices']         = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_studentID($student->studentID, $schoolyearID);
                $this->data['grandtotalandpayment'] = $this->grandtotalandpaid($this->data['maininvoices'], $schoolyearID);

                $this->data["subview"] = "invoice/index";
                $this->load->view('_layout_main', $this->data);
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } elseif($usertypeID == 4) {
            $this->data['headerassets'] = [
                'css' => [
                    'assets/select2/css/select2.css',
                    'assets/select2/css/select2-bootstrap.css'
                ],
                'js'  => [
                    'assets/select2/select2.js'
                ]
            ];

            $parentID = $this->session->userdata("loginuserID");
            $students = $this->studentrelation_m->get_order_by_student([
                'parentID'       => $parentID,
                'srschoolyearID' => $schoolyearID
            ]);
            if(customCompute($students)) {
                $studentArray                       = pluck($students, 'srstudentID');
                $this->data['maininvoices']         = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_multi_studentID($studentArray, $schoolyearID);
                $this->data['grandtotalandpayment'] = $this->grandtotalandpaid($this->data['maininvoices'], $schoolyearID);
                $this->data["subview"]              = "invoice/index";
                $this->load->view('_layout_main', $this->data);
            } else {
                $this->data['maininvoices']         = [];
                $this->data['grandtotalandpayment'] = [];
                $this->data["subview"]              = "invoice/index";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data['maininvoices']         = $this->maininvoice_m->get_maininvoice_with_studentrelation($schoolyearID);
            $this->data['grandtotalandpayment'] = $this->grandtotalandpaid($this->data['maininvoices'], $schoolyearID);
            $this->data["subview"]              = "invoice/index";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function add()
    {
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
            $this->data['headerassets'] = [
                'css' => [
                    'assets/datepicker/datepicker.css',
                    'assets/select2/css/select2.css',
                    'assets/select2/css/select2-bootstrap.css'
                ],
                'js'  => [
                    'assets/datepicker/datepicker.js',
                    'assets/select2/select2.js'
                ]
            ];

            $this->data['classes']  = $this->classes_m->general_get_classes();
            $this->data['feetypes'] = $this->feetypes_m->get_feetypes();
            $this->data['students'] = [];

            $this->data["subview"] = "invoice/add";
            $this->load->view('_layout_main', $this->data);
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function edit()
    {
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
            $this->data['headerassets'] = [
                'css' => [
                    'assets/datepicker/datepicker.css',
                    'assets/select2/css/select2.css',
                    'assets/select2/css/select2-bootstrap.css'
                ],
                'js'  => [
                    'assets/datepicker/datepicker.js',
                    'assets/select2/select2.js'
                ]
            ];

            $maininvoiceID = htmlentities((string) escapeString($this->uri->segment(3)));
            if((int)$maininvoiceID !== 0) {
                $schoolyearID                = $this->session->userdata('defaultschoolyearID');
                $this->data['maininvoiceID'] = $maininvoiceID;
                $this->data['maininvoice']   = $this->maininvoice_m->get_single_maininvoice(['maininvoiceID' => $maininvoiceID]);
                if(customCompute($this->data['maininvoice'])) {
                    if($this->data['maininvoice']->maininvoicestatus == 0) {
                        $this->data['classes']  = $this->classes_m->general_get_classes();
                        $this->data['feetypes'] = pluck($this->feetypes_m->get_feetypes(), 'obj', 'feetypesID');
                        $this->data['students'] = $this->studentrelation_m->get_order_by_studentrelation([
                            'srclassesID'    => $this->data['maininvoice']->maininvoiceclassesID,
                            'srschoolyearID' => $schoolyearID
                        ]);

                        $this->data['invoices'] = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $maininvoiceID]);

                        $this->data["subview"] = "invoice/edit";
                        $this->load->view('_layout_main', $this->data);
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

    public function delete()
    {
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
            $maininvoiceID = htmlentities((string) escapeString($this->uri->segment(3)));
            if((int)$maininvoiceID !== 0) {
                $maininvoice = $this->maininvoice_m->get_single_maininvoice([
                    'maininvoiceID'         => $maininvoiceID,
                    'maininvoicedeleted_at' => 1
                ]);
                if(customCompute($maininvoice)) {
                    if($maininvoice->maininvoicestatus == 0) {
                        $this->maininvoice_m->update_maininvoice(['maininvoicedeleted_at' => 0], $maininvoiceID);
                        $this->invoice_m->update_invoice_by_maininvoiceID(['deleted_at' => 0], $maininvoiceID);
                        $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                        redirect(base_url('invoice/index'));
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

    public function view()
    {
        $usertypeID             = $this->session->userdata("usertypeID");
        $schoolyearID           = $this->session->userdata('defaultschoolyearID');
        $this->data['feetypes'] = pluck($this->feetypes_m->get_feetypes(), 'feetypes', 'feetypesID');

        if($usertypeID == 3) {
            $id = htmlentities((string) escapeString($this->uri->segment(3)));
            if((int)$id !== 0) {
                $studentID  = $this->session->userdata("loginuserID");
                $getstudent = $this->studentrelation_m->get_single_student([
                    "srstudentID"    => $studentID,
                    'srschoolyearID' => $schoolyearID
                ]);
                if(customCompute($getstudent)) {
                    $this->data['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID($id, $schoolyearID);
                    if(customCompute($this->data['maininvoice']) && ($this->data['maininvoice']->maininvoicestudentID == $getstudent->studentID)) {
                        $this->data['invoices'] = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $id]);

                        $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maininvoice'], $schoolyearID, $this->data["maininvoice"]->maininvoicestudentID);

                        $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maininvoice"]->maininvoicestudentID]);

                        $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maininvoice']->maininvoiceusertypeID, $this->data['maininvoice']->maininvoiceuserID);

                        $this->data["subview"] = "invoice/view";
                        $this->load->view('_layout_main', $this->data);
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
        } elseif($usertypeID == 4) {
            $id = htmlentities((string) escapeString($this->uri->segment(3)));
            if((int)$id !== 0) {
                $parentID     = $this->session->userdata("loginuserID");
                $getStudents  = $this->studentrelation_m->get_order_by_student([
                    'parentID'       => $parentID,
                    'srschoolyearID' => $schoolyearID
                ]);
                $fetchStudent = pluck($getStudents, 'srstudentID', 'srstudentID');
                if(customCompute($fetchStudent)) {
                    $this->data['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID($id, $schoolyearID);
                    if($this->data['maininvoice']) {
                        if(in_array($this->data['maininvoice']->maininvoicestudentID, $fetchStudent)) {
                            $this->data['invoices'] = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $id]);

                            $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maininvoice'], $schoolyearID, $this->data["maininvoice"]->maininvoicestudentID);

                            $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maininvoice"]->maininvoicestudentID]);

                            $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maininvoice']->maininvoiceusertypeID, $this->data['maininvoice']->maininvoiceuserID);

                            $this->data["subview"] = "invoice/view";
                            $this->load->view('_layout_main', $this->data);
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
        } else {
            $id = htmlentities((string) escapeString($this->uri->segment(3)));
            if((int)$id !== 0) {
                $this->data['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID($id, $schoolyearID);
                $this->data['invoices']    = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $id]);

                if(customCompute($this->data["maininvoice"])) {
                    $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maininvoice'], $schoolyearID, $this->data["maininvoice"]->maininvoicestudentID);

                    $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maininvoice"]->maininvoicestudentID]);

                    $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maininvoice']->maininvoiceusertypeID, $this->data['maininvoice']->maininvoiceuserID);

                    $this->data["subview"] = "invoice/view";
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
    }

    public function print_preview()
    {
        if(permissionChecker('invoice_view')) {
            $usertypeID             = $this->session->userdata("usertypeID");
            $schoolyearID           = $this->session->userdata('defaultschoolyearID');
            $this->data['feetypes'] = pluck($this->feetypes_m->get_feetypes(), 'feetypes', 'feetypesID');
            if($usertypeID == 3) {
                $id = htmlentities((string) escapeString($this->uri->segment(3)));
                if((int)$id !== 0) {
                    $studentID  = $this->session->userdata("loginuserID");
                    $getstudent = $this->studentrelation_m->get_single_student([
                        "srstudentID"    => $studentID,
                        'srschoolyearID' => $schoolyearID
                    ]);
                    if(customCompute($getstudent)) {
                        $this->data['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID($id, $schoolyearID);
                        if(customCompute($this->data['maininvoice']) && ($this->data['maininvoice']->maininvoicestudentID == $getstudent->studentID)) {
                            $this->data['invoices'] = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $id]);

                            $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maininvoice'], $schoolyearID, $this->data["maininvoice"]->maininvoicestudentID);

                            $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maininvoice"]->maininvoicestudentID]);

                            $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maininvoice']->maininvoiceusertypeID, $this->data['maininvoice']->maininvoiceuserID);
                            $this->reportPDF('invoicemodule.css', $this->data, 'invoice/print_preview');
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
            } elseif($usertypeID == 4) {
                $id = htmlentities((string) escapeString($this->uri->segment(3)));
                if((int)$id !== 0) {
                    $parentID     = $this->session->userdata("loginuserID");
                    $getstudents  = $this->studentrelation_m->get_order_by_student([
                        'parentID'       => $parentID,
                        'srschoolyearID' => $schoolyearID
                    ]);
                    $fetchStudent = pluck($getstudents, 'srstudentID', 'srstudentID');
                    if(customCompute($fetchStudent)) {
                        $this->data['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID($id, $schoolyearID);
                        if($this->data['maininvoice']) {
                            if(in_array($this->data['maininvoice']->maininvoicestudentID, $fetchStudent)) {
                                $this->data['invoices'] = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $id]);

                                $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maininvoice'], $schoolyearID, $this->data["maininvoice"]->maininvoicestudentID);

                                $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maininvoice"]->maininvoicestudentID]);

                                $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maininvoice']->maininvoiceusertypeID, $this->data['maininvoice']->maininvoiceuserID);

                                $this->reportPDF('invoicemodule.css', $this->data, 'invoice/print_preview');
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
            } else {
                $id = htmlentities((string) escapeString($this->uri->segment(3)));
                if((int)$id !== 0) {
                    $this->data['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID($id, $schoolyearID);
                    $this->data['invoices']    = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $id]);

                    if($this->data["maininvoice"]) {
                        $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maininvoice'], $schoolyearID, $this->data["maininvoice"]->maininvoicestudentID);

                        $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maininvoice"]->maininvoicestudentID]);

                        $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maininvoice']->maininvoiceusertypeID, $this->data['maininvoice']->maininvoiceuserID);
                        $this->reportPDF('invoicemodule.css', $this->data, 'invoice/print_preview');
                    } else {
                        $this->data["subview"] = "error";
                        $this->load->view('_layout_main', $this->data);
                    }
                } else {
                    $this->data["subview"] = "error";
                    $this->load->view('_layout_main', $this->data);
                }
            }
        } else {
            $this->data["subview"] = "errorpermission";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function send_mail()
    {
        $usertypeID             = $this->session->userdata("usertypeID");
        $schoolyearID           = $this->session->userdata('defaultschoolyearID');
        $this->data['feetypes'] = pluck($this->feetypes_m->get_feetypes(), 'feetypes', 'feetypesID');

        $retArray['status']  = FALSE;
        $retArray['message'] = '';
        if(permissionChecker('invoice_view')) {
            if($_POST !== []) {
                $rules = $this->send_mail_rules();
                $this->form_validation->set_rules($rules);
                if($this->form_validation->run() == FALSE) {
                    $retArray           = $this->form_validation->error_array();
                    $retArray['status'] = FALSE;
                    echo json_encode($retArray);
                    exit;
                } else {
                    $to      = $this->input->post('to');
                    $subject = $this->input->post('subject');
                    $message = $this->input->post('message');
                    $id      = $this->input->post('id');
                    $f       = FALSE;

                    if($usertypeID == 3) {
                        if((int)$id !== 0) {
                            $studentID  = $this->session->userdata("loginuserID");
                            $getstudent = $this->studentrelation_m->get_single_student([
                                'srstudentID'    => $studentID,
                                'srschoolyearID' => $schoolyearID
                            ]);

                            $this->data['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID($id, $schoolyearID);
                            if(customCompute($this->data['maininvoice']) && ($this->data['maininvoice']->maininvoicestudentID == $getstudent->studentID)) {
                                $f = TRUE;
                            }
                        }
                    } elseif($usertypeID == 4) {
                        if((int)$id !== 0) {
                            $parentID     = $this->session->userdata("loginuserID");
                            $getStudents  = $this->studentrelation_m->get_order_by_student([
                                'parentID'       => $parentID,
                                'srschoolyearID' => $schoolyearID
                            ]);
                            $fetchStudent = pluck($getStudents, 'srstudentID', 'srstudentID');
                            if(customCompute($fetchStudent)) {
                                $this->data['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID($id, $schoolyearID);
                                if(customCompute($this->data['maininvoice']) && in_array($this->data['maininvoice']->maininvoicestudentID, $fetchStudent)) {
                                    $f = TRUE;
                                }
                            }
                        }
                    } else {
                        $f = TRUE;
                    }

                    if($f) {
                        $id = $this->input->post('id');
                        if((int)$id !== 0) {
                            $this->data['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID($id);
                            $this->data['invoices']    = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $id]);
                            if(customCompute($this->data["maininvoice"])) {
                                $this->data['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->data['maininvoice'], $schoolyearID, $this->data["maininvoice"]->maininvoicestudentID);

                                $this->data["student"] = $this->student_m->get_single_student(['studentID' => $this->data["maininvoice"]->maininvoicestudentID]);

                                $this->data['createuser'] = getNameByUsertypeIDAndUserID($this->data['maininvoice']->maininvoiceusertypeID, $this->data['maininvoice']->maininvoiceuserID);

                                $this->reportSendToMail('invoicemodule.css', $this->data, 'invoice/print_preview', $to, $subject, $message);
                                $retArray['message'] = "Success";
                                $retArray['status']  = TRUE;
                                echo json_encode($retArray);
                                exit;
                            } else {
                                $retArray['message'] = $this->lang->line('invoice_data_not_found');
                                echo json_encode($retArray);
                                exit;
                            }
                        } else {
                            $retArray['message'] = $this->lang->line('invoice_id_not_found');
                            echo json_encode($retArray);
                            exit;
                        }
                    } else {
                        $retArray['message'] = $this->lang->line('invoice_authorize');
                        echo json_encode($retArray);
                        exit;
                    }
                }
            } else {
                $retArray['message'] = $this->lang->line('invoice_postmethod');
                echo json_encode($retArray);
                exit;
            }
        } else {
            $retArray['message'] = $this->lang->line('invoice_permission');
            echo json_encode($retArray);
            exit;
        }
    }

    protected function payment_rules( $invoices )
    {
        $rules = [
            [
                'field' => 'payment_method',
                'label' => $this->lang->line("invoice_paymentmethod"),
                'rules' => 'trim|required|xss_clean|max_length[11]|callback_unique_payment_method'
            ]
        ];

        if($invoices && customCompute($invoices)) {
            foreach($invoices as $invoice) {
                if($invoice->paidstatus != 2) {
                    $rules[] = [
                        'field' => 'paidamount_' . $invoice->invoiceID,
                        'label' => $this->lang->line("invoice_amount"),
                        'rules' => 'trim|xss_clean|max_length[15]|callback_unique_givenamount'
                    ];

                    $rules[] = [
                        'field' => 'weaver_' . $invoice->invoiceID,
                        'label' => $this->lang->line("invoice_weaver"),
                        'rules' => 'trim|xss_clean|max_length[15]|callback_unique_givenamount'
                    ];

                    $rules[] = [
                        'field' => 'fine_' . $invoice->invoiceID,
                        'label' => $this->lang->line("invoice_fine"),
                        'rules' => 'trim|xss_clean|max_length[15]|callback_unique_givenamount'
                    ];
                }
            }
        }

        return $rules;
    }

    public function unique_givenamount( $postValue )
    {
        if ($this->_amountgivenstatus == '') {
            $paidstatus   = FALSE;
            $weaverstatus = FALSE;
            $finestatus   = FALSE;
            $id           = htmlentities((string) escapeString($this->uri->segment(3)));
            $schoolyearID = $this->session->userdata('defaultschoolyearID');
            if((int)$id !== 0) {
                $maininvoice = $this->maininvoice_m->get_single_maininvoice(['maininvoiceID' => $id]);
                if(customCompute($maininvoice)) {
                    $invoices                = $this->invoice_m->get_order_by_invoice([
                        'maininvoiceID' => $id,
                        'deleted_at'    => 1
                    ]);
                    $invoicepaymentandweaver = $this->paymentdue($maininvoice, $schoolyearID, $maininvoice->maininvoicestudentID);
                    if(customCompute($invoices)) {
                        foreach($invoices as $invoice) {
                            if($invoice->paidstatus != 2) {
                                if($this->input->post('paidamount_' . $invoice->invoiceID) != '') {
                                    $paidstatus = TRUE;
                                }

                                if($this->input->post('weaver_' . $invoice->invoiceID) != '') {
                                    $weaverstatus = TRUE;
                                }

                                if($this->input->post('fine_' . $invoice->invoiceID) != '') {
                                    $finestatus = TRUE;
                                }
                            }

                            $amount = 0;
                            if(isset($invoicepaymentandweaver['totalamount'][$invoice->invoiceID])) {
                                $amount += (float)$invoicepaymentandweaver['totalamount'][$invoice->invoiceID];
                            }

                            if(isset($invoicepaymentandweaver['totaldiscount'][$invoice->invoiceID])) {
                                $amount -= (float)$invoicepaymentandweaver['totaldiscount'][$invoice->invoiceID];
                            }

                            if((float)$amount < (float)((float)$this->input->post('paidamount_' . $invoice->invoiceID) + (float)$this->input->post('weaver_' . $invoice->invoiceID))) {
                                if($this->input->post('paidamount_' . $invoice->invoiceID) != '') {
                                    $this->_amountgivenstatuserror[] = (float)$this->input->post('paidamount_' . $invoice->invoiceID);
                                }

                                if($this->input->post('weaver_' . $invoice->invoiceID) != '') {
                                    $this->_amountgivenstatuserror[] = (float)$this->input->post('weaver_' . $invoice->invoiceID);
                                }
                            }
                        }
                    }
                }
            }
            if ($this->session->userdata('usertypeID') == 1 || $this->session->userdata('usertypeID') == 5) {
                if($paidstatus || $weaverstatus || $finestatus) {
                    $this->_amountgivenstatus = TRUE;
                    return TRUE;
                } else {
                    $this->_amountgivenstatus = FALSE;
                    $this->form_validation->set_message("unique_givenamount", "The amount is required.");
                    return FALSE;
                }
            } elseif ($paidstatus) {
                $this->_amountgivenstatus = TRUE;
                return TRUE;
            } else {
                $this->_amountgivenstatus = FALSE;
                $this->form_validation->set_message("unique_givenamount", "The amount is required.");
                return FALSE;
            }
        } elseif ($this->_amountgivenstatus) {
            if($postValue != '') {
                if(in_array((float)$postValue, $this->_amountgivenstatuserror)) {
                    $this->form_validation->set_message("unique_givenamount", "The amount is required.");
                    return FALSE;
                } else {
                    return TRUE;
                }
            } else {
                return TRUE;
            }
        } else {
            $this->form_validation->set_message("unique_givenamount", "The amount is required.");
            return FALSE;
        }
    }

    public function unique_payment_method()
    {
        $payment_methods = $this->payment_gateway_m->get_order_by_payment_gateway(['status' => 1]);
        if(in_array(ucfirst((string) $this->input->post('payment_method')), $this->payment_methods($payment_methods))) {
            if(ucfirst((string) $this->input->post('payment_method')) === 'Select') {
                $this->form_validation->set_message("unique_payment_method", "Payment method is required.");
                return false;
            } else {
                if(!$this->payment_gateway->gateway($this->input->post('payment_method'))->status()) {
                    $this->form_validation->set_message("unique_payment_method", "The Payment method is disable now, try other payment method system");
                    return false;
                }
                return true;
            }
        }
    }

    public function payment()
    {
        if(permissionChecker('invoice_view')) {
            $this->data['headerassets'] = [
                'css' => [
                    'assets/select2/css/select2.css',
                    'assets/select2/css/select2-bootstrap.css',
                    'assets/datepicker/datepicker.css',
                ],
                'js'  => [
                    'assets/datepicker/datepicker.js',
                    'assets/select2/select2.js'
                ]
            ];

            $id           = htmlentities((string) escapeString($this->uri->segment(3)));
            $schoolyearID = $this->session->userdata('defaultschoolyearID');
            if((int)$id !== 0) {
                $maininvoice = $this->maininvoice_m->get_single_maininvoice(['maininvoiceID' => $id]);
                if(customCompute($maininvoice)) {
                    if($maininvoice->maininvoicestatus != 2) {
                        $this->data['student']        = $this->studentrelation_m->get_single_studentrelation([
                            'srstudentID'    => $maininvoice->maininvoicestudentID,
                            'srschoolyearID' => $schoolyearID
                        ]);
                        $this->data['studentprofile'] = $this->studentrelation_m->get_single_student([
                            'srstudentID'    => $maininvoice->maininvoicestudentID,
                            'srschoolyearID' => $schoolyearID
                        ]);
                        if(customCompute($this->data['student'])) {
                            $usertypeID = $this->session->userdata('usertypeID');
                            $userID     = $this->session->userdata('loginuserID');

                            $f = FALSE;
                            if($usertypeID == 3) {
                                if($this->data['student']->srstudentID == $userID) {
                                    $f = TRUE;
                                }
                            } elseif($usertypeID == 4) {
                                $parentID     = $this->session->userdata("loginuserID");
                                $getStudents  = $this->studentrelation_m->get_order_by_student([
                                    'parentID'       => $parentID,
                                    'srschoolyearID' => $schoolyearID
                                ]);
                                $fetchStudent = pluck($getStudents, 'srstudentID', 'srstudentID');
                                if(customCompute($fetchStudent) && in_array($this->data['student']->srstudentID, $fetchStudent)) {
                                    $f = TRUE;
                                }
                            } else {
                                $f = TRUE;
                            }

                            if($f) {
                                $this->data['usertype']                = $this->usertype_m->get_single_usertype(['usertypeID' => 3]);
                                $this->data['class']                   = $this->classes_m->general_get_single_classes(['classesID' => $this->data['student']->srclassesID]);
                                $this->data['section']                 = $this->section_m->general_get_single_section(['sectionID' => $this->data['student']->srsectionID]);
                                $this->data['invoices']                = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $id, 'deleted_at'    => 1]);
                                $this->data['feetypes']                = pluck($this->feetypes_m->get_feetypes(), 'feetypes', 'feetypesID');
                                $this->data['invoicepaymentandweaver'] = $this->paymentdue($maininvoice, $schoolyearID, $this->data['student']->srstudentID);
                                $this->data['payment_settings']        = $this->payment_gateway_m->get_order_by_payment_gateway(['status' => 1]);
                                $this->data['payment_options']         = pluck($this->payment_gateway_option_m->get_payment_gateway_option(), 'payment_value', 'payment_option');
                                $this->data['payment_gateway']         = $this->payment_methods($this->data['payment_settings']);
                                $this->data['maininvoice']             = $maininvoice;
                                if($_POST !== []) {
                                    $rules = $this->payment_rules($this->data['invoices']);
                                    $this->form_validation->set_rules($rules);
                                    if ($this->form_validation->run() == FALSE) {
                                        $this->data["subview"] = "invoice/payment";
                                        $this->load->view('_layout_main', $this->data);
                                    } elseif ($this->input->post('payment_method')) {
                                        $this->payment_gateway->gateway($this->input->post('payment_method'))->payment($this->input->post(), $maininvoice);
                                    } else {
                                        $this->session->set_flashdata('error', 'You are not authorized');
                                        redirect(base_url("invoice/payment/$id"));
                                    }
                                } else {
                                    $this->data["subview"] = "invoice/payment";
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

    public function viewpayment()
    {
        if(permissionChecker('invoice_view')) {
            $globalpaymentID = htmlentities((string) escapeString($this->uri->segment(3)));
            $maininvoiceID   = htmlentities((string) escapeString($this->uri->segment(4)));
            $schoolyearID    = $this->session->userdata('defaultschoolyearID');
            if((int)$globalpaymentID && (int)$maininvoiceID) {
                $globalpayment = $this->globalpayment_m->get_single_globalpayment([
                    'globalpaymentID' => $globalpaymentID,
                    'schoolyearID'    => $schoolyearID
                ]);
                $maininvoice   = $this->maininvoice_m->get_single_maininvoice([
                    'maininvoiceID'           => $maininvoiceID,
                    'maininvoiceschoolyearID' => $schoolyearID
                ]);
                if(customCompute($maininvoice) && customCompute($globalpayment)) {
                    $usertypeID = $this->session->userdata('usertypeID');
                    $userID     = $this->session->userdata('loginuserID');

                    $f = FALSE;
                    if($usertypeID == 3) {
                        $getstudent = $this->studentrelation_m->get_single_studentrelation([
                            'srstudentID'    => $globalpayment->studentID,
                            'srschoolyearID' => $globalpayment->schoolyearID
                        ]);
                        if(customCompute($getstudent) && $getstudent->srstudentID == $userID) {
                            $f = TRUE;
                        }
                    } elseif($usertypeID == 4) {
                        $parentID     = $this->session->userdata("loginuserID");
                        $schoolyearID = $this->session->userdata('defaultschoolyearID');
                        $getStudents  = $this->studentrelation_m->get_order_by_student([
                            'parentID'       => $parentID,
                            'srschoolyearID' => $schoolyearID
                        ]);
                        $fetchStudent = pluck($getStudents, 'srstudentID', 'srstudentID');
                        if(customCompute($fetchStudent) && in_array($globalpayment->studentID, $fetchStudent)) {
                            $f = TRUE;
                        }
                    } else {
                        $f = TRUE;
                    }

                    if($f) {
                        $studentrelation = $this->studentrelation_m->get_single_studentrelation([
                            'srstudentID'    => $globalpayment->studentID,
                            'srschoolyearID' => $globalpayment->schoolyearID
                        ]);
                        if(customCompute($studentrelation)) {
                            $this->data['feetypes'] = pluck($this->feetypes_m->get_feetypes(), 'feetypes', 'feetypesID');
                            $this->data['student']  = $this->student_m->get_single_student(['studentID' => $globalpayment->studentID]);
                            $this->data['invoices'] = pluck($this->invoice_m->get_order_by_invoice(['maininvoiceID' => $maininvoiceID]), 'obj', 'invoiceID');

                            $this->payment_m->order_payment('paymentID', 'asc');
                            $this->data['payments']       = $this->payment_m->get_order_by_payment(['globalpaymentID' => $globalpaymentID]);
                            $this->data['weaverandfines'] = pluck($this->weaverandfine_m->get_order_by_weaverandfine(['globalpaymentID' => $globalpaymentID]), 'obj', 'paymentID');

                            $this->data['paymenttype'] = '';
                            if(customCompute($this->data['payments'])) {
                                foreach($this->data['payments'] as $payment) {
                                    $this->data['paymenttype'] = $payment->paymenttype;
                                    break;
                                }
                            }

                            $this->data['studentrelation'] = $studentrelation;
                            $this->data['globalpayment']   = $globalpayment;
                            $this->data['maininvoice']     = $maininvoice;

                            $this->data["subview"] = "invoice/viewpayment";
                            $this->load->view('_layout_main', $this->data);
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
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function print_previewviewpayment()
    {
        if(permissionChecker('invoice_view')) {
            $globalpaymentID = htmlentities((string) escapeString($this->uri->segment(3)));
            $maininvoiceID   = htmlentities((string) escapeString($this->uri->segment(4)));
            $schoolyearID    = $this->session->userdata('defaultschoolyearID');

            if((int)$globalpaymentID && (int)$maininvoiceID) {
                $globalpayment = $this->globalpayment_m->get_single_globalpayment([
                    'globalpaymentID' => $globalpaymentID,
                    'schoolyearID'    => $schoolyearID
                ]);
                $maininvoice   = $this->maininvoice_m->get_single_maininvoice([
                    'maininvoiceID'           => $maininvoiceID,
                    'maininvoiceschoolyearID' => $schoolyearID
                ]);
                if(customCompute($maininvoice) && customCompute($globalpayment)) {
                    $usertypeID = $this->session->userdata('usertypeID');
                    $userID     = $this->session->userdata('loginuserID');

                    $f = FALSE;
                    if($usertypeID == 3) {
                        $getstudent = $this->studentrelation_m->get_single_studentrelation([
                            'srstudentID'    => $globalpayment->studentID,
                            'srschoolyearID' => $globalpayment->schoolyearID
                        ]);
                        if(customCompute($getstudent) && $getstudent->srstudentID == $userID) {
                            $f = TRUE;
                        }
                    } elseif($usertypeID == 4) {
                        $parentID     = $this->session->userdata("loginuserID");
                        $schoolyearID = $this->session->userdata('defaultschoolyearID');
                        $getStudents  = $this->studentrelation_m->get_order_by_student([
                            'parentID'       => $parentID,
                            'srschoolyearID' => $schoolyearID
                        ]);
                        $fetchStudent = pluck($getStudents, 'srstudentID', 'srstudentID');
                        if(customCompute($fetchStudent) && in_array($globalpayment->studentID, $fetchStudent)) {
                            $f = TRUE;
                        }
                    } else {
                        $f = TRUE;
                    }

                    if($f) {
                        $studentrelation = $this->studentrelation_m->get_single_studentrelation([
                            'srstudentID'    => $globalpayment->studentID,
                            'srschoolyearID' => $globalpayment->schoolyearID
                        ]);
                        if(customCompute($studentrelation)) {
                            $this->data['feetypes'] = pluck($this->feetypes_m->get_feetypes(), 'feetypes', 'feetypesID');
                            $this->data['student']  = $this->student_m->get_single_student(['studentID' => $globalpayment->studentID]);
                            $this->data['invoices'] = pluck($this->invoice_m->get_order_by_invoice(['maininvoiceID' => $maininvoiceID]), 'obj', 'invoiceID');
                            $this->payment_m->order_payment('paymentID', 'asc');
                            $this->data['payments']       = $this->payment_m->get_order_by_payment(['globalpaymentID' => $globalpaymentID]);
                            $this->data['weaverandfines'] = pluck($this->weaverandfine_m->get_order_by_weaverandfine(['globalpaymentID' => $globalpaymentID]), 'obj', 'paymentID');

                            $this->data['paymenttype'] = '';
                            if(customCompute($this->data['payments'])) {
                                foreach($this->data['payments'] as $payment) {
                                    $this->data['paymenttype'] = $payment->paymenttype;
                                    break;
                                }
                            }

                            $this->data['studentrelation'] = $studentrelation;
                            $this->data['globalpayment']   = $globalpayment;
                            $this->data['maininvoice']     = $maininvoice;

                            $this->reportPDF('invoicemodulepayment.css', $this->data, 'invoice/print_previewviewpayment');
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
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    protected function viewpayment_send_mail_rules()
    {
        return [
            [
                'field' => 'globalpaymentID',
                'label' => $this->lang->line('invoice_globalpaymentID'),
                'rules' => 'trim|required|xss_clean|numeric|callback_valid_data'
            ],
            [
                'field' => 'maininvoiceID',
                'label' => $this->lang->line('invoice_maininvoiceID'),
                'rules' => 'trim|required|xss_clean|numeric|callback_valid_data'
            ],
            [
                'field' => 'to',
                'label' => $this->lang->line('to'),
                'rules' => 'trim|required|xss_clean|valid_email'
            ],
            [
                'field' => 'subject',
                'label' => $this->lang->line('subject'),
                'rules' => 'trim|required|xss_clean'
            ],
            [
                'field' => 'message',
                'label' => $this->lang->line('message'),
                'rules' => 'trim|xss_clean'
            ]
        ];
    }

    public function viewpayment_send_mail()
    {
        $retArray['status']  = FALSE;
        $retArray['message'] = '';
        if(permissionChecker('invoice_view')) {
            if($_POST !== []) {
                $rules = $this->viewpayment_send_mail_rules();
                $this->form_validation->set_rules($rules);
                if($this->form_validation->run() == FALSE) {
                    $retArray           = $this->form_validation->error_array();
                    $retArray['status'] = FALSE;
                    echo json_encode($retArray);
                    exit;
                } else {
                    $schoolyearID    = $this->session->userdata('defaultschoolyearID');
                    $globalpaymentID = $this->input->post('globalpaymentID');
                    $maininvoiceID   = $this->input->post('maininvoiceID');
                    $to              = $this->input->post('to');
                    $subject         = $this->input->post('subject');
                    $message         = $this->input->post('message');

                    if((int)$globalpaymentID && (int)$maininvoiceID) {
                        $globalpayment = $this->globalpayment_m->get_single_globalpayment([
                            'globalpaymentID' => $globalpaymentID,
                            'schoolyearID'    => $schoolyearID
                        ]);
                        $maininvoice   = $this->maininvoice_m->get_single_maininvoice([
                            'maininvoiceID'           => $maininvoiceID,
                            'maininvoiceschoolyearID' => $schoolyearID
                        ]);

                        if(customCompute($maininvoice) && customCompute($globalpayment)) {
                            $usertypeID = $this->session->userdata('usertypeID');
                            $userID     = $this->session->userdata('loginuserID');

                            $f = FALSE;
                            if($usertypeID == 3) {
                                $getstudent = $this->studentrelation_m->get_single_studentrelation([
                                    'srstudentID'    => $globalpayment->studentID,
                                    'srschoolyearID' => $globalpayment->schoolyearID
                                ]);
                                if(customCompute($getstudent) && $getstudent->srstudentID == $userID) {
                                    $f = TRUE;
                                }
                            } elseif($usertypeID == 4) {
                                $parentID     = $this->session->userdata("loginuserID");
                                $schoolyearID = $this->session->userdata('defaultschoolyearID');
                                $getStudents  = $this->studentrelation_m->get_order_by_student([
                                    'parentID'       => $parentID,
                                    'srschoolyearID' => $schoolyearID
                                ]);
                                $fetchStudent = pluck($getStudents, 'srstudentID', 'srstudentID');
                                if(customCompute($fetchStudent) && in_array($globalpayment->studentID, $fetchStudent)) {
                                    $f = TRUE;
                                }
                            } else {
                                $f = TRUE;
                            }

                            if($f) {
                                $studentrelation = $this->studentrelation_m->get_single_studentrelation([
                                    'srstudentID'    => $globalpayment->studentID,
                                    'srschoolyearID' => $globalpayment->schoolyearID
                                ]);
                                if(customCompute($studentrelation)) {
                                    $this->data['feetypes'] = pluck($this->feetypes_m->get_feetypes(), 'feetypes', 'feetypesID');
                                    $this->data['student']  = $this->student_m->get_single_student(['studentID' => $globalpayment->studentID]);
                                    $this->data['invoices'] = pluck($this->invoice_m->get_order_by_invoice(['maininvoiceID' => $maininvoiceID]), 'obj', 'invoiceID');
                                    $this->payment_m->order_payment('paymentID', 'asc');
                                    $this->data['payments']       = $this->payment_m->get_order_by_payment(['globalpaymentID' => $globalpaymentID]);
                                    $this->data['weaverandfines'] = pluck($this->weaverandfine_m->get_order_by_weaverandfine(['globalpaymentID' => $globalpaymentID]), 'obj', 'paymentID');

                                    $this->data['paymenttype'] = '';
                                    if(customCompute($this->data['payments'])) {
                                        foreach($this->data['payments'] as $payment) {
                                            $this->data['paymenttype'] = $payment->paymenttype;
                                            break;
                                        }
                                    }

                                    $this->data['studentrelation'] = $studentrelation;
                                    $this->data['globalpayment']   = $globalpayment;
                                    $this->data['maininvoice']     = $maininvoice;

                                    $this->reportSendToMail('invoicemodulepayment.css', $this->data, 'invoice/print_previewviewpayment', $to, $subject, $message);
                                    $retArray['message'] = "Success";
                                    $retArray['status']  = TRUE;
                                    echo json_encode($retArray);
                                } else {
                                    $retArray['message'] = $this->lang->line('invoice_data_not_found');
                                    echo json_encode($retArray);
                                    exit;
                                }
                            } else {
                                $retArray['message'] = $this->lang->line('invoice_data_not_found');
                                echo json_encode($retArray);
                                exit;
                            }
                        } else {
                            $retArray['message'] = $this->lang->line('invoice_data_not_found');
                            echo json_encode($retArray);
                            exit;
                        }
                    } else {
                        $retArray['message'] = $this->lang->line('invoice_data_not_found');
                        echo json_encode($retArray);
                        exit;
                    }
                }
            } else {
                $retArray['message'] = $this->lang->line('invoice_postmethod');
                echo json_encode($retArray);
                exit;
            }
        } else {
            $retArray['message'] = $this->lang->line('invoice_permission');
            echo json_encode($retArray);
            exit;
        }
    }

    public function valid_data( $data )
    {
        if($data == 0) {
            $this->form_validation->set_message('valid_data', 'The %s field is required.');
            return FALSE;
        }
        return TRUE;
    }

    public function unique_classID()
    {
        if($this->input->post('classesID') == 0) {
            $this->form_validation->set_message("unique_classID", "The %s field is required");
            return FALSE;
        }
        return TRUE;
    }

    public function unique_studentID()
    {
        $id = $this->input->post('editID');
        if ((int)$id && $id > 0 && $this->input->post('studentID') == 0) {
            $this->form_validation->set_message("unique_studentID", "%s field is required.");
            return FALSE;
        }
        return TRUE;
    }

    public function date_valid( $date )
    {
        if(strlen((string) $date) < 10) {
            $this->form_validation->set_message("date_valid", "%s is not valid dd-mm-yyyy");
            return FALSE;
        } else {
            $arr  = explode("-", (string) $date);
            $dd   = $arr[0];
            $mm   = $arr[1];
            $yyyy = $arr[2];
            if(checkdate($mm, $dd, $yyyy)) {
                return TRUE;
            } else {
                $this->form_validation->set_message("date_valid", "%s is not valid dd-mm-yyyy");
                return FALSE;
            }
        }
    }

    public function unique_status()
    {
        if($this->input->post('statusID') === '5') {
            $this->form_validation->set_message("unique_status", "The %s field is required.");
            return FALSE;
        } else {
            $array = [0, 1, 2];

            if(!in_array($this->input->post('statusID'), $array)) {
                $this->form_validation->set_message("unique_status", "The %s field is required.");
                return FALSE;
            }
        }
        return TRUE;
    }

    public function unique_feetypeitems()
    {
        $feetypeitems = json_decode((string) $this->input->post('feetypeitems'));
        $status       = [];
        if(customCompute($feetypeitems)) {
            foreach($feetypeitems as $feetypeitem) {
                if($feetypeitem->amount == '') {
                    $status[] = FALSE;
                }
            }
        } else {
            $this->form_validation->set_message("unique_feetypeitems", "The fee type item is required.");
            return FALSE;
        }

        if(in_array(FALSE, $status)) {
            $this->form_validation->set_message("unique_feetypeitems", "The fee type amount is required.");
            return FALSE;
        }
        return TRUE;
    }

    public function getstudent()
    {
        $classesID    = $this->input->post('classesID');
        $schoolyearID = $this->session->userdata('defaultschoolyearID');

        if($this->input->post('edittype')) {
            echo '<option value="0">' . $this->lang->line('invoice_select_student') . '</option>';
        } else {
            echo '<option value="0">' . $this->lang->line('invoice_all_student') . '</option>';
        }

        $students = $this->studentrelation_m->get_order_by_student([
            'srschoolyearID' => $schoolyearID,
            'srclassesID'    => $classesID
        ]);
        if(customCompute($students)) {
            foreach($students as $student) {
                echo "<option value=\"$student->srstudentID\">" . $student->srname . " - " . $this->lang->line('invoice_roll') . " - " . $student->srroll . "</option>";
            }
        }
    }

    public function saveinvoice()
    {
        $maininvoiceID      = 0;
        $retArray['status'] = FALSE;
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
            if(permissionChecker('invoice_add') || permissionChecker('invoice_edit')) {
                if($_POST !== []) {
                    $rules = $this->rules($this->input->post('statusID'));
                    $this->form_validation->set_rules($rules);
                    if($this->form_validation->run() == FALSE) {
                        $retArray['error']  = $this->form_validation->error_array();
                        $retArray['status'] = FALSE;
                        echo json_encode($retArray);
                        exit;
                    } else {
                        $invoiceMainArray     = [];
                        $globalPaymentArray   = [];
                        $invoiceArray         = [];
                        $paymentArray         = [];
                        $paymentHistoryArray  = [];
                        $studentArray         = [];
                        $globalPaymentIDArray = [];
                        $feetype              = pluck($this->feetypes_m->get_feetypes(), 'feetypes', 'feetypesID');
                        $feetypeitems         = json_decode((string) $this->input->post('feetypeitems'));
                        $schoolyearID         = $this->session->userdata('defaultschoolyearID');

                        $studentID = $this->input->post('studentID');
                        $classesID = $this->input->post('classesID');
                        if(((int)$studentID || $studentID == 0) && (int)($classesID)) {
                            if($studentID == 0) {
                                $getstudents = $this->studentrelation_m->get_order_by_student([
                                    "srclassesID"    => $classesID,
                                    'srschoolyearID' => $schoolyearID
                                ]);
                            } else {
                                $getstudents = $this->studentrelation_m->get_order_by_student([
                                    "srclassesID"    => $classesID,
                                    'srstudentID'    => $studentID,
                                    'srschoolyearID' => $schoolyearID
                                ]);
                            }

                            if(customCompute($getstudents)) {
                                $paymentStatus = 0;
                                if($this->input->post('statusID') !== '0') {
                                    if ((float)$this->input->post('totalsubtotal') == (float)0) {
                                        $paymentStatus = 2;
                                    } elseif ((float)$this->input->post('totalpaidamount') > (float)0) {
                                        $paymentStatus = (float)$this->input->post('totalsubtotal') === (float)$this->input->post('totalpaidamount') ? 2 : 1;
                                    }
                                }

                                $clearancetype = 'unpaid';
                                if($paymentStatus == 0) {
                                    $clearancetype = 'unpaid';
                                } elseif($paymentStatus == 1) {
                                    $clearancetype = 'partial';
                                } elseif($paymentStatus === 2) {
                                    $clearancetype = 'paid';
                                }

                                foreach($getstudents as $key => $getstudent) {
                                    $invoiceMainArray[] = [
                                        'maininvoiceschoolyearID' => $schoolyearID,
                                        'maininvoiceclassesID'    => $this->input->post('classesID'),
                                        'maininvoicestudentID'    => $getstudent->srstudentID,
                                        'maininvoicestatus'       => (($this->input->post('statusID') !== '0') ? (((float)$this->input->post('totalsubtotal') == (float)0) ? 2 : (((float)$this->input->post('totalpaidamount') > (float)0) ? ((float)$this->input->post('totalsubtotal') === (float)$this->input->post('totalpaidamount') ? 2 : 1) : 0)) : 0),
                                        'maininvoiceuserID'       => $this->session->userdata('loginuserID'),
                                        'maininvoiceusertypeID'   => $this->session->userdata('usertypeID'),
                                        'maininvoiceuname'        => $this->session->userdata('name'),
                                        'maininvoicedate'         => date("Y-m-d", strtotime((string) $this->input->post("date"))),
                                        'maininvoicecreate_date'  => date('Y-m-d'),
                                        'maininvoiceday'          => date('d'),
                                        'maininvoicemonth'        => date('m'),
                                        'maininvoiceyear'         => date('Y'),
                                        'maininvoicedeleted_at'   => 1
                                    ];

                                    $globalPaymentArray[] = [
                                        'classesID'          => $getstudent->srclassesID,
                                        'sectionID'          => $getstudent->srsectionID,
                                        'studentID'          => $getstudent->srstudentID,
                                        'clearancetype'      => $clearancetype,
                                        'invoicename'        => $getstudent->srregisterNO . '-' . $getstudent->srname,
                                        'invoicedescription' => '',
                                        'paymentyear'        => date('Y'),
                                        'schoolyearID'       => $schoolyearID,
                                    ];

                                    $studentArray[] = $getstudent->srstudentID;
                                }

                                if(customCompute($invoiceMainArray)) {
                                    $count   = customCompute($invoiceMainArray);
                                    $firstID = $this->maininvoice_m->insert_batch_maininvoice($invoiceMainArray);

                                    $lastID = $firstID + ($count - 1);

                                    if($lastID >= $firstID) {
                                        $j = 0;
                                        for($i = $firstID; $i <= $lastID; $i++) {
                                            if(customCompute($feetypeitems)) {
                                                foreach($feetypeitems as $feetypeitem) {
                                                    $invoiceArray[] = [
                                                        'schoolyearID'  => $invoiceMainArray[$j]['maininvoiceschoolyearID'],
                                                        'classesID'     => $invoiceMainArray[$j]['maininvoiceclassesID'],
                                                        'studentID'     => $invoiceMainArray[$j]['maininvoicestudentID'],
                                                        'feetypeID'     => isset($feetypeitem->feetypeID) ? $feetypeitem->feetypeID : 0,
                                                        'feetype'       => isset($feetype[$feetypeitem->feetypeID]) ? $feetype[$feetypeitem->feetypeID] : '',
                                                        'amount'        => isset($feetypeitem->amount) ? $feetypeitem->amount : 0,
                                                        'discount'      => (isset($feetypeitem->discount) ? (($feetypeitem->discount == '') ? 0 : $feetypeitem->discount) : 0),
                                                        'paidstatus'    => ($this->input->post('statusID') !== '0') ? (((float)$feetypeitem->paidamount > (float)0) ? (((float)$feetypeitem->subtotal === (float)$feetypeitem->paidamount) ? 2 : 1) : 0) : 0,
                                                        'userID'        => $invoiceMainArray[$j]['maininvoiceuserID'],
                                                        'usertypeID'    => $invoiceMainArray[$j]['maininvoiceusertypeID'],
                                                        'uname'         => $invoiceMainArray[$j]['maininvoiceuname'],
                                                        'date'          => $invoiceMainArray[$j]['maininvoicedate'],
                                                        'create_date'   => $invoiceMainArray[$j]['maininvoicecreate_date'],
                                                        'day'           => $invoiceMainArray[$j]['maininvoiceday'],
                                                        'month'         => $invoiceMainArray[$j]['maininvoicemonth'],
                                                        'year'          => $invoiceMainArray[$j]['maininvoiceyear'],
                                                        'deleted_at'    => $invoiceMainArray[$j]['maininvoicedeleted_at'],
                                                        'maininvoiceID' => $i
                                                    ];

                                                    $paymentHistoryArray[] = [
                                                        'paymenttype'   => ucfirst((string) $this->input->post('payment_method')),
                                                        'paymentamount' => $feetypeitem->paidamount
                                                    ];
                                                }
                                            }
                                            $j++;
                                        }
                                    }
                                }

                                $paymentInserStatus = 0;
                                if($this->input->post('statusID') == !'0') {
                                    if($this->input->post('totalpaidamount') > 0) {
                                        if((float)$this->input->post('totalsubtotal') === (float)$this->input->post('totalpaidamount')) {
                                            $paymentInserStatus = 2;
                                        } else {
                                            $paymentInserStatus = 1;
                                        }
                                    } else {
                                        $paymentInserStatus = 0;
                                    }
                                }

                                $invoicefirstID = $this->invoice_m->insert_batch_invoice($invoiceArray);

                                $invoiceSubtotalStatus = 1;
                                if((float)$this->input->post('totalsubtotal') == (float)0) {
                                    $invoiceSubtotalStatus = 0;
                                }

                                if ($paymentInserStatus && $invoiceSubtotalStatus && customCompute($invoiceArray)) {
                                    $invoicecount   = customCompute($invoiceArray);
                                    $invoicefirstID = $invoicefirstID;
                                    $invoicelastID  = $invoicefirstID + ($invoicecount - 1);
                                    $globalcount   = customCompute($globalPaymentArray);
                                    $globalfirstID = $this->globalpayment_m->insert_batch_globalpayment($globalPaymentArray);
                                    $globallastID  = $globalfirstID + ($globalcount - 1);
                                    if(customCompute($studentArray)) {
                                        $studentcount = customCompute($getstudents);
                                        for($n = 0; $n <= ($studentcount - 1); $n++) {
                                            $globalPaymentIDArray[$studentArray[$n]] = $globalfirstID;
                                            $globalfirstID++;
                                        }
                                    }
                                    if($invoicelastID >= $invoicefirstID) {
                                        $k = 0;
                                        for($i = $invoicefirstID; $i <= $invoicelastID; $i++) {
                                            $paymentArray[] = [
                                                'schoolyearID'    => $invoiceArray[$k]['schoolyearID'],
                                                'invoiceID'       => $i,
                                                'studentID'       => $invoiceArray[$k]['studentID'],
                                                'paymentamount'   => isset($paymentHistoryArray[$k]['paymentamount']) ? (($paymentHistoryArray[$k]['paymentamount'] == "") ? NULL : $paymentHistoryArray[$k]['paymentamount']) : 0,
                                                'paymenttype'     => ucfirst((string) $this->input->post('payment_method')),
                                                'paymentdate'     => date('Y-m-d'),
                                                'paymentday'      => date('d'),
                                                'paymentmonth'    => date('m'),
                                                'paymentyear'     => date('Y'),
                                                'userID'          => $invoiceArray[$k]['userID'],
                                                'usertypeID'      => $invoiceArray[$k]['usertypeID'],
                                                'uname'           => $invoiceArray[$k]['uname'],
                                                'transactionID'   => 'CASHANDCHEQUE' . random19(),
                                                'globalpaymentID' => isset($globalPaymentIDArray[$invoiceArray[$k]['studentID']]) ? $globalPaymentIDArray[$invoiceArray[$k]['studentID']] : 0,
                                            ];
                                            $k++;
                                        }
                                    }
                                    if(customCompute($paymentArray)) {
                                        $this->payment_m->insert_batch_payment($paymentArray);
                                    }
                                }

                                $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                                $retArray['status']  = TRUE;
                                $retArray['message'] = 'Success';
                                echo json_encode($retArray);
                                exit;
                            } else {
                                $retArray['error'] = ['student' => 'Student not found.'];
                                echo json_encode($retArray);
                                exit;
                            }
                        } else {
                            $retArray['error'] = ['classstudent' => 'Class and Student not found.'];
                            echo json_encode($retArray);
                            exit;
                        }
                    }
                } else {
                    $retArray['error'] = ['posttype' => 'Post type is required.'];
                    echo json_encode($retArray);
                    exit;
                }
            } else {
                $retArray['error'] = ['permission' => 'Invoice permission is required.'];
                echo json_encode($retArray);
                exit;
            }
        } else {
            $retArray['error'] = ['permission' => 'Permission Denied.'];
            echo json_encode($retArray);
            exit;
        }
    }

    public function saveinvoicefforedit()
    {
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('defaultschoolyearID') == 5)) {
            $maininvoiceID      = 0;
            $retArray['status'] = FALSE;
            if(permissionChecker('invoice_edit')) {
                if($_POST !== []) {
                    $rules = $this->rules($this->input->post('statusID'));
                    $this->form_validation->set_rules($rules);
                    if($this->form_validation->run() == FALSE) {
                        $retArray['error']  = $this->form_validation->error_array();
                        $retArray['status'] = FALSE;
                        echo json_encode($retArray);
                        exit;
                    } else {
                        $globalPaymentArray  = [];
                        $mainInvoiceArray    = [];
                        $invoiceArray        = [];
                        $paymentArray        = [];
                        $paymentHistoryArray = [];

                        $editID = $this->input->post('editID');
                        if((int)$editID !== 0) {
                            $feetype      = pluck($this->feetypes_m->get_feetypes(), 'feetypes', 'feetypesID');
                            $feetypeitems = json_decode((string) $this->input->post('feetypeitems'));
                            $schoolyearID = $this->session->userdata('defaultschoolyearID');

                            $studentID = $this->input->post('studentID');
                            $classesID = $this->input->post('classesID');

                            if((int)$studentID && (int)$classesID) {
                                $getstudent = $this->studentrelation_m->get_single_student([
                                    "srclassesID"    => $classesID,
                                    'srstudentID'    => $studentID,
                                    'srschoolyearID' => $schoolyearID
                                ]);
                                if(customCompute($getstudent)) {
                                    $paymentStatus = 0;
                                    if($this->input->post('statusID') !== '0') {
                                        if ((float)$this->input->post('totalsubtotal') == (float)0) {
                                            $paymentStatus = 2;
                                        } elseif ($this->input->post('totalpaidamount') > 0) {
                                            $paymentStatus = (float)$this->input->post('totalsubtotal') === (float)$this->input->post('totalpaidamount') ? 2 : 1;
                                        }
                                    }

                                    $clearancetype = 'unpaid';
                                    if($paymentStatus == 0) {
                                        $clearancetype = 'unpaid';
                                    } elseif($paymentStatus == 1) {
                                        $clearancetype = 'partial';
                                    } elseif($paymentStatus === 2) {
                                        $clearancetype = 'paid';
                                    }

                                    if(customCompute($feetypeitems)) {
                                        foreach($feetypeitems as $feetypeitem) {
                                            $invoiceArray[] = [
                                                'schoolyearID'  => $schoolyearID,
                                                'classesID'     => $this->input->post('classesID'),
                                                'studentID'     => $getstudent->srstudentID,
                                                'feetypeID'     => isset($feetypeitem->feetypeID) ? $feetypeitem->feetypeID : 0,
                                                'feetype'       => isset($feetype[$feetypeitem->feetypeID]) ? $feetype[$feetypeitem->feetypeID] : '',
                                                'amount'        => isset($feetypeitem->amount) ? $feetypeitem->amount : 0,
                                                'discount'      => (isset($feetypeitem->discount) ? (($feetypeitem->discount == '') ? 0 : $feetypeitem->discount) : 0),
                                                'paidstatus'    => ($this->input->post('statusID') !== '0') ? (($feetypeitem->paidamount > 0) ? (((float)$feetypeitem->subtotal === (float)$feetypeitem->paidamount) ? 2 : 1) : 0) : 0,
                                                'userID'        => $this->session->userdata('loginuserID'),
                                                'usertypeID'    => $this->session->userdata('usertypeID'),
                                                'uname'         => $this->session->userdata('name'),
                                                'date'          => date("Y-m-d", strtotime((string) $this->input->post("date"))),
                                                'create_date'   => date('Y-m-d'),
                                                'day'           => date('d'),
                                                'month'         => date('m'),
                                                'year'          => date('Y'),
                                                'deleted_at'    => 1,
                                                'maininvoiceID' => $editID
                                            ];

                                            $paymentHistoryArray[] = [
                                                'paymenttype'   => ucfirst((string) $this->input->post('payment_method')),
                                                'paymentamount' => $feetypeitem->paidamount
                                            ];
                                        }
                                    }

                                    $globalPaymentArray = [
                                        'classesID'          => $getstudent->srclassesID,
                                        'sectionID'          => $getstudent->srsectionID,
                                        'studentID'          => $getstudent->srstudentID,
                                        'clearancetype'      => $clearancetype,
                                        'invoicename'        => $getstudent->srregisterNO . '-' . $getstudent->srname,
                                        'invoicedescription' => '',
                                        'paymentyear'        => date('Y'),
                                        'schoolyearID'       => $schoolyearID,
                                    ];

                                    $this->invoice_m->delete_invoice_by_maininvoiceID($editID);

                                    $invoicefirstID = $this->invoice_m->insert_batch_invoice($invoiceArray);


                                    $paymentInserStatus = 0;
                                    if($this->input->post('statusID') == !'0') {
                                        if($this->input->post('totalpaidamount') > 0) {
                                            if((float)$this->input->post('totalsubtotal') === (float)$this->input->post('totalpaidamount')) {
                                                $paymentInserStatus = 2;
                                            } else {
                                                $paymentInserStatus = 1;
                                            }
                                        } else {
                                            $paymentInserStatus = 0;
                                        }
                                    }

                                    if ($paymentInserStatus !== 0 && customCompute($invoiceArray)) {
                                        $globalpaymentID = $this->globalpayment_m->insert_globalpayment($globalPaymentArray);
                                        $invoicecount   = customCompute($invoiceArray);
                                        $invoicefirstID = $invoicefirstID;
                                        $invoicelastID  = $invoicefirstID + ($invoicecount - 1);
                                        if($invoicelastID >= $invoicefirstID) {
                                            $k = 0;
                                            for($i = $invoicefirstID; $i <= $invoicelastID; $i++) {
                                                $paymentArray[] = [
                                                    'schoolyearID'    => $invoiceArray[$k]['schoolyearID'],
                                                    'invoiceID'       => $i,
                                                    'studentID'       => $invoiceArray[$k]['studentID'],
                                                    'paymentamount'   => isset($paymentHistoryArray[$k]['paymentamount']) ? (($paymentHistoryArray[$k]['paymentamount'] == "") ? NULL : $paymentHistoryArray[$k]['paymentamount']) : 0,
                                                    'paymenttype'     => ucfirst((string) $this->input->post('payment_method')),
                                                    'paymentdate'     => date('Y-m-d'),
                                                    'paymentday'      => date('d'),
                                                    'paymentmonth'    => date('m'),
                                                    'paymentyear'     => date('Y'),
                                                    'userID'          => $invoiceArray[$k]['userID'],
                                                    'usertypeID'      => $invoiceArray[$k]['usertypeID'],
                                                    'uname'           => $invoiceArray[$k]['uname'],
                                                    'transactionID'   => 'CASHANDCHEQUE' . random19(),
                                                    'globalpaymentID' => $globalpaymentID
                                                ];
                                                $k++;
                                            }
                                        }
                                        $this->payment_m->insert_batch_payment($paymentArray);
                                        $mainInvoiceArray = [
                                            'maininvoicestatus' => (($this->input->post('statusID') !== '0') ? (((float)$this->input->post('totalsubtotal') == (float)0) ? 2 : (($this->input->post('totalpaidamount') > 0) ? ((float)$this->input->post('totalsubtotal') === (float)$this->input->post('totalpaidamount') ? 2 : 1) : 0)) : 0)
                                        ];
                                        $this->maininvoice_m->update_maininvoice($mainInvoiceArray, $editID);
                                    }

                                    $this->session->set_flashdata('success', $this->lang->line('menu_success'));
                                    $retArray['status']  = TRUE;
                                    $retArray['message'] = 'Success';
                                    echo json_encode($retArray);
                                    exit;
                                } else {
                                    $retArray['error'] = ['student' => 'Student not found.'];
                                    echo json_encode($retArray);
                                    exit;
                                }
                            } else {
                                $retArray['error'] = ['classstudent' => 'Class and Student not found.'];
                                echo json_encode($retArray);
                                exit;
                            }
                        } else {
                            $retArray['error'] = ['editid' => 'Edit id is required.'];
                            echo json_encode($retArray);
                            exit;
                        }
                    }
                } else {
                    $retArray['error'] = ['posttype' => 'Post type is required.'];
                    echo json_encode($retArray);
                    exit;
                }
            } else {
                $retArray['error'] = ['permission' => 'Invoice permission is required.'];
                echo json_encode($retArray);
                exit;
            }
        } else {
            $retArray['error'] = ['permission' => 'Permission Denied.'];
            echo json_encode($retArray);
            exit;
        }
    }

    private function grandtotalandpaid( $maininvoices, $schoolyearID )
    {
        $retArray           = [];
        $invoiceitems       = pluck_multi_array_key($this->invoice_m->get_order_by_invoice(['schoolyearID' => $schoolyearID]), 'obj', 'maininvoiceID', 'invoiceID');
        $paymentitems       = pluck_multi_array($this->payment_m->get_order_by_payment([
            'schoolyearID'     => $schoolyearID,
            'paymentamount !=' => NULL
        ]), 'obj', 'invoiceID');
        $weaverandfineitems = pluck_multi_array($this->weaverandfine_m->get_order_by_weaverandfine(['schoolyearID' => $schoolyearID]), 'obj', 'invoiceID');
        if(customCompute($maininvoices)) {
            foreach($maininvoices as $maininvoice) {
                if(isset($invoiceitems[$maininvoice->maininvoiceID]) && customCompute($invoiceitems[$maininvoice->maininvoiceID])) {
                    foreach($invoiceitems[$maininvoice->maininvoiceID] as $invoiceitem) {
                        $amount = $invoiceitem->amount;
                        if($invoiceitem->discount > 0) {
                            $amount = ($invoiceitem->amount - (($invoiceitem->amount / 100) * $invoiceitem->discount));
                        }

                        if(isset($retArray['grandtotal'][$maininvoice->maininvoiceID])) {
                            $retArray['grandtotal'][$maininvoice->maininvoiceID] += $amount;
                        } else {
                            $retArray['grandtotal'][$maininvoice->maininvoiceID] = $amount;
                        }

                        if(isset($retArray['totalamount'][$maininvoice->maininvoiceID])) {
                            $retArray['totalamount'][$maininvoice->maininvoiceID] += $invoiceitem->amount;
                        } else {
                            $retArray['totalamount'][$maininvoice->maininvoiceID] = $invoiceitem->amount;
                        }

                        if(isset($retArray['totaldiscount'][$maininvoice->maininvoiceID])) {
                            $retArray['totaldiscount'][$maininvoice->maininvoiceID] += ($invoiceitem->amount / 100) * $invoiceitem->discount;
                        } else {
                            $retArray['totaldiscount'][$maininvoice->maininvoiceID] = (($invoiceitem->amount / 100) * $invoiceitem->discount);
                        }

                        if(isset($paymentitems[$invoiceitem->invoiceID]) && customCompute($paymentitems[$invoiceitem->invoiceID])) {
                            foreach($paymentitems[$invoiceitem->invoiceID] as $paymentitem) {
                                if(isset($retArray['totalpayment'][$maininvoice->maininvoiceID])) {
                                    $retArray['totalpayment'][$maininvoice->maininvoiceID] += $paymentitem->paymentamount;
                                } else {
                                    $retArray['totalpayment'][$maininvoice->maininvoiceID] = $paymentitem->paymentamount;
                                }
                            }
                        }

                        if(isset($weaverandfineitems[$invoiceitem->invoiceID]) && customCompute($weaverandfineitems[$invoiceitem->invoiceID])) {
                            foreach($weaverandfineitems[$invoiceitem->invoiceID] as $weaverandfineitem) {
                                if(isset($retArray['totalweaver'][$maininvoice->maininvoiceID])) {
                                    $retArray['totalweaver'][$maininvoice->maininvoiceID] += $weaverandfineitem->weaver;
                                } else {
                                    $retArray['totalweaver'][$maininvoice->maininvoiceID] = $weaverandfineitem->weaver;
                                }

                                if(isset($retArray['totalfine'][$maininvoice->maininvoiceID])) {
                                    $retArray['totalfine'][$maininvoice->maininvoiceID] += $weaverandfineitem->fine;
                                } else {
                                    $retArray['totalfine'][$maininvoice->maininvoiceID] = $weaverandfineitem->fine;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $retArray;
    }

    private function grandtotalandpaidsingle( $maininvoice, $schoolyearID, $studentID = NULL )
    {
        $retArray = [
            'grandtotal'    => 0,
            'totalamount'   => 0,
            'totaldiscount' => 0,
            'totalpayment'  => 0,
            'totalfine'     => 0,
            'totalweaver'   => 0
        ];
        if(customCompute($maininvoice)) {
            if((int)$studentID && $studentID != NULL) {
                $invoiceitems       = pluck_multi_array_key($this->invoice_m->get_order_by_invoice([
                    'studentID'     => $studentID,
                    'maininvoiceID' => $maininvoice->maininvoiceID,
                    'schoolyearID'  => $schoolyearID
                ]), 'obj', 'maininvoiceID', 'invoiceID');
                $paymentitems       = pluck_multi_array($this->payment_m->get_order_by_payment([
                    'schoolyearID'     => $schoolyearID,
                    'paymentamount !=' => NULL
                ]), 'obj', 'invoiceID');
                $weaverandfineitems = pluck_multi_array($this->weaverandfine_m->get_order_by_weaverandfine(['schoolyearID' => $schoolyearID]), 'obj', 'invoiceID');
            } else {
                $invoiceitem        = [];
                $paymentitems       = [];
                $weaverandfineitems = [];
            }

            if(isset($invoiceitems[$maininvoice->maininvoiceID]) && customCompute($invoiceitems[$maininvoice->maininvoiceID])) {
                foreach($invoiceitems[$maininvoice->maininvoiceID] as $invoiceitem) {
                    $amount = $invoiceitem->amount;
                    if($invoiceitem->discount > 0) {
                        $amount = ($invoiceitem->amount - (($invoiceitem->amount / 100) * $invoiceitem->discount));
                    }

                    $retArray['grandtotal'] = isset($retArray['grandtotal']) ? $retArray['grandtotal'] + $amount : $amount;

                    if(isset($retArray['totalamount'])) {
                        $retArray['totalamount'] += $invoiceitem->amount;
                    } else {
                        $retArray['totalamount'] = $invoiceitem->amount;
                    }

                    if(isset($retArray['totaldiscount'])) {
                        $retArray['totaldiscount'] += ($invoiceitem->amount / 100) * $invoiceitem->discount;
                    } else {
                        $retArray['totaldiscount'] = (($invoiceitem->amount / 100) * $invoiceitem->discount);
                    }

                    if(isset($paymentitems[$invoiceitem->invoiceID]) && customCompute($paymentitems[$invoiceitem->invoiceID])) {
                        foreach($paymentitems[$invoiceitem->invoiceID] as $paymentitem) {
                            if(isset($retArray['totalpayment'])) {
                                $retArray['totalpayment'] += $paymentitem->paymentamount;
                            } else {
                                $retArray['totalpayment'] = $paymentitem->paymentamount;
                            }
                        }
                    }

                    if(isset($weaverandfineitems[$invoiceitem->invoiceID]) && customCompute($weaverandfineitems[$invoiceitem->invoiceID])) {
                        foreach($weaverandfineitems[$invoiceitem->invoiceID] as $weaverandfineitem) {
                            if(isset($retArray['totalweaver'])) {
                                $retArray['totalweaver'] += $weaverandfineitem->weaver;
                            } else {
                                $retArray['totalweaver'] = $weaverandfineitem->weaver;
                            }

                            if(isset($retArray['totalfine'])) {
                                $retArray['totalfine'] += $weaverandfineitem->fine;
                            } else {
                                $retArray['totalfine'] = $weaverandfineitem->fine;
                            }
                        }
                    }
                }
            }
        }

        return $retArray;
    }

    private function paymentdue( $maininvoice, $schoolyearID, $studentID = NULL )
    {
        $retArray = [];
        if(customCompute($maininvoice)) {
            if((int)$studentID && $studentID != NULL) {
                $invoiceitems       = pluck_multi_array_key($this->invoice_m->get_order_by_invoice([
                    'studentID'     => $studentID,
                    'maininvoiceID' => $maininvoice->maininvoiceID,
                    'schoolyearID'  => $schoolyearID
                ]), 'obj', 'maininvoiceID', 'invoiceID');
                $paymentitems       = pluck_multi_array($this->payment_m->get_order_by_payment([
                    'schoolyearID'     => $schoolyearID,
                    'paymentamount !=' => NULL
                ]), 'obj', 'invoiceID');
                $weaverandfineitems = pluck_multi_array($this->weaverandfine_m->get_order_by_weaverandfine(['schoolyearID' => $schoolyearID]), 'obj', 'invoiceID');
            } else {
                $invoiceitem        = [];
                $paymentitems       = [];
                $weaverandfineitems = [];
            }

            if(isset($invoiceitems[$maininvoice->maininvoiceID]) && customCompute($invoiceitems[$maininvoice->maininvoiceID])) {
                foreach($invoiceitems[$maininvoice->maininvoiceID] as $invoiceitem) {
                    $amount = $invoiceitem->amount;
                    if($invoiceitem->discount > 0) {
                        $amount = ($invoiceitem->amount - (($invoiceitem->amount / 100) * $invoiceitem->discount));
                    }

                    if(isset($retArray['totalamount'][$invoiceitem->invoiceID])) {
                        $retArray['totalamount'][$invoiceitem->invoiceID] += $invoiceitem->amount;
                    } else {
                        $retArray['totalamount'][$invoiceitem->invoiceID] = $invoiceitem->amount;
                    }

                    if(isset($retArray['totaldiscount'][$invoiceitem->invoiceID])) {
                        $retArray['totaldiscount'][$invoiceitem->invoiceID] += ($invoiceitem->amount / 100) * $invoiceitem->discount;
                    } else {
                        $retArray['totaldiscount'][$invoiceitem->invoiceID] = (($invoiceitem->amount / 100) * $invoiceitem->discount);
                    }

                    if(isset($paymentitems[$invoiceitem->invoiceID]) && customCompute($paymentitems[$invoiceitem->invoiceID])) {
                        foreach($paymentitems[$invoiceitem->invoiceID] as $paymentitem) {
                            if(isset($retArray['totalpayment'][$paymentitem->invoiceID])) {
                                $retArray['totalpayment'][$paymentitem->invoiceID] += $paymentitem->paymentamount;
                            } else {
                                $retArray['totalpayment'][$paymentitem->invoiceID] = $paymentitem->paymentamount;
                            }
                        }
                    }

                    if(isset($weaverandfineitems[$invoiceitem->invoiceID]) && customCompute($weaverandfineitems[$invoiceitem->invoiceID])) {
                        foreach($weaverandfineitems[$invoiceitem->invoiceID] as $weaverandfineitem) {
                            if(isset($retArray['totalweaver'][$weaverandfineitem->invoiceID])) {
                                $retArray['totalweaver'][$weaverandfineitem->invoiceID] += $weaverandfineitem->weaver;
                            } else {
                                $retArray['totalweaver'][$weaverandfineitem->invoiceID] = $weaverandfineitem->weaver;
                            }

                            if(isset($retArray['totalfine'][$weaverandfineitem->invoiceID])) {
                                $retArray['totalfine'][$weaverandfineitem->invoiceID] += $weaverandfineitem->fine;
                            } else {
                                $retArray['totalfine'][$weaverandfineitem->invoiceID] = $weaverandfineitem->fine;
                            }
                        }
                    }
                }
            }
        }

        return $retArray;
    }

    private function globalpayment( $maininvoice, $schoolyearID, $studentID = NULL )
    {
        if(customCompute($maininvoice)) {
            if((int)$studentID && $studentID != NULL) {
                $invoiceitems       = pluck_multi_array_key($this->invoice_m->get_order_by_invoice([
                    'studentID'     => $studentID,
                    'maininvoiceID' => $maininvoice->maininvoiceID,
                    'schoolyearID'  => $schoolyearID
                ]), 'obj', 'maininvoiceID', 'invoiceID');
                $paymentitems       = pluck_multi_array($this->payment_m->get_order_by_payment(['schoolyearID' => $schoolyearID]), 'obj', 'invoiceID');
                $weaverandfineitems = pluck_multi_array($this->weaverandfine_m->get_order_by_weaverandfine(['schoolyearID' => $schoolyearID]), 'obj', 'invoiceID');
            } else {
                $invoiceitem        = [];
                $paymentitems       = [];
                $weaverandfineitems = [];
            }

            if(isset($invoiceitems[$maininvoice->maininvoiceID]) && customCompute($invoiceitems[$maininvoice->maininvoiceID])) {
                foreach($invoiceitems[$maininvoice->maininvoiceID] as $invoiceitem) {
                    if(isset($paymentitems[$invoiceitem->invoiceID]) && customCompute($paymentitems[$invoiceitem->invoiceID])) {
                        foreach($paymentitems[$invoiceitem->invoiceID] as $paymentitem) {
                            $retArray['globalpayments'][$paymentitem->globalpaymentID][$paymentitem->paymentID] = [
                                'paymentID'     => $paymentitem->paymentID,
                                'invoiceID'     => $paymentitem->invoiceID,
                                'paymentamount' => $paymentitem->paymentamount,
                                'paymentdate'   => $paymentitem->paymentdate,
                                'weaver'        => '',
                                'fine'          => '',
                            ];
                        }
                    }

                    if(isset($weaverandfineitems[$invoiceitem->invoiceID]) && customCompute($weaverandfineitems[$invoiceitem->invoiceID])) {
                        foreach($weaverandfineitems[$invoiceitem->invoiceID] as $weaverandfineitem) {
                            $retArray['globalpayments'][$weaverandfineitem->globalpaymentID][$weaverandfineitem->paymentID]['weaver'] = $weaverandfineitem->weaver;

                            $retArray['globalpayments'][$weaverandfineitem->globalpaymentID][$weaverandfineitem->paymentID]['fine'] = $weaverandfineitem->fine;
                        }
                    }
                }
            }
        }

        return $retArray;
    }

    public function paymentlist()
    {
        if(permissionChecker('invoice_view')) {
            $schoolyearID  = $this->session->userdata('defaultschoolyearID');
            $maininvoiceID = $this->input->post('maininvoiceID');

            $globalPaymentArray   = [];
            $globalpaymentobjects = [];
            $allpayments          = [];
            $allweaverandfines    = [];
            $paymentlists         = [];

            if(!empty($maininvoiceID) && (int)$maininvoiceID && $maininvoiceID > 0) {
                $maininvoice = $this->maininvoice_m->get_single_maininvoice([
                    'maininvoiceID'           => $maininvoiceID,
                    'maininvoiceschoolyearID' => $schoolyearID
                ]);
                if(customCompute($maininvoice)) {
                    $invoices       = $this->invoice_m->get_order_by_invoice([
                        'maininvoiceID' => $maininvoiceID,
                        'schoolyearID'  => $schoolyearID
                    ]);
                    $globalpayments = pluck($this->globalpayment_m->get_order_by_globalpayment(['studentID' => $maininvoice->maininvoicestudentID]), 'obj', 'globalpaymentID');

                    if(customCompute($invoices)) {
                        foreach($invoices as $invoice) {
                            $payments = $this->payment_m->get_order_by_payment([
                                'invoiceID' => $invoice->invoiceID,
                                'studentID' => $maininvoice->maininvoicestudentID
                            ]);

                            $weaverandfines = $this->weaverandfine_m->get_order_by_weaverandfine([
                                'invoiceID' => $invoice->invoiceID,
                                'studentID' => $maininvoice->maininvoicestudentID
                            ]);
                            if(customCompute($payments)) {
                                foreach($payments as $payment) {
                                    if(isset($globalpayments[$payment->globalpaymentID])) {
                                        $allpayments[$payment->globalpaymentID][] = $payment;
                                        if(!in_array($payment->globalpaymentID, $globalPaymentArray)) {
                                            $globalPaymentArray[]   = $payment->globalpaymentID;
                                            $globalpaymentobjects[] = $globalpayments[$payment->globalpaymentID];
                                        }
                                    }
                                }
                            }

                            if(customCompute($weaverandfines)) {
                                foreach($weaverandfines as $weaverandfine) {
                                    $allweaverandfines[$weaverandfine->globalpaymentID][] = $weaverandfine;
                                }
                            }
                        }
                    }

                    if(customCompute($globalpaymentobjects)) {
                        foreach($globalpaymentobjects as $globalpaymentobject) {
                            if (isset($allpayments[$globalpaymentobject->globalpaymentID]) && customCompute($allpayments[$globalpaymentobject->globalpaymentID])) {
                                foreach($allpayments[$globalpaymentobject->globalpaymentID] as $payment) {
                                    if(isset($paymentlists[$globalpaymentobject->globalpaymentID])) {
                                        $paymentlists[$globalpaymentobject->globalpaymentID]['paymentamount'] += $payment->paymentamount;
                                    } else {
                                        $paymentlists[$globalpaymentobject->globalpaymentID] = [
                                            'globalpaymentID' => $globalpaymentobject->globalpaymentID,
                                            'paymentamount'   => $payment->paymentamount,
                                            'date'            => $payment->paymentdate,
                                            'paymenttype'     => $payment->paymenttype,
                                        ];
                                    }
                                }
                                if(isset($allweaverandfines[$globalpaymentobject->globalpaymentID])) {
                                    foreach($allweaverandfines[$globalpaymentobject->globalpaymentID] as $allweaverandfine) {
                                        if (isset($paymentlists[$globalpaymentobject->globalpaymentID]['weaveramount']) && isset($paymentlists[$globalpaymentobject->globalpaymentID]['fineamount'])) {
                                            $paymentlists[$globalpaymentobject->globalpaymentID]['weaveramount'] += $allweaverandfine->weaver;
                                            $paymentlists[$globalpaymentobject->globalpaymentID]['fineamount']   += $allweaverandfine->fine;
                                        } elseif (isset($paymentlists[$globalpaymentobject->globalpaymentID])) {
                                            $paymentlists[$globalpaymentobject->globalpaymentID]['weaveramount'] = $allweaverandfine->weaver;
                                            $paymentlists[$globalpaymentobject->globalpaymentID]['fineamount']   = $allweaverandfine->fine;
                                        } else {
                                            $paymentlists[$globalpaymentobject->globalpaymentID] = [
                                                'weaveramount' => $allweaverandfine->weaver,
                                                'fineamount'   => $allweaverandfine->fine,
                                            ];
                                        }
                                    }
                                } else {
                                    $paymentlists[$globalpaymentobject->globalpaymentID]['weaveramount'] = 0;
                                    $paymentlists[$globalpaymentobject->globalpaymentID]['fineamount']   = 0;
                                }
                            }
                        }
                    }
                }

                if(customCompute($paymentlists)) {
                    $i = 1;
                    foreach($paymentlists as $key => $paymentlist) {
                        echo '<tr>';
                        echo '<td data-title="' . $this->lang->line('slno') . '">';
                        echo $i;
                        echo '</td>';

                        echo '<td data-title="' . $this->lang->line('invoice_date') . '">';
                        echo date('d M Y', strtotime((string) $paymentlist['date']));
                        echo '</td>';

                        echo '<td data-title="' . $this->lang->line('invoice_paymentmethod') . '">';
                        echo $paymentlist['paymenttype'];
                        echo '</td>';

                        echo '<td data-title="' . $this->lang->line('invoice_paymentamount') . '">';
                        echo number_format($paymentlist['paymentamount'], 2);
                        echo '</td>';

                        echo '<td data-title="' . $this->lang->line('invoice_weaver') . '">';
                        echo number_format($paymentlist['weaveramount'], 2);
                        echo '</td>';

                        echo '<td data-title="' . $this->lang->line('invoice_fine') . '">';
                        echo number_format($paymentlist['fineamount'], 2);
                        echo '</td>';
                        echo '<td data-title="' . $this->lang->line('action') . '">';
                        if(permissionChecker('invoice_view')) {
                            echo '<a href="' . base_url('invoice/viewpayment/' . $paymentlist['globalpaymentID'] . '/' . $maininvoiceID) . '" class="btn btn-success btn-xs mrg" data-placement="top" data-toggle="tooltip" data-original-title="' . $this->lang->line('view') . '"><i class="fa fa-check-square-o"></i></a>';
                        }

                        if((($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || $this->session->userdata('usertypeID') == 5) && (($this->lang->line('Cash') == $paymentlist['paymenttype']) || ($this->lang->line('Cheque') == $paymentlist['paymenttype']) || ('Cash' == $paymentlist['paymenttype']) || ('Cheque' == $paymentlist['paymenttype'])) && permissionChecker('invoice_delete')) {
                            echo '<a href="' . base_url('invoice/deleteinvoicepaid/' . $paymentlist['globalpaymentID'] . '/' . $maininvoiceID) . '" onclick="return confirm(' . "'" . 'you are about to delete a record. This cannot be undone. are you sure?' . "'" . ')" class="btn btn-danger btn-xs mrg" data-placement="top" data-toggle="tooltip" data-original-title="' . $this->lang->line('delete') . '"><i class="fa fa-trash-o"></i></a>';
                        }
                        echo '</td>';
                        echo '</tr>';
                        $i++;
                    }
                }
            }

        }
    }

    public function deleteinvoicepaid()
    {
        if(($this->data['siteinfos']->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1) || ($this->session->userdata('usertypeID') == 5)) {
            $globalpaymentID = htmlentities((string) escapeString($this->uri->segment(3)));
            $maininvoiceID   = htmlentities((string) escapeString($this->uri->segment(4)));
            $schoolyearID    = $this->session->userdata('defaultschoolyearID');

            $paymentArray       = [];
            $weaverandfineArray = [];
            if(permissionChecker('invoice_delete')) {
                if((int)$globalpaymentID && (int)$maininvoiceID) {
                    $globalpayment = $this->globalpayment_m->get_single_globalpayment(['globalpaymentID' => $globalpaymentID]);
                    if(customCompute($globalpayment)) {
                        $payments       = $this->payment_m->get_order_by_payment(['globalpaymentID' => $globalpaymentID]);
                        $weaverandfines = pluck($this->weaverandfine_m->get_order_by_weaverandfine(['globalpaymentID' => $globalpaymentID]), 'obj', 'paymentID');

                        $excType = TRUE;
                        foreach($payments as $payment) {
                            if(($this->lang->line('Cash') == $payment->paymenttype) || ($this->lang->line('Cheque') == $payment->paymenttype) || ('Cash' == $payment->paymenttype) || ('Cheque' == $payment->paymenttype)) {
                                $paymentArray[] = $payment->paymentID;
                                if(isset($weaverandfines[$payment->paymentID])) {
                                    $weaverandfineArray[] = $weaverandfines[$payment->paymentID]->weaverandfineID;
                                }
                            } else {
                                $excType               = FALSE;
                                $this->data["subview"] = "error";
                                $this->load->view('_layout_main', $this->data);
                                break;
                            }
                        }

                        if($excType) {
                            $this->payment_m->delete_batch_payment($paymentArray);
                            $this->weaverandfine_m->delete_batch_weaverandfine($weaverandfineArray);
                            $this->globalpayment_m->delete_globalpayment($globalpaymentID);


                            $invoices     = $this->invoice_m->get_order_by_invoice(['maininvoiceID' => $maininvoiceID]);
                            $invoicepluck = pluck($invoices, 'invoiceID');

                            $invoicesum       = $this->invoice_m->get_invoice_sum(['maininvoiceID' => $maininvoiceID]);
                            $paymentsum       = $this->payment_m->get_where_payment_sum('paymentamount', 'invoiceID', $invoicepluck);
                            $weaverandfinesum = $this->weaverandfine_m->get_where_weaverandfine_sum([
                                'weaver',
                                'fine'
                            ], 'invoiceID', $invoicepluck);

                            $maininvoiceArray = [];
                            if(($paymentsum->paymentamount + $weaverandfinesum->weaver) == NULL) {
                                $maininvoiceArray['maininvoicestatus'] = 0;
                            } elseif((float)($paymentsum->paymentamount + $weaverandfinesum->weaver) == (float)0) {
                                $maininvoiceArray['maininvoicestatus'] = 0;
                            } elseif((float)$invoicesum->invoiceamount === (float)($paymentsum->paymentamount + $weaverandfinesum->weaver)) {
                                $maininvoiceArray['maininvoicestatus'] = 2;
                            } elseif((float)($paymentsum->paymentamount + $weaverandfinesum->weaver) > 0 && ((float)$invoicesum->invoiceamount > (float)($paymentsum->paymentamount + $weaverandfinesum->weaver))) {
                                $maininvoiceArray['maininvoicestatus'] = 1;
                            } elseif((float)($paymentsum->paymentamount + $weaverandfinesum->weaver) > 0 && ((float)$invoicesum->invoiceamount < (float)($paymentsum->paymentamount + $weaverandfinesum->weaver))) {
                                $maininvoiceArray['maininvoicestatus'] = 2;
                            }

                            $payments       = pluck($this->payment_m->get_where_payment_sum('paymentamount', 'invoiceID', $invoicepluck, 'invoiceID'), 'obj', 'invoiceID');
                            $weaverandfines = pluck($this->weaverandfine_m->get_where_weaverandfine_sum([
                                'weaver',
                                'fine'
                            ], 'invoiceID', $invoicepluck, 'invoiceID'), 'obj', 'invoiceID');

                            $invoiceArray = [];
                            if(customCompute($invoices)) {
                                foreach($invoices as $invoice) {
                                    $paymentandweaver = 0;
                                    $paidstatus       = 0;
                                    if(isset($payments[$invoice->invoiceID])) {
                                        $paymentandweaver += $payments[$invoice->invoiceID]->paymentamount;
                                    }

                                    if(isset($weaverandfines[$invoice->invoiceID])) {
                                        $paymentandweaver += $weaverandfines[$invoice->invoiceID]->weaver;
                                    }

                                    if($paymentandweaver == NULL) {
                                        $paidstatus = 0;
                                    } elseif((float)$paymentandweaver == (float)0) {
                                        $paidstatus = 0;
                                    } elseif((float)$invoice->amount === (float)$paymentandweaver) {
                                        $paidstatus = 2;
                                    } elseif((float)$paymentandweaver > 0 && ((float)$invoice->amount > (float)$paymentandweaver)) {
                                        $paidstatus = 1;
                                    } elseif((float)$paymentandweaver > 0 && ((float)$invoice->amount < (float)$paymentandweaver)) {
                                        $paidstatus = 2;
                                    }

                                    $invoiceArray[] = [
                                        'paidstatus' => $paidstatus,
                                        'invoiceID'  => $invoice->invoiceID
                                    ];
                                }
                            }

                            if(customCompute($invoiceArray)) {
                                $this->invoice_m->update_batch_invoice($invoiceArray, 'invoiceID');
                            }
                            $this->maininvoice_m->update_maininvoice($maininvoiceArray, $maininvoiceID);

                            redirect(base_url('invoice/index'));
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
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function payment_methods( $payment_gateways )
    {
        $payment_methods['select'] = $this->lang->line("invoice_select_paymentmethod");
        if($this->session->userdata('usertypeID') == 1 || $this->session->userdata('usertypeID') == 5) {
            $payment_methods['Cash']   = $this->lang->line('Cash');
            $payment_methods['Cheque'] = $this->lang->line('Cheque');
        }

        if(customCompute($payment_gateways)) {
            $online_gateway  = pluck($payment_gateways, 'name', 'slug');
            $payment_methods = array_merge($payment_methods, $online_gateway);
        }

        return $payment_methods;
    }

    public function success()
    {
        if(isset($this->payment_gateway_array[htmlentities((string) escapeString($this->uri->segment(3)))])) {
            $this->payment_gateway->gateway(htmlentities((string) escapeString($this->uri->segment(3))))->success();
        }
    }

    public function cancel()
    {
        if(isset($this->payment_gateway_array[htmlentities((string) escapeString($this->uri->segment(3)))])) {
            $this->payment_gateway->gateway(htmlentities((string) escapeString($this->uri->segment(3))))->cancel();
        }
    }

    public function fail()
    {
        if(isset($this->payment_gateway_array[htmlentities((string) escapeString($this->uri->segment(3)))])) {
            $this->payment_gateway->gateway(htmlentities((string) escapeString($this->uri->segment(3))))->fail();
        }
    }

    public function weaver()
    {
        if(isset($this->payment_gateway_array[htmlentities((string) escapeString($this->uri->segment(3)))])) {
            $this->payment_gateway->gateway(htmlentities((string) escapeString($this->uri->segment(3))))->weaver();
        }
    }

}
