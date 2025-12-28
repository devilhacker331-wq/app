<?php

require_once(dirname(__FILE__, 2) . '/PaymentAbstract.php');
require_once(dirname(__FILE__, 2) . '/Service/PaymentService.php');
require_once(dirname(__FILE__, 2) . '/Service/BalanceService.php');
require_once(FCPATH . 'vendor/autoload.php');

use Omnipay\Omnipay;

class Paypal extends PaymentAbstract
{
    public $url;
    public $params;
    /**
     * @var \BalanceService
     */
    public $balanceService;
    public $main_invoice_id;

    public function __construct()
    {
        parent::__construct();
        $this->ci =& get_instance();
        $this->ci->lang->load('paypal_rules', $this->ci->session->userdata('lang'));
        $this->url            = base_url("invoice/index");
        $this->balanceService = new BalanceService();
        $this->gateway        = Omnipay::create('PayPal_Express');
        $this->gateway->setUsername($this->payment_setting_option['paypal_username']);
        $this->gateway->setPassword($this->payment_setting_option['paypal_password']);
        $this->gateway->setSignature($this->payment_setting_option['paypal_signature']);
        $this->gateway->setTestMode((bool)$this->payment_setting_option['paypal_demo']);
    }

    public function rules()
    {
        return [
            [
                'field' => 'payment_type',
                'label' => $this->ci->lang->line("paypal_payment_type"),
                'rules' => 'trim|required'
            ],
            [
                'field' => 'paypal_username',
                'label' => $this->ci->lang->line("paypal_username"),
                'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
            ],
            [
                'field' => 'paypal_password',
                'label' => $this->ci->lang->line("paypal_password"),
                'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
            ],
            [
                'field' => 'paypal_signature',
                'label' => $this->ci->lang->line("paypal_signature"),
                'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
            ],
            [
                'field' => 'paypal_email',
                'label' => $this->ci->lang->line("paypal_email"),
                'rules' => 'trim|xss_clean|max_length[255]|valid_email|callback_unique_field'
            ],
            [
                'field' => 'paypal_currency',
                'label' => $this->ci->lang->line("paypal_currency"),
                'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
            ],
            [
                'field' => 'paypal_demo',
                'label' => $this->ci->lang->line("paypal_demo"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ],
            [
                'field' => 'paypal_status',
                'label' => $this->ci->lang->line("paypal_status"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ]
        ];
    }

    public function payment_rules()
    {
        return [];
    }

    public function status()
    {
        $paypal_status = $this->ci->payment_gateway_m->get_single_payment_gateway(['slug' => 'paypal', 'status' => 1]);
        return is_object($paypal_status);
    }

    public function cancel()
    {
        redirect($this->url);
    }

    public function fail()
    {
        redirect($this->url);
    }

    public function payment( $array, $invoice )
    {
        $this->main_invoice_id = $invoice->maininvoiceID;
        $this->url             = base_url("invoice/payment/" . $this->main_invoice_id);
        $response              = $this->balanceService->result($array, $invoice->maininvoiceID);
        $this->params          = [
            'cancelUrl'       => base_url('invoice/cancel/paypal'),
            'returnUrl'       => base_url('invoice/success/paypal'),
            'main_invoice_id' => $invoice->maininvoiceID,
            'name'            => $invoice->maininvoiceuname,
            'description'     => $response->fee_type,
            'amount'          => (float) $response->total_amount,
            'currency'        => $this->payment_setting_option['paypal_currency'],
            'payment'         => $array
        ];
        $this->ci->session->set_userdata("params", $this->params);

        if((float)($response->total_amount) == (float)0) {
            redirect($this->weaver_url .'/paypal');
        } else {
            $this->response = $this->gateway->purchase($this->params)->send();
            if($this->response->isSuccessful()) {
            } elseif($this->response->isRedirect()) {
                $this->response->redirect();
            } else {
                echo $this->response->getMessage();
            }
        }
    }

    public function success()
    {
        $params         = $this->ci->session->userdata('params');
        $this->url      = base_url("invoice/payment" . $params['main_invoice_id']);
        $this->response = $this->gateway->completePurchase($params)->send();
        $this->response = $this->response->getData();
        $purchase_id    = $_GET['PayerID'];
        if(isset($this->response['PAYMENTINFO_0_ACK']) && $this->response['PAYMENTINFO_0_ACK'] === 'Success') {
            if($purchase_id) {
                $transaction_id = $this->response['PAYMENTINFO_0_TRANSACTIONID'];
                $paymentService = new PaymentService($transaction_id);
                $payment_id     = $paymentService->add_transaction([
                    'payment'         => $params['payment'],
                    'main_invoice_id' => $params['main_invoice_id'],
                    'amount'          => $params['amount'],
                    'payment_method'  => 'paypal'
                ]);

                if($payment_id) {
                    redirect(base_url("invoice/viewpayment/" . $payment_id . '/' . $params['main_invoice_id']));
                } else {
                    $this->fail();
                }
            } else {
                $this->ci->session->set_flashdata('error', 'Payer id not found!');
                redirect($this->url);
            }
        } else {
            $this->ci->session->set_flashdata('error', 'Payment not success!');
            redirect($this->url);
        }
    }

    public function weaver()
    {
        $transaction_id = 'PAYPALWEAVER'. random19() . date('ymdhis');
        $params         = $this->ci->session->userdata('params');
        $paymentService = new PaymentService($transaction_id);
        $payment_id     = $paymentService->add_transaction([
            'payment'         => $params['payment'],
            'main_invoice_id' => $params['main_invoice_id'],
            'payment_method'  => 'paypal'
        ]);

        if($payment_id) {
            redirect(base_url("invoice/viewpayment/" . $payment_id . '/' . $params['main_invoice_id']));
        } else {
            $this->fail();
        }
    }
}