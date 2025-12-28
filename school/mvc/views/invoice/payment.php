<form role="form" method="post" id="payment-form">
<div class="row">
    <div class="col-sm-3">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa icon-invoice"></i> <?=$this->lang->line('invoice_payment')?></h3>
            </div>

            <div class="box-body box-profile">
                <center>
                <?=profileviewimage($studentprofile->photo)?>
                </center>

                <h3 class="profile-username text-center"><?=$student->srname?></h3>

                <p class="text-muted text-center"><?=$usertype->usertype?></p>

                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item" style="background-color: #FFF">
                        <b><?=$this->lang->line('invoice_registerno')?></b> <a class="pull-right"><?=$student->srregisterNO?></a>
                    </li>
                    <li class="list-group-item" style="background-color: #FFF">
                        <b><?=$this->lang->line('invoice_roll')?></b> <a class="pull-right"><?=$student->srroll?></a>
                    </li>
                    <li class="list-group-item" style="background-color: #FFF">
                        <b><?=$this->lang->line('invoice_class')?></b> <a class="pull-right"><?=customCompute($class) ? $class->classes : ''?></a>
                    </li>
                    <li class="list-group-item" style="background-color: #FFF">
                        <b><?=$this->lang->line('invoice_section')?></b> <a class="pull-right"><?=customCompute($section) ? $section->section : ''?></a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="box" style="margin-bottom:40px">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-money"></i> <?=$this->lang->line('panel_title')?></h3>
            </div>
            <div class="box-body">
                <div class="form-group <?=form_error('payment_method') ? 'has-error' : '' ?>">
                    <label for="payment_method"><?=$this->lang->line('invoice_paymentmethod')?> <span class="text-red">*</span></label>

                    <?php
                        echo form_dropdown("payment_method", $payment_gateway, set_value("payment_method"), "id='payment_method' class='form-control select2' ");
                    ?>
                    <span class="text-red">
                        <?=form_error('payment_method')?>
                    </span>
                </div>
                <?php
                if(customCompute($payment_settings)) {
                    foreach($payment_settings as $payment_setting) {
                        if($payment_setting->misc != null) {
                            $misc = json_decode((string) $payment_setting->misc);
                            if(customCompute($misc->input)) {
                                foreach($misc->input as $input) {
                                    $this->load->view($input);
                                }
                            }
                        }
                    }
                }
                ?>
                <button id="addPaymentButton" type="submit" class="btn btn-success"><?=$this->lang->line('add_payment')?></button>
            </div>
        </div>
    </div>

    <div class="col-sm-9">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa icon-feetypes"></i> <?=$this->lang->line('invoice_feetype_list')?></h3>
                <ol class="breadcrumb">
                    <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
                    <li><a href="<?=base_url("invoice/index")?>"><?=$this->lang->line('menu_invoice')?></a></li>
                    <li class="active"><?=$this->lang->line('menu_add')?> <?=$this->lang->line('invoice_payment')?></li>
                </ol>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered feetype-style" style="font-size: 16px;">
                        <thead>
                            <tr>
                                <th class="col-sm-1"><?=$this->lang->line('slno')?></th>
                                <th class="col-sm-3"><?=$this->lang->line('invoice_feetype')?></th>
                                <th class="col-sm-1" ><?=$this->lang->line('invoice_amount')?></th>
                                <th class="col-sm-1" ><?=$this->lang->line('invoice_due')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('invoice_paid_amount')?></th>
                                <?php if($this->session->userdata('usertypeID') == 1 || $this->session->userdata('usertypeID') == 5) { ?>
                                    <th class="col-sm-2" ><?=$this->lang->line('invoice_weaver')?></th>
                                    <th class="col-sm-2"><?=$this->lang->line('invoice_fine')?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody id="feetypeList">
                            <?php 
                                $totalAmount = 0;
                                $totalDue = 0;
                                if(customCompute($invoices)) {
                                    $i = 1;
                                    foreach ($invoices as $invoice) {
                                        $amount = $invoice->amount;
                                        $due = 0;

                                        if(isset($invoicepaymentandweaver['totalamount'][$invoice->invoiceID])) {
                                            $due = $invoicepaymentandweaver['totalamount'][$invoice->invoiceID]; 

                                            if(isset($invoicepaymentandweaver['totaldiscount'][$invoice->invoiceID])) {
                                                $due -= $invoicepaymentandweaver['totaldiscount'][$invoice->invoiceID];
                                            }

                                            if(isset($invoicepaymentandweaver['totalpayment'][$invoice->invoiceID])) {
                                                $due -= $invoicepaymentandweaver['totalpayment'][$invoice->invoiceID];
                                            } 

                                            if(isset($invoicepaymentandweaver['totalweaver'][$invoice->invoiceID])) {
                                                $due -= $invoicepaymentandweaver['totalweaver'][$invoice->invoiceID];
                                            }
                                        }

                                        $totalAmount += $amount; 
                                        $totalDue += $due; 
                                        $rand = rand(1, 9999999999);

                                        echo '<tr id="tr_'.$rand.'">';
                                            echo '<td>';
                                                echo $i; 
                                            echo '</td>';

                                            echo '<td>';
                                                echo isset($feetypes[$invoice->feetypeID]) ? $feetypes[$invoice->feetypeID] : ''; 
                                            echo '</td>';

                                            echo '<td>';
                                                echo $amount; 
                                            echo '</td>';

                                            echo '<td id="due_'.$rand.'">';
                                                echo $due; 
                                            echo '</td>';

                                            echo '<td>';
                                                echo '<input id="paidamount_'.$rand.'" class="form-control change-paidamount '.(form_error('paidamount_'.$invoice->invoiceID) ? 'bordered-red' : '').'" type="text" name="paidamount_'.$invoice->invoiceID.'" value="'.set_value('paidamount_'.$invoice->invoiceID).'" >';
                                            echo '</td>';

                                            if($this->session->userdata('usertypeID') == 1 || $this->session->userdata('usertypeID') == 5) {
                                                echo '<td>';
                                                    echo '<input id="weaver_'.$rand.'" class="form-control change-weaver '.(form_error('weaver_'.$invoice->invoiceID) ? 'bordered-red' : '').'" type="text" name="weaver_'.$invoice->invoiceID.'" value="'.set_value('weaver_'.$invoice->invoiceID).'" >';
                                                echo '</td>';

                                                echo '<td>';
                                                    echo '<input id="fine_'.$rand.'" class="form-control change-fine '.(form_error('fine_'.$invoice->invoiceID) ? 'bordered-red' : '').'" type="text" name="fine_'.$invoice->invoiceID.'" value="'.set_value('fine_'.$invoice->invoiceID).'" >';
                                                echo '</td>';
                                            }
                                        echo '</tr>';
                                        $i++;
                                    } 
                                } 
                            ?>
                        </tbody>
                        <tfoot id="feetypeListFooter">
                            <tr>
                                <td colspan="2" style="font-weight: bold"><?=$this->lang->line('invoice_total')?></td>
                                <td id="totalAmount" style="font-weight: bold"><?=number_format($totalAmount, 2);?></td>
                                <td id="totalDue" style="font-weight: bold"><?=number_format($totalDue, 2);?></td>
                                <td id="totalPaidAmount" style="font-weight: bold">0.00</td>
                                <?php if($this->session->userdata('usertypeID') == 1 || $this->session->userdata('usertypeID') == 5) { ?>
                                    <td id="totalWeaver" style="font-weight: bold">0.00</td>
                                    <td id="totalFine" style="font-weight: bold">0.00</td>
                                <?php } ?>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</form>

<?php
$js_gateway     = [];
$submit_gateway = [];
if(customCompute($payment_settings)) {
    foreach($payment_settings as $payment_setting) {
        if($payment_setting->misc != null) {

            $misc = json_decode((string) $payment_setting->misc);
            if(customCompute($misc->js)) {
                foreach($misc->js as $js) {
                    $this->load->view($js);
                }
            }

            if(customCompute($misc->input) && isset($misc->input[0])) {
                $js_gateway[$payment_setting->slug] = isset($misc->input[0]);
            }

            if(customCompute($misc->input) && (isset($misc->submit) && $misc->submit)) {
                $submit_gateway[$payment_setting->slug] = $misc->submit;
            }
        }
    }
}

$js_gateway     = json_encode($js_gateway);
$submit_gateway = json_encode($submit_gateway);

?>

<script type="text/javascript">
    const gateway = <?=$js_gateway?>;
    const submit_gateway = <?=$submit_gateway?>;

    let form = document.getElementById('payment-form');
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        let payment_method = $('#payment_method').val();
        let submit = false;
        for(let item in submit_gateway) {
            if(item == payment_method) {
                submit = true;
                window[payment_method + '_payment']();
                break;
            }
        }

        if(submit == false) {
            form.submit();
        }
    });

    $('.select2').select2();

    $(document).change(function() {
        let payment_method = $('#payment_method').val();
        for(let item in gateway) {
            if(item == payment_method) {
                console.log('#' + item + '_div');
                if(gateway[item]) {
                    $('#' + item + '_div').show();
                }
            } else {
                $('#' + item + '_div').hide();
            }
        }
    });
    function getRandomInt() {
      return Math.floor(Math.random() * Math.floor(9999999999999999));
    }

    function toFixedVal(x) {
      if (Math.abs(x) < 1.0) {
        var e = parseFloat(x.toString().split('e-')[1]);
        if (e) {
            x *= Math.pow(10,e-1);
            x = '0.' + (new Array(e)).join('0') + x.toString().substring(2);
        }
      } else {
        var e = parseFloat(x.toString().split('+')[1]);
        if (e > 20) {
            e -= 20;
            x /= Math.pow(10,e);
            x += (new Array(e+1)).join('0');
        }
      }
      return x;
    }

    function isNumeric(n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    }

    function dotAndNumber(data) {
        var retArray = [];
        var fltFlag = true;
        if(data.length > 0) {
            for(var i = 0; i <= (data.length-1); i++) {
                if(i == 0 && data.charAt(i) == '.') {
                    fltFlag = false;
                    retArray.push(true);
                } else {
                    if(data.charAt(i) == '.' && fltFlag == true) {
                        retArray.push(true);
                        fltFlag = false;
                    } else {
                        if(isNumeric(data.charAt(i))) {
                            retArray.push(true);
                        } else {
                            retArray.push(false);
                        }
                    }

                }
            }
        }

        if(jQuery.inArray(false, retArray) ==  -1) {
            return true;
        }
        return false;
    }

    function floatChecker(value) {
        var val = value;
        if(isNumeric(val)) {
            return true;
        } else {
            return false;
        }
    }

    function lenChecker(data, len) {
        var retdata = 0;
        var lencount = 0;
        data = toFixedVal(data);
        if(data.length > len) {
            lencount = (data.length - len);
            data = data.toString();
            data = data.slice(0, -lencount);
            retdata = parseFloat(data);
        } else {
            retdata = parseFloat(data);
        }

        return toFixedVal(retdata);
    }
    
    function parseSentenceForNumber(sentence) {
        var matches = sentence.replace(/,/g, '').match(/(\+|-)?((\d+(\.\d+)?)|(\.\d+))/);
        return matches && matches[0] || null;
    }

    function currencyConvert(data) {
        return data.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
    }

    var globaltotalpaidamount = 0;
    var globaltotalweaver = 0;
    var globaltotalfine = 0;
    function totalInfo() {
        var totalPaidAmount = 0;
        var totalWeaver = 0;
        var totalFine = 0;
        
        $('#feetypeList tr').each(function(index, value) {
            if($(this).children().eq(4).children().val() != '' && $(this).children().eq(4).children().val() != null && $(this).children().eq(4).children().val() != '.') {
                var paidamount = parseFloat($(this).children().eq(4).children().val());
                totalPaidAmount += paidamount;
            }

            if($(this).children().eq(5).children().val() != '' && $(this).children().eq(5).children().val() != null && $(this).children().eq(5).children().val() != '.') {
                var weaver = parseFloat($(this).children().eq(5).children().val());
                totalWeaver += weaver;
            }

            if($(this).children().eq(6).children().val() != '' && $(this).children().eq(6).children().val() != null && $(this).children().eq(6).children().val() != '.') {
                var fine = parseFloat($(this).children().eq(6).children().val());
                totalFine += fine;
            }
        });

        globaltotalpaidamount = totalPaidAmount;
        $('#totalPaidAmount').text(currencyConvert(totalPaidAmount)); 

        globaltotalweaver = totalWeaver;
        $('#totalWeaver').text(currencyConvert(totalWeaver));

        globaltotalfine = totalFine;
        $('#totalFine').text(currencyConvert(totalFine));
    }

    $(document).on('keyup', '.change-paidamount', function() {
        var trID = $(this).parent().parent().attr('id').replace('tr_','');
        var due = $('#due_'+trID).text();
        var paidamount = $('#paidamount_'+trID).val();
        var weaver = $('#weaver_'+trID).val();

        var duestatus = false;
        var dotandnumberstatus = false;
        var paidamountstatus = false;

        if(due != '' && due != null && due > 0) {
            duestatus = true;
        }

        if(duestatus) {
            if(dotAndNumber(paidamount)) {
                dotandnumberstatus = true;
            } else {
                dotandnumberstatus = false;
                $('#paidamount_'+trID).val(parseSentenceForNumber(toFixedVal(paidamount)));
            }
        }

        if(dotandnumberstatus) {
            if(paidamount.length > 15) {
                paidamount = lenChecker(paidamount, 15);
                $('#paidamount_'+trID).val(paidamount);
                paidamountstatus = true;
            } else {
                paidamountstatus = true;
            }
        } else {
            $('#paidamount_'+trID).val('');
        }

        if(paidamountstatus) {
            if(weaver > 0) {
                weaver = parseFloat(weaver);
                paidamount = parseFloat(paidamount);
                due = parseFloat(due);
                if(weaver+paidamount > due) {
                    $('#paidamount_'+trID).val((due-weaver));
                }
            } else {
                paidamount = parseFloat(paidamount);
                due = parseFloat(due);
                if(paidamount > due) {
                    $('#paidamount_'+trID).val(due);
                }
            }

            if(parseFloat($(this).val()) == 0) {
                $('#paidamount_'+trID).val('');
            }
            totalInfo();
        }
    });

    $(document).on('keyup', '.change-weaver', function() {
        var trID = $(this).parent().parent().attr('id').replace('tr_','');
        var due = $('#due_'+trID).text();
        var paidamount = $('#paidamount_'+trID).val();
        var weaver = $('#weaver_'+trID).val();

        var duestatus = false;
        var dotandnumberstatus = false;
        var weaverstatus = false;

        if(due != '' && due != null && due > 0) {
            duestatus = true;
        }

        if(duestatus) {
            if(dotAndNumber(weaver)) {
                dotandnumberstatus = true;
            } else {
                dotandnumberstatus = false;
                $('#weaver_'+trID).val(parseSentenceForNumber(toFixedVal(weaver)));
            }
        } else {
            $('#weaver_'+trID).val('');
        }

        if(dotandnumberstatus) {
            if(weaver.length > 15) {
                weaver = lenChecker(weaver, 15);
                $('#weaver_'+trID).val(weaver);
                weaverstatus = true;
            } else {
                weaverstatus = true;
            }
        }

        if(weaverstatus) {
            if(paidamount > 0) {
                paidamount = parseFloat(paidamount);
                weaver = parseFloat(weaver);
                due = parseFloat(due);
                if(weaver+paidamount > due) {
                    $('#weaver_'+trID).val((due-paidamount));
                }
            } else {
                weaver = parseFloat(weaver);
                due = parseFloat(due);
                if(weaver > due) {
                    $('#weaver_'+trID).val(due);
                }
            }

            if(parseFloat($(this).val()) == 0) {
                $('#weaver_'+trID).val('');
            }
            totalInfo();
        }
    });

    $(document).on('keyup', '.change-fine', function() {
        var fine = $(this).val();

        var dotandnumberstatus = false;
        var finestatus = false;

        if(dotAndNumber(fine)) {
            dotandnumberstatus = true;
        } else {
            dotandnumberstatus = false;
            $(this).val(parseSentenceForNumber(toFixedVal(fine)));
        }

        if(dotandnumberstatus) {
            if(fine.length > 15) {
                fine = lenChecker(fine, 15);
                $(this).val(fine);
                finestatus = true;
            } else {
                finestatus = true;
            }

            totalInfo();
        }
    });

    totalInfo();


</script>
