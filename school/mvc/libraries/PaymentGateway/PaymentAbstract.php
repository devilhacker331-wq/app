<?php

abstract class PaymentAbstract
{
    public $ci;
    public $setting;
    public $gateway;
    public $response;
    public $weaver_url;
    public $payment_setting_option;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model('setting_m');
        $this->ci->load->model('payment_gateway_m');
        $this->ci->load->model('payment_gateway_option_m');
        $this->weaver_url = base_url('invoice/weaver');
        $this->setting                = $this->ci->setting_m->get_setting();
        $this->payment_setting_option = pluck($this->ci->payment_gateway_option_m->get_payment_gateway_option(), 'payment_value', 'payment_option');
    }

    abstract public function rules();

    abstract public function payment_rules();

    abstract public function status();

    abstract public function payment( $array, $invoice );

    abstract public function success();

    abstract public function fail();

    abstract public function cancel();
}