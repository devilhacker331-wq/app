<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once(APPPATH . 'libraries/PaymentGateway/PaymentGateway.php');

class Paymentsettings extends Admin_Controller
{
    public $load;
    public $lang;
    public $session;
    public $payment_gateway_option_m;
    public $data;
    public $form_validation;
    public $input;
    public $payment_gateway_m;
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

    public $payment_type;
    /**
     * @var \PaymentGateway
     */
    public $payment_gateway;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('payment_gateway_m');
        $this->load->model('payment_gateway_option_m');
        $this->lang->load('payment_settings', $this->session->userdata('lang'));
        $this->payment_gateway = new PaymentGateway();

        if(config_item('demo')) {
            $this->session->set_flashdata('error', 'In demo payment setting module is disable!');
            redirect(base_url('dashboard/index'));
        }
    }

    public function index()
    {
        $gatewayOptions                        = $this->payment_gateway_option_m->get_payment_gateway_option();
        $this->data['payment_gateways']        = $this->payment_gateway_m->get_payment_gateway();
        $this->data['payment_gateway_options'] = pluck_multi_array($gatewayOptions, 'obj', 'payment_gateway_id');

        if(customCompute($this->data['payment_gateways'])) {
            foreach($this->data['payment_gateways'] as $paymentGateway) {
                $this->lang->load($paymentGateway->slug . '_rules', $this->session->userdata('lang'));
            }
        }

        if(customCompute($this->data['payment_gateways'])) {
            if($_POST !== []) {
                $this->payment_type = $this->input->post('payment_type');
                $rules              = $this->rules();
                $this->form_validation->set_rules($rules);
                if($this->form_validation->run() == false) {
                    $this->data["subview"] = "paymentsettings/index";
                    $this->load->view('_layout_main', $this->data);
                } else {
                    $array              = [];
                    $j                  = 0;
                    $payment_gateway_id = 0;
                    $gateway_options    = pluck($gatewayOptions, 'obj', 'payment_option');
                    for($i = 0; $i < customCompute($rules); $i++) {
                        $key = $rules[$i]['field'];
                        if(isset($gateway_options[$key])) {
                            $array[$j]['id']                 = $gateway_options[$key]->id;
                            $array[$j]['payment_gateway_id'] = $gateway_options[$key]->payment_gateway_id;
                            $array[$j]['payment_option']     = $gateway_options[$key]->payment_option;
                            $array[$j]['payment_value']      = $this->input->post($key);
                            $j++;

                            $payment_gateway_id = $gateway_options[$key]->payment_gateway_id;
                        }
                    }
                    $this->payment_gateway_option_m->update_batch_payment_gateway_option($array, 'id');
                    if(!is_null($this->input->post($this->payment_type . '_status'))) {
                        $this->payment_gateway_m->update_payment_gateway(['status' => $this->input->post($this->payment_type . '_status')], $payment_gateway_id);
                    }
                    $this->session->set_flashdata('success', "Success");
                    redirect(site_url("paymentsettings/index"));
                }
            } else {
                $this->data["subview"] = "paymentsettings/index";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "_not_found";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function rules()
    {
        return $this->payment_gateway->gateway($this->payment_type)->rules();
    }

    public function unique_field( $field )
    {
        $status = $this->input->post($this->payment_type . '_status');
        if ($status != '' && $status == 1 && $field == '') {
            $this->form_validation->set_message("unique_field", "The %s is required.");
            return false;
        }
        return true;
    }
}
