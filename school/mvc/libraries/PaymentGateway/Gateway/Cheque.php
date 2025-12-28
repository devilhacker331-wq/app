<?php

require_once(dirname(__FILE__, 2) . '/PaymentAbstract.php');

class Cheque extends PaymentAbstract
{
    public $params;
    public $fail_url;
    public $success_url;
    public $main_invoice_id;
    /**
     * @var \BalanceService
     */
    public $balanceService;

    public function __construct()
    {
        parent::__construct();
        $this->balanceService = new BalanceService();
    }

    public function rules()
    {
        return [];
    }

    public function payment_rules()
    {
        return [];
    }

    public function status()
    {
        return true;
    }

    public function cancel()
    {
        redirect($this->fail_url);
    }

    public function fail()
    {
        redirect($this->fail_url);
    }

    public function payment( $array, $invoice )
    {
        $this->fail_url        = base_url("invoice/payment/" . $invoice->maininvoiceID);
        $this->main_invoice_id = $invoice->maininvoiceID;
        $this->params          = [
            'main_invoice_id' => $invoice->maininvoiceID,
            'payment'         => $array
        ];
        $this->ci->session->set_userdata("params", $this->params);
        $this->success();

    }

    public function success()
    {
        $transaction_id = 'CHEQUE' . random19() . date('ymdhis');
        $params         = $this->ci->session->userdata('params');
        $paymentService = new PaymentService($transaction_id);
        $payment_id     = $paymentService->add_transaction([
            'payment'         => $params['payment'],
            'main_invoice_id' => $params['main_invoice_id'],
            'payment_method'  => 'cheque'
        ]);

        if($payment_id) {
            redirect(base_url("invoice/viewpayment/" . $payment_id . '/' . $params['main_invoice_id']));
        } else {
            $this->fail();
        }
    }
}