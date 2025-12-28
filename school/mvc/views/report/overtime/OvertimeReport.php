<div class="row">
    <div class="col-sm-12" style="margin:10px 0px">
        <?php
            $generatepdfurl = base_url("overtimereport/pdf/".$usertypeID."/".$userID."/".$fromdate."/".$todate);
            $generatexmlurl = base_url("overtimereport/xlsx/".$usertypeID."/".$userID."/".$fromdate."/".$todate);
            echo btn_printReport('overtimereport', $this->lang->line('report_print'), 'printablediv');
            echo btn_pdfPreviewReport('overtimereport',$generatepdfurl, $this->lang->line('report_pdf_preview'));
            echo btn_xmlReport('overtimereport',$generatexmlurl, $this->lang->line('report_xlsx'));
            echo btn_sentToMailReport('overtimereport', $this->lang->line('report_send_pdf_to_mail'));
        ?>
    </div>
</div>
<div class="box">
    <div class="box-header bg-gray">
        <h3 class="box-title text-navy"><i class="fa fa-clipboard"></i> <?=$this->lang->line('overtimereport_report_for')?> - <?=$this->lang->line('overtimereport_overtime')?>  </h3>
    </div><!-- /.box-header -->

    <div id="printablediv">
            <!-- form start -->
        <div class="box-body">
            <div class="row">
                <div class="col-sm-12">
                    <?=reportheader($siteinfos, $schoolyearsessionobj)?>
                </div>

                <?php if($fromdate != 0 && $todate != 0 ) { ?>
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="col-sm-12">
                                <h5 class="pull-left" style="margin-top:5px">
                                    <?=$this->lang->line('overtimereport_fromdate')?> : <?=date('d M Y', $fromdate)?></p>
                                </h5>
                                <h5 class="pull-right" style="margin-top:5px">
                                    <?=$this->lang->line('overtimereport_todate')?> : <?=date('d M Y', $todate)?></p>
                                </h5>
                            </div>
                        </div>
                    </div>
                <?php } elseif($usertypeID && $userID ) { ?>
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="col-sm-12">
                                <h5 class="pull-left" style="margin-top:5px">
                                    <?php
                                        echo $this->lang->line('overtimereport_role')." : ";
                                        echo $usertypes[$usertypeID];
                                    ?>
                                </h5>
                                <h5 class="pull-right" style="margin-top:5px">
                                    <?php
                                        echo $this->lang->line('overtimereport_user_name')." : ";
                                        echo $allUsers[$usertypeID][$userID]->name;
                                    ?>
                                </h5>
                            </div>
                        </div>
                    </div>
                <?php } elseif($usertypeID) { ?>
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="col-sm-12">
                                <h5 class="pull-left" style="margin-top:5px">
                                    <?php
                                        echo $this->lang->line('overtimereport_role')." : ";
                                        echo $usertypes[$usertypeID];
                                    ?>
                                </h5>
                            </div>
                        </div>
                    </div>
                <?php } elseif($usertypeID == 0) { ?>
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="col-sm-12">
                                <h5 class="pull-left" style="margin-top:5px">
                                    <?php
                                        echo $this->lang->line('overtimereport_role')." : ";
                                        echo $this->lang->line('overtimereport_alluser');
                                    ?>
                                </h5>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <div class="col-sm-12" style="margin-top:5px">
                    <?php if(customCompute($overtimes)) { ?>
                        <div id="hide-table">
                            <table id="example1" class="table table-striped table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th><?=$this->lang->line('slno')?></th>
                                    <th><?=$this->lang->line('overtimereport_role')?></th>
                                    <th><?=$this->lang->line('overtimereport_user')?></th>
                                    <th><?=$this->lang->line('overtimereport_date')?></th>
                                    <th><?=$this->lang->line('overtimereport_hours')?></th>
                                    <th><?=$this->lang->line('overtimereport_amount')?></th>
                                    <th><?=$this->lang->line('overtimereport_total_amount')?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $totalOvertimeAmount = 0; $i=1; 
                                if(customCompute($overtimes)) { foreach($overtimes as $overtime) { 
                                    $totalOvertimeAmount = $overtime->total_amount;?>
                                    <tr>
                                        <td data-title="<?=$this->lang->line('slno')?>"><?=$i?></td>
                                        <td data-title="<?=$this->lang->line('overtimereport_role')?>">
                                            <?=isset($usertypes[$overtime->usertypeID]) ? $usertypes[$overtime->usertypeID] : ''?>
                                        </td>
                                        <td data-title="<?=$this->lang->line('overtimereport_user')?>">
                                            <?=isset($allUsers[$overtime->usertypeID][$overtime->userID]) ? $allUsers[$overtime->usertypeID][$overtime->userID]->name : '' ?>
                                        </td>
                                        <td data-title="<?=$this->lang->line('overtimereport_date')?>">
                                            <?=date('d-M-Y h:i A', strtotime((string) $overtime->date))?>
                                        </td>
                                        <td data-title="<?=$this->lang->line('overtimereport_hours')?>">
                                            <?=$overtime->hours;?>
                                        </td>
                                        <td data-title="<?=$this->lang->line('overtimereport_amount')?>">
                                            <?=$overtime->amount;?>
                                        </td>
                                        <td data-title="<?=$this->lang->line('overtimereport_total_amount')?>">
                                            <?=$overtime->total_amount;?>
                                        </td>
                                    </tr>
                                    <?php $i++; } } ?>
                                <tr>
                                    <td data-title="<?=$this->lang->line('overtimereport_grand_total')?>" colspan="6" class="text-bold text-right"><?=$this->lang->line('overtimereport_grand_total')?> <?=empty($siteinfos->currency_code) ? '' : "(".$siteinfos->currency_code.")"?></td>
                                    <td data-title="<?=$this->lang->line('overtimereport_total_overtime_amount')?>" class="text-bold"><?=number_format($totalOvertimeAmount,2)?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <?php } else { ?>
                            <div class="callout callout-danger">
                                <p><b class="text-info"><?=$this->lang->line('overtimereport_data_not_found')?></b></p>
                            </div>
                        <?php } ?>
                </div>
                <div class="col-sm-12 text-center footerAll">
                    <?=reportfooter($siteinfos, $schoolyearsessionobj)?>
                </div>
            </div><!-- row -->
        </div><!-- Body -->
    </div>
</div>

<!-- email modal starts here -->
<form class="form-horizontal" role="form" action="<?=base_url('overtimereport/send_pdf_to_mail');?>" method="post">
    <div class="modal fade" id="mail">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?=$this->lang->line('overtimereport_close')?></span></button>
                <h4 class="modal-title"><?=$this->lang->line('overtimereport_mail')?></h4>
            </div>
            <div class="modal-body">

                <?php
                    if (form_error('to')) {
                                        echo "<div class='form-group has-error' >";
                                    } else {
                                        echo "<div class='form-group' >";
                                    }
                ?>
                    <label for="to" class="col-sm-2 control-label">
                        <?=$this->lang->line("overtimereport_to")?> <span class="text-red">*</span>
                    </label>
                    <div class="col-sm-6">
                        <input type="email" class="form-control" id="to" name="to" value="<?=set_value('to')?>" >
                    </div>
                    <span class="col-sm-4 control-label" id="to_error">
                    </span>
                </div>

                <?php
                    if (form_error('subject')) {
                    echo "<div class='form-group has-error' >";
                } else {
                    echo "<div class='form-group' >";
                }
                ?>
                    <label for="subject" class="col-sm-2 control-label">
                        <?=$this->lang->line("overtimereport_subject")?> <span class="text-red">*</span>
                    </label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" id="subject" name="subject" value="<?=set_value('subject')?>" >
                    </div>
                    <span class="col-sm-4 control-label" id="subject_error">
                    </span>

                </div>

                <?php
                    if (form_error('message')) {
                    echo "<div class='form-group has-error' >";
                } else {
                    echo "<div class='form-group' >";
                }
                ?>
                    <label for="message" class="col-sm-2 control-label">
                        <?=$this->lang->line("overtimereport_message")?>
                    </label>
                    <div class="col-sm-6">
                        <textarea class="form-control" id="message" style="resize: vertical;" name="message" value="<?=set_value('message')?>" ></textarea>
                    </div>
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" style="margin-bottom:0px;" data-dismiss="modal"><?=$this->lang->line('close')?></button>
                <input type="button" id="send_pdf" class="btn btn-success" value="<?=$this->lang->line("overtimereport_send")?>" />
            </div>
        </div>
      </div>
    </div>
</form>
<!-- email end here -->

<?php
    $fromdate = '';
    if($fromdate != '') {
        $fromdate = date('d-m-Y',strtotime($fromdate));
    }

    $todate = '';
    if($todate != '') {
        $todate = date('d-m-Y',strtotime($todate));
    }
?>

<script type="text/javascript">
    
    function check_email(email) {
        var status = false;
        var emailRegEx = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i;
        if (email.search(emailRegEx) == -1) {
            $("#to_error").html('');
            $("#to_error").html("<?=$this->lang->line('overtimereport_mail_valid')?>").css("text-align", "left").css("color", 'red');
        } else {
            status = true;
        }
        return status;
    }

    $('#send_pdf').click(function() {

        var field = {
            'to'            : $('#to').val(),
            'subject'       : $('#subject').val(),
            'message'       : $('#message').val(),
            'usertypeID'    : '<?=$usertypeID?>',
            'userID'        : '<?=$userID?>',
            'fromdate'      : "<?=$fromdate?>",
            'todate'        : "<?=$todate?>"
        };

        var to      = $('#to').val();
        var subject = $('#subject').val();
        var error   = 0;

        $("#to_error").html("");
        $("#subject_error").html("");

        if(to == "" || to == null) {
            error++;
            $("#to_error").html("<?=$this->lang->line('overtimereport_mail_to')?>").css("text-align", "left").css("color", 'red');
        } else {
            if(check_email(to) == false) {
                error++
            }
        }

        if(subject == "" || subject == null) {
            error++;
            $("#subject_error").html("<?=$this->lang->line('overtimereport_mail_subject')?>").css("text-align", "left").css("color", 'red');
        } else {
            $("#subject_error").html("");
        }

        if(error == 0) {
            $('#send_pdf').attr('disabled','disabled');
            $.ajax({
                type: 'POST',
                url: "<?=base_url('overtimereport/send_pdf_to_mail')?>",
                data: field,
                dataType: "html",
                success: function(data) {
                    var response = JSON.parse(data);
                    if(response.status == false) {
                        $('#send_pdf').removeAttr('disabled');
                        $.each(response, function(index, value) {
                            if(index != 'status') {
                                toastr["error"](value)
                                toastr.options = {
                                  "closeButton": true,
                                  "debug": false,
                                  "newestOnTop": false,
                                  "progressBar": false,
                                  "positionClass": "toast-top-right",
                                  "preventDuplicates": false,
                                  "onclick": null,
                                  "showDuration": "500",
                                  "hideDuration": "500",
                                  "timeOut": "5000",
                                  "extendedTimeOut": "1000",
                                  "showEasing": "swing",
                                  "hideEasing": "linear",
                                  "showMethod": "fadeIn",
                                  "hideMethod": "fadeOut"
                                }
                            }
                        });
                    } else {
                        location.reload();
                    }
                }
            });
        }
    });
</script>
