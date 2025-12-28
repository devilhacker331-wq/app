<?php

class PaymentGateway
{
    public $gateway;

    public function __construct()
    {
        foreach(scandir(dirname(__FILE__) . '/Gateway') as $filename) {
            $path = dirname(__FILE__) . '/Gateway/' . $filename;
            if(is_file($path)) {
                require($path);
            }
        }
    }

    public function gateway( ...$args ) : object
    {
        $payment_method = '';
        if($args !== []) {
            $payment_method = ucfirst((string) array_shift($args));
        }

        if(count($args) == 0) {
            $args = null;
        }

        $this->gateway = new $payment_method($args);
        return $this;
    }

    public function payment( $post_data, $invoice_data )
    {
        return $this->gateway->payment($post_data, $invoice_data);
    }

    public function status()
    {
        return $this->gateway->status();
    }

    public function rules()
    {
        return $this->gateway->rules();
    }

    public function success()
    {
        return $this->gateway->success();
    }

    public function cancel()
    {
        return $this->gateway->cancel();
    }

    public function fail()
    {
        return $this->gateway->fail();
    }

    public function weaver()
    {
        return $this->gateway->weaver();
    }

    public function payment_rules($rules) : array
    {
        return array_merge($rules, $this->gateway->payment_rules($rules));
    }

}