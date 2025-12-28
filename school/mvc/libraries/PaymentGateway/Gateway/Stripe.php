<?php

require_once(dirname(__FILE__, 2) . '/PaymentAbstract.php');
require_once(dirname(__FILE__, 2) . '/Service/PaymentService.php');
require_once(dirname(__FILE__, 2) . '/Service/BalanceService.php');
require_once(FCPATH . 'vendor/autoload.php');

use Omnipay\Omnipay;

class Stripe extends PaymentAbstract
{
    public $params;
    public $url;
    /**
     * @var \BalanceService
     */
    public $balanceService;
    public $main_invoice_id;

    public function __construct()
    {
        parent::__construct();
        $this->ci->lang->load('stripe_rules', $this->ci->session->userdata('lang'));
        $this->url            = base_url("invoice/view");
        $this->gateway        = Omnipay::create('Stripe');
        $this->balanceService = new BalanceService();
        $this->gateway->setApiKey($this->payment_setting_option['stripe_secret']);
        $this->gateway->setTestMode((bool)$this->payment_setting_option['stripe_demo']);
    }

    public function rules()
    {
        return [
            [
                'field' => 'payment_type',
                'label' => $this->ci->lang->line("stripe_payment_type"),
                'rules' => 'trim|required'
            ],
            [
                'field' => 'stripe_key',
                'label' => $this->ci->lang->line("stripe_key"),
                'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
            ],
            [
                'field' => 'stripe_secret',
                'label' => $this->ci->lang->line("stripe_secret"),
                'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
            ],
            [
                'field' => 'stripe_currency',
                'label' => $this->ci->lang->line("stripe_currency"),
                'rules' => 'trim|xss_clean|max_length[255]|callback_unique_field'
            ],
            [
                'field' => 'stripe_demo',
                'label' => $this->ci->lang->line("stripe_demo"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ],
            [
                'field' => 'stripe_status',
                'label' => $this->ci->lang->line("stripe_status"),
                'rules' => 'trim|xss_clean|max_length[255]'
            ]
        ];
    }

    public function payment_rules()
    {
        return [
            [
                'field' => 'stripeToken',
                'label' => $this->ci->lang->line("stripe_token"),
                'rules' => 'trim|required|xss_clean'
            ]
        ];
    }

    public function status()
    {
        $stripe_status = $this->ci->payment_gateway_m->get_single_payment_gateway(['slug' => 'stripe', 'status' => 1]);
        return is_object($stripe_status);
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
            'main_invoice_id' => $invoice->maininvoiceID,
            'invoice'         => $response->fee_type,
            'amount'          => (float) $response->total_amount,
            'currency'        => $this->payment_setting_option['stripe_currency'],
            'token'           => $array['stripeToken'],
            'payment'         => $array
        ];
        $this->ci->session->set_userdata("params", $this->params);
        if((float)($response->total_amount) == (float)0) {
            redirect($this->weaver_url .'/stripe');
        } else {
            $this->response = $this->gateway->purchase($this->params)->send();
            $this->success();
        }
    }

    public function success()
    {
        $params    = $this->ci->session->userdata('params');
        $this->url = base_url("invoice/payment" . $params['main_invoice_id']);
        if($this->response->isSuccessful()) {
            if($this->response->getData()['status'] === "succeeded") {
                $transaction_id = $this->response->getData()['id'];
                if($transaction_id) {
                    $paymentService = new PaymentService($transaction_id);
                    $payment_id     = $paymentService->add_transaction([
                        'payment'         => $params['payment'],
                        'main_invoice_id' => $params['main_invoice_id'],
                        'amount'          => $params['amount'],
                        'payment_method'  => 'stripe'
                    ]);
                    redirect(base_url("invoice/viewpayment/" . $payment_id . '/' . $params['main_invoice_id']));
                } else {
                    $this->ci->session->set_flashdata('error', 'Payer id not found!');
                    redirect($this->url);
                }
            } else {
                $this->ci->session->set_flashdata('error', 'Payment not success!');
                redirect($this->url);
            }
        } elseif($this->response->isRedirect()) {
            $this->response->redirect();
        } else {
            $this->ci->session->set_flashdata('error', "Something went wrong!");
            redirect($this->url);
        }
    }

    public function weaver()
    {
        $transaction_id = 'STRIPEWEAVER'. random19() . date('ymdhis');
        $params         = $this->ci->session->userdata('params');
        $paymentService = new PaymentService($transaction_id);
        $payment_id     = $paymentService->add_transaction([
            'payment'         => $params['payment'],
            'main_invoice_id' => $params['main_invoice_id'],
            'payment_method'  => 'stripe'
        ]);

        if($payment_id) {
            redirect(base_url("invoice/viewpayment/" . $payment_id . '/' . $params['main_invoice_id']));
        } else {
            $this->fail();
        }
    }
}