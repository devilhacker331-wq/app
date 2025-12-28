<?php

class BalanceService
{
    public $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model('invoice_m');
    }

    public function result( $array, $main_invoice_id )
    {
        $invoices = $this->ci->invoice_m->get_order_by_invoice([
            'maininvoiceID' => $main_invoice_id,
            'deleted_at'    => 1
        ]);

        $response = ['fee_type' => '', 'amount' => 0, 'fine' => 0, 'weaver' => 0];
        if(!empty($invoices)) {
            foreach($invoices as $invoice) {
                if(isset($array['paidamount_' . $invoice->invoiceID]) || isset($array['fine_' . $invoice->invoiceID])) {
                    $response['fee_type'] .= $invoice->feetype . ', ';
                }

                if(isset($array['paidamount_' . $invoice->invoiceID])) {
                    $response['amount'] += (float)$array['paidamount_' . $invoice->invoiceID];
                }

                if(isset($array['fine_' . $invoice->invoiceID])) {
                    $response['fine'] += (float)$array['fine_' . $invoice->invoiceID];
                }

                if(isset($array['weaver_' . $invoice->invoiceID])) {
                    $response['weaver'] += (float)$array['weaver_' . $invoice->invoiceID];
                }
            }

            if(isset($response['fee_type']) && $response['fee_type'] !== '') {
                $response['fee_type'] = substr($response['fee_type'], 0, -2);
            }
            $response['total_amount'] = $response['amount'] + $response['fine'];
        }
        return (object)$response;
    }
}