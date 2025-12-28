<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') || exit('No direct script access allowed');

class Invoice extends Api_Controller 
{
    public $load;
    public $session;
    public $student_m;
    public $retdata;
    public $studentrelation_m;
    public $invoice_m;
    public $maininvoice_m;
    public $globalpayment_m;
    public $payment_m;
    public $weaverandfine_m;
    public function __construct() 
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
    }

    public function index_get() 
    {
        $usertypeID = $this->session->userdata("usertypeID");
        $schoolyearID = $this->session->userdata("defaultschoolyearID");
        if($usertypeID == 3) {
            $username = $this->session->userdata("username");
            $student  = $this->student_m->get_single_student(array("username" => $username));
            if(customCompute($student)) {
                $this->retdata['maininvoices'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_studentID($student->studentID, $schoolyearID);
                $this->retdata['grandtotalandpayment'] = $this->grandtotalandpaid($this->retdata['maininvoices'], $schoolyearID);

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
        } elseif($usertypeID == 4) {
            $parentID = $this->session->userdata("loginuserID");
            $students = $this->studentrelation_m->get_order_by_student(array('parentID' => $parentID, 'srschoolyearID' => $schoolyearID));
            if(customCompute($students)) {
                $studentArray = pluck($students, 'srstudentID');
                $this->retdata['maininvoices'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_multi_studentID($studentArray, $schoolyearID);
                $this->retdata['grandtotalandpayment'] = $this->grandtotalandpaid($this->retdata['maininvoices'], $schoolyearID);
                
                $this->response([
                    'status'    => true,
                    'message'   => 'Success',
                    'data'      => $this->retdata
                ], REST_Controller::HTTP_OK);
            } else {
                $this->retdata['maininvoices'] = [];
                $this->retdata['grandtotalandpayment'] = [];

                $this->response([
                    'status'    => true,
                    'message'   => 'Success',
                    'data'      => $this->retdata
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $this->retdata['maininvoices'] = $this->maininvoice_m->get_maininvoice_with_studentrelation($schoolyearID);
            $this->retdata['grandtotalandpayment'] = $this->grandtotalandpaid($this->retdata['maininvoices'], $schoolyearID);
            
            $this->response([
                'status'    => true,
                'message'   => 'Success',
                'data'      => $this->retdata
            ], REST_Controller::HTTP_OK);
        }
    }

    public function view_get($id = null) 
    {
        $usertypeID = $this->session->userdata("usertypeID");
        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $this->retdata['feetypes'] = pluck($this->feetypes_m->get_feetypes(), 'feetypes', 'feetypesID');
        $this->retdata["siteinfos"] = $this->data['siteinfos'];

        if($usertypeID == 3) {
            if((int)$id !== 0) {
                $studentID  = $this->session->userdata("loginuserID");
                $getstudent = $this->studentrelation_m->get_single_student(array("srstudentID" => $studentID, 'srschoolyearID' => $schoolyearID));
                if(customCompute($getstudent)) {
                    $this->retdata['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID($id, $schoolyearID);
                    if(customCompute($this->retdata['maininvoice']) && ($this->retdata['maininvoice']->maininvoicestudentID == $getstudent->studentID)) {
                        $invoices = $this->invoice_m->get_order_by_invoice(array('maininvoiceID' => $id));
                        if(customCompute($invoices)) {
                            foreach ($invoices as $key=> $invoice) {
                                $invoices[$key]->dicountamount = (float)(($invoice->discount*$invoice->amount) / 100);
                                $invoices[$key]->subtotal      = (int)$invoice->amount - $invoices[$key]->dicountamount;
                            }
                        }
                        $this->retdata['invoices'] = $invoices;

                        $this->retdata['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->retdata['maininvoice'], $schoolyearID, $this->retdata["maininvoice"]->maininvoicestudentID);

                        $this->retdata["student"] = $this->student_m->get_single_student(array('studentID' => $this->retdata["maininvoice"]->maininvoicestudentID));

                        $this->retdata['createuser'] = getNameByUsertypeIDAndUserID($this->retdata['maininvoice']->maininvoiceusertypeID, $this->retdata['maininvoice']->maininvoiceuserID);

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
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Error 404',
                    'data' => []
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } elseif($usertypeID == 4) {
            if((int)$id !== 0) {
                $parentID = $this->session->userdata("loginuserID");
                $getStudents = $this->studentrelation_m->get_order_by_student(array('parentID' => $parentID, 'srschoolyearID' => $schoolyearID));
                $fetchStudent = pluck($getStudents, 'srstudentID', 'srstudentID');
                if(customCompute($fetchStudent)) {
                    $this->retdata['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID($id, $schoolyearID);
                    if($this->retdata['maininvoice']) {
                        if(in_array($this->retdata['maininvoice']->maininvoicestudentID, $fetchStudent)) {

                            $invoices = $this->invoice_m->get_order_by_invoice(array('maininvoiceID' => $id));
                            if(customCompute($invoices)) {
                                foreach ($invoices as $key=> $invoice) {
                                    $invoices[$key]->dicountamount = (float)(($invoice->discount*$invoice->amount) / 100);
                                    $invoices[$key]->subtotal      = (int)$invoice->amount - $invoices[$key]->dicountamount;
                                }
                            }
                            $this->retdata['invoices'] = $invoices;

                            $this->retdata['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->retdata['maininvoice'], $schoolyearID, $this->retdata["maininvoice"]->maininvoicestudentID);

                            $this->retdata["student"] = $this->student_m->get_single_student(array('studentID' => $this->retdata["maininvoice"]->maininvoicestudentID));

                            $this->retdata['createuser'] = getNameByUsertypeIDAndUserID($this->retdata['maininvoice']->maininvoiceusertypeID, $this->retdata['maininvoice']->maininvoiceuserID);

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
        } elseif ((int)$id !== 0) {
            $this->retdata['maininvoice'] = $this->maininvoice_m->get_maininvoice_with_studentrelation_by_maininvoiceID($id, $schoolyearID);
            $invoices = $this->invoice_m->get_order_by_invoice(array('maininvoiceID' => $id));
            if(customCompute($invoices)) {
                foreach ($invoices as $key=> $invoice) {
                    $invoices[$key]->dicountamount = (float)(($invoice->discount*$invoice->amount) / 100);
                    $invoices[$key]->subtotal      = (int)$invoice->amount - $invoices[$key]->dicountamount;
                }
            }
            $this->retdata['invoices'] = $invoices;
            if(customCompute($this->retdata["maininvoice"])) {
                $this->retdata['grandtotalandpayment'] = $this->grandtotalandpaidsingle($this->retdata['maininvoice'], $schoolyearID, $this->retdata["maininvoice"]->maininvoicestudentID);

                $this->retdata["student"] = $this->student_m->get_single_student(array('studentID' => $this->retdata["maininvoice"]->maininvoicestudentID));

                $this->retdata['createuser'] = getNameByUsertypeIDAndUserID($this->retdata['maininvoice']->maininvoiceusertypeID, $this->retdata['maininvoice']->maininvoiceuserID);

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

    public function paymentlist_get($maininvoiceID = null) 
    {
        if(permissionChecker('invoice_view')) {
            $schoolyearID = $this->session->userdata('defaultschoolyearID');

            $globalPaymentArray = [];
            $globalpaymentobjects = [];
            $allpayments = [];
            $allweaverandfines = [];
            $paymentlists = [];

            if(!empty($maininvoiceID) && (int)$maininvoiceID && $maininvoiceID > 0) {
                $maininvoice = $this->maininvoice_m->get_single_maininvoice(array('maininvoiceID' => $maininvoiceID, 'maininvoiceschoolyearID' => $schoolyearID));
                if(customCompute($maininvoice)) {
                    $invoices = $this->invoice_m->get_order_by_invoice(array('maininvoiceID' => $maininvoiceID, 'schoolyearID' => $schoolyearID));
                    $globalpayments = pluck($this->globalpayment_m->get_order_by_globalpayment(array('studentID' => $maininvoice->maininvoicestudentID)), 'obj', 'globalpaymentID');

                    if(customCompute($invoices)) {
                        foreach ($invoices as $invoice) {
                            $payments = $this->payment_m->get_order_by_payment(array('invoiceID' => $invoice->invoiceID, 'studentID' => $maininvoice->maininvoicestudentID));

                            $weaverandfines = $this->weaverandfine_m->get_order_by_weaverandfine(array('invoiceID' => $invoice->invoiceID, 'studentID' => $maininvoice->maininvoicestudentID));
                            if(customCompute($payments)) {
                                foreach ($payments as $payment) {
                                    if(isset($globalpayments[$payment->globalpaymentID])) {
                                        $allpayments[$payment->globalpaymentID][] = $payment;
                                        if(!in_array($payment->globalpaymentID, $globalPaymentArray)) {
                                            $globalPaymentArray[] = $payment->globalpaymentID;
                                            $globalpaymentobjects[] = $globalpayments[$payment->globalpaymentID];
                                        }
                                    }
                                }
                            }

                            if(customCompute($weaverandfines)) {
                                foreach ($weaverandfines as $weaverandfine) {
                                    $allweaverandfines[$weaverandfine->globalpaymentID][] = $weaverandfine;
                                }
                            }
                        }
                    }

                    if(customCompute($globalpaymentobjects)) {
                        foreach ($globalpaymentobjects as $globalpaymentobject) {
                            if (isset($allpayments[$globalpaymentobject->globalpaymentID]) && customCompute($allpayments[$globalpaymentobject->globalpaymentID])) {
                                foreach ($allpayments[$globalpaymentobject->globalpaymentID] as $payment) {
                                    if(isset($paymentlists[$globalpaymentobject->globalpaymentID])) {
                                        $paymentlists[$globalpaymentobject->globalpaymentID]['paymentamount'] += $payment->paymentamount;
                                    } else {
                                        $paymentlists[$globalpaymentobject->globalpaymentID] = array(
                                            'globalpaymentID' => $globalpaymentobject->globalpaymentID,
                                            'paymentamount' => $payment->paymentamount,
                                            'date' => $payment->paymentdate,
                                            'paymenttype' => $payment->paymenttype,
                                        );
                                    }
                                }
                                if(isset($allweaverandfines[$globalpaymentobject->globalpaymentID])) {
                                    foreach ($allweaverandfines[$globalpaymentobject->globalpaymentID] as $allweaverandfine) {
                                        if (isset($paymentlists[$globalpaymentobject->globalpaymentID]['weaveramount']) && isset($paymentlists[$globalpaymentobject->globalpaymentID]['fineamount'])) {
                                            $paymentlists[$globalpaymentobject->globalpaymentID]['weaveramount'] += $allweaverandfine->weaver;
                                            $paymentlists[$globalpaymentobject->globalpaymentID]['fineamount'] += $allweaverandfine->fine;
                                        } elseif (isset($paymentlists[$globalpaymentobject->globalpaymentID])) {
                                            $paymentlists[$globalpaymentobject->globalpaymentID]['weaveramount'] = $allweaverandfine->weaver;
                                            $paymentlists[$globalpaymentobject->globalpaymentID]['fineamount'] = $allweaverandfine->fine;
                                        } else {
                                            $paymentlists[$globalpaymentobject->globalpaymentID] = array(
                                                'weaveramount' => $allweaverandfine->weaver,
                                                'fineamount' => $allweaverandfine->fine,
                                            );
                                        }
                                    }
                                } else {
                                    $paymentlists[$globalpaymentobject->globalpaymentID]['weaveramount'] = 0;
                                    $paymentlists[$globalpaymentobject->globalpaymentID]['fineamount'] = 0;
                                }
                            }
                        }
                    }
                }

                $this->retdata['paymentlists'] = $paymentlists;
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
                'message' => 'Permission deny',
                'data' => []
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function viewpayment_get($globalpaymentID = null, $maininvoiceID = null) 
    {
        if(permissionChecker('invoice_view')) {
            $schoolyearID = $this->session->userdata('defaultschoolyearID');
            if((int)$globalpaymentID && (int)$maininvoiceID) {
                $globalpayment = $this->globalpayment_m->get_single_globalpayment(array('globalpaymentID' => $globalpaymentID, 'schoolyearID' => $schoolyearID));
                $maininvoice = $this->maininvoice_m->get_single_maininvoice(array('maininvoiceID' => $maininvoiceID, 'maininvoiceschoolyearID' => $schoolyearID));
                if(customCompute($maininvoice) && customCompute($globalpayment)) {
                    $usertypeID = $this->session->userdata('usertypeID');
                    $userID = $this->session->userdata('loginuserID');

                    $f = FALSE;
                    if($usertypeID == 3) {
                        $getstudent = $this->studentrelation_m->get_single_studentrelation(array('srstudentID' => $globalpayment->studentID, 'srschoolyearID' => $globalpayment->schoolyearID));
                        if(customCompute($getstudent) && $getstudent->srstudentID == $userID) {
                            $f = TRUE;
                        }
                    } elseif($usertypeID == 4) {
                        $parentID = $this->session->userdata("loginuserID");
                        $schoolyearID = $this->session->userdata('defaultschoolyearID');
                        $getStudents = $this->studentrelation_m->get_order_by_student(array('parentID' => $parentID, 'srschoolyearID' => $schoolyearID));
                        $fetchStudent = pluck($getStudents, 'srstudentID', 'srstudentID');
                        if(customCompute($fetchStudent) && in_array($globalpayment->studentID, $fetchStudent)) {
                            $f = TRUE;
                        }
                    } else {
                        $f = TRUE;
                    }

                    if($f) {
                        $studentrelation = $this->studentrelation_m->get_single_studentrelation(array('srstudentID' => $globalpayment->studentID, 'srschoolyearID' => $globalpayment->schoolyearID));
                        if(customCompute($studentrelation)) {
                            $this->retdata['feetypes'] = pluck($this->feetypes_m->get_feetypes(), 'feetypes', 'feetypesID');
                            $this->retdata['student'] = $this->student_m->get_single_student(array('studentID' => $globalpayment->studentID));
                            $this->retdata['invoices'] = pluck($this->invoice_m->get_order_by_invoice(array('maininvoiceID' => $maininvoiceID)), 'obj', 'invoiceID');

                            $this->payment_m->order_payment('paymentID', 'asc');
                            $this->retdata['payments'] = $this->payment_m->get_order_by_payment(array('globalpaymentID' => $globalpaymentID));
                            $this->retdata['weaverandfines'] = pluck($this->weaverandfine_m->get_order_by_weaverandfine(array('globalpaymentID' => $globalpaymentID)), 'obj', 'paymentID');

                            $this->retdata['paymenttype'] = '';
                            if(customCompute($this->retdata['payments'])) {
                                foreach ($this->retdata['payments'] as $payment) {
                                    $this->retdata['paymenttype'] = $payment->paymenttype;
                                    break;
                                }
                            }

                            $this->retdata['studentrelation'] = $studentrelation;
                            $this->retdata['globalpayment'] = $globalpayment;
                            $this->retdata['maininvoice'] = $maininvoice;

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
        } else {
            $this->response([
                'status' => false,
                'message' => 'Permission deny',
                'data' => []
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    private function grandtotalandpaid($maininvoices, $schoolyearID) 
    {
        $retArray = [];
        $invoiceitems = pluck_multi_array_key($this->invoice_m->get_order_by_invoice(array('schoolyearID' => $schoolyearID)), 'obj', 'maininvoiceID', 'invoiceID');
        $paymentitems = pluck_multi_array($this->payment_m->get_order_by_payment(array('schoolyearID' => $schoolyearID, 'paymentamount !=' => NULL)), 'obj', 'invoiceID');
        $weaverandfineitems = pluck_multi_array($this->weaverandfine_m->get_order_by_weaverandfine(array('schoolyearID' => $schoolyearID)), 'obj', 'invoiceID');
        if(customCompute($maininvoices)) {
            foreach ($maininvoices as $maininvoice) {
                if(isset($invoiceitems[$maininvoice->maininvoiceID]) && customCompute($invoiceitems[$maininvoice->maininvoiceID])) {
                    foreach ($invoiceitems[$maininvoice->maininvoiceID] as $invoiceitem) {
                        $amount = $invoiceitem->amount;
                        if($invoiceitem->discount > 0) {
                            $amount = ($invoiceitem->amount - (($invoiceitem->amount/100) *$invoiceitem->discount));
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
                            $retArray['totaldiscount'][$maininvoice->maininvoiceID] += ($invoiceitem->amount/100) *$invoiceitem->discount;
                        } else {
                            $retArray['totaldiscount'][$maininvoice->maininvoiceID] = (($invoiceitem->amount/100) *$invoiceitem->discount);
                        }

                        if(isset($paymentitems[$invoiceitem->invoiceID]) && customCompute($paymentitems[$invoiceitem->invoiceID])) {
                            foreach ($paymentitems[$invoiceitem->invoiceID] as $paymentitem) {
                                if(isset($retArray['totalpayment'][$maininvoice->maininvoiceID])) {
                                    $retArray['totalpayment'][$maininvoice->maininvoiceID] += $paymentitem->paymentamount;
                                } else {
                                    $retArray['totalpayment'][$maininvoice->maininvoiceID] = $paymentitem->paymentamount;
                                }
                            }
                        }

                        if(isset($weaverandfineitems[$invoiceitem->invoiceID]) && customCompute($weaverandfineitems[$invoiceitem->invoiceID])) {
                            foreach ($weaverandfineitems[$invoiceitem->invoiceID] as $weaverandfineitem) {
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

    private function grandtotalandpaidsingle($maininvoice, $schoolyearID, $studentID = null) 
    {
        $retArray = ['grandtotal' => 0, 'totalamount' => 0, 'totaldiscount' => 0, 'totalpayment' => 0, 'totalfine' => 0, 'totalweaver' => 0, 'balanceamount'=>0];
        if(customCompute($maininvoice)) {
            if((int)$studentID && $studentID != null) {
                $invoiceitems = pluck_multi_array_key($this->invoice_m->get_order_by_invoice(array('studentID' => $studentID, 'maininvoiceID' => $maininvoice->maininvoiceID,  'schoolyearID' => $schoolyearID)), 'obj', 'maininvoiceID', 'invoiceID');
                $paymentitems = pluck_multi_array($this->payment_m->get_order_by_payment(array('schoolyearID' => $schoolyearID, 'paymentamount !=' => NULL)), 'obj', 'invoiceID');
                $weaverandfineitems = pluck_multi_array($this->weaverandfine_m->get_order_by_weaverandfine(array('schoolyearID' => $schoolyearID)), 'obj', 'invoiceID');
            } else {
                $invoiceitem = [];
                $paymentitems = [];
                $weaverandfineitems = [];
            }

            if(isset($invoiceitems[$maininvoice->maininvoiceID]) && customCompute($invoiceitems[$maininvoice->maininvoiceID])) {
                foreach ($invoiceitems[$maininvoice->maininvoiceID] as $invoiceitem) {
                    $amount = $invoiceitem->amount;
                    if($invoiceitem->discount > 0) {
                        $amount = ($invoiceitem->amount - (($invoiceitem->amount/100) *$invoiceitem->discount));
                    }

                    $retArray['grandtotal'] = isset($retArray['grandtotal']) ? $retArray['grandtotal'] + $amount : $amount;

                    if(isset($retArray['totalamount'])) {
                        $retArray['totalamount'] += $invoiceitem->amount;
                    } else {
                        $retArray['totalamount'] = $invoiceitem->amount;
                    }

                    if(isset($retArray['totaldiscount'])) {
                        $retArray['totaldiscount'] += ($invoiceitem->amount/100) *$invoiceitem->discount;
                    } else {
                        $retArray['totaldiscount'] = (($invoiceitem->amount/100) *$invoiceitem->discount);
                    }

                    if(isset($paymentitems[$invoiceitem->invoiceID]) && customCompute($paymentitems[$invoiceitem->invoiceID])) {
                        foreach ($paymentitems[$invoiceitem->invoiceID] as $paymentitem) {
                            if(isset($retArray['totalpayment'])) {
                                $retArray['totalpayment'] += $paymentitem->paymentamount;
                            } else {
                                $retArray['totalpayment'] = $paymentitem->paymentamount;
                            }
                        }
                    }

                    if(isset($weaverandfineitems[$invoiceitem->invoiceID]) && customCompute($weaverandfineitems[$invoiceitem->invoiceID])) {
                        foreach ($weaverandfineitems[$invoiceitem->invoiceID] as $weaverandfineitem) {
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

                    $retArray['balanceamount'] = $retArray['grandtotal'] - ($retArray['totalpayment'] + $retArray['totalweaver']);
                }
            }
        }

        return $retArray;
    }

      
}
