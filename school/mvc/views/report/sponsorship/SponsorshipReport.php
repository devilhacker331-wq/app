<div class="row">
    <div class="col-sm-12" style="margin:10px 0px">
        <?php
            $generatepdfurl = base_url("sponsorshipreport/pdf/".$typeId);
            $generatexmlurl = base_url("sponsorshipreport/xlsx/".$typeId);
            echo btn_printReport('sponsorshipreport', $this->lang->line('report_print'), 'printablediv');
            echo btn_pdfPreviewReport('sponsorshipreport',$generatepdfurl, $this->lang->line('report_pdf_preview'));
            echo btn_xmlReport('sponsorshipreport',$generatexmlurl, $this->lang->line('report_xlsx'));
            echo btn_sentToMailReport('sponsorshipreport', $this->lang->line('report_send_pdf_to_mail'));
        ?>
    </div>
</div>

<div class="box">
    <div class="box-header bg-gray">
        <h3 class="box-title text-navy"><i class="fa fa-clipboard"></i> 
            <?=$this->lang->line('sponsorshipreport_report_for')?> - <?=$this->lang->line('sponsorshipreport_sponsorship')?> 
        </h3>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div id="printablediv">
        <div class="box-body">
            <div class="row">
                 <div class="col-sm-12">
                    <?=reportheader($siteinfos, $schoolyearsessionobj)?>
                </div>
                <div class="col-sm-12">
                    <div class="row">
                        <div class="col-sm-12">
                            <h5 class="pull-left">
                                <?=$this->lang->line('sponsorshipreport_type')?> : <?=isset($types[$typeId]) ? $types[$typeId] : ''?>
                            </h5>          
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <?php if(customCompute($sponsorships)) { ?>
                    <div id="hide-table">
                        <table id="example1" class="table table-striped table-bordered table-hover dataTable no-footer">
                            <thead>
                                <tr>
                                    <th class="col-lg-1"><?=$this->lang->line('sponsorshipreport_slno')?></th>
                                    <th><?=$this->lang->line('sponsorshipreport_candidate_name')?></th>
                                    <th><?=$this->lang->line('sponsorshipreport_candidate_phone')?></th>
                                    <th><?=$this->lang->line('sponsorshipreport_candidate_email')?></th>
                                    <th><?=$this->lang->line('sponsorshipreport_sponsor_name')?></th>
                                    <th><?=$this->lang->line('sponsorshipreport_start_date')?></th>
                                    <th><?=$this->lang->line('sponsorshipreport_end_date')?></th>
                                </tr>
                            </thead>
                            <tbody>
                               <?php if(customCompute($sponsorships)) {$i = 1; foreach($sponsorships as $sponsorship) { ?>
                                <tr>
                                    <td data-title="<?=$this->lang->line('sponsorshipreport_slno')?>">
                                        <?=$i?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('sponsorshipreport_candidate_name')?>">
                                        <?=$sponsorship->cname?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('sponsorshipreport_candidate_phone')?>">
                                        <?=$sponsorship->cphone?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('sponsorshipreport_candidate_email')?>">
                                        <?=$sponsorship->cemail?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('sponsorshipreport_sponsor_name')?>">
                                        <?=$sponsorship->name?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('sponsorshipreport_start_date')?>">
                                       <?=date('d M Y', strtotime((string) $sponsorship->start_date)); ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('sponsorshipreport_end_date')?>">
                                        <?=date('d M Y', strtotime((string) $sponsorship->end_date)); ?>
                                    </td>
                                </tr>
                                <?php $i++; }} ?>
                            </tbody>
                        </table>
                    </div>
                    <?php } else { ?>
                        <div class="callout callout-danger">
                            <p><b class="text-info"><?=$this->lang->line('sponsorshipreport_data_not_found')?></b></p>
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
<form class="form-horizontal" role="form" action="<?=base_url('sponsorshipreport/send_pdf_to_mail');?>" method="post">
    <div class="modal fade" id="mail">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?=$this->lang->line('sponsorshipreport_close')?></span></button>
                <h4 class="modal-title"><?=$this->lang->line('sponsorshipreport_mail')?></h4>
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
                        <?=$this->lang->line("sponsorshipreport_to")?> <span class="text-red">*</span>
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
                        <?=$this->lang->line("sponsorshipreport_subject")?> <span class="text-red">*</span>
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
                        <?=$this->lang->line("sponsorshipreport_message")?>
                    </label>
                    <div class="col-sm-6">
                        <textarea class="form-control" id="message" style="resize: vertical;" name="message" value="<?=set_value('message')?>" ></textarea>
                    </div>
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" style="margin-bottom:0px;" data-dismiss="modal"><?=$this->lang->line('close')?></button>
                <input type="button" id="send_pdf" class="btn btn-success" value="<?=$this->lang->line("sponsorshipreport_send")?>" />
            </div>
        </div>
      </div>
    </div>
</form>
<!-- email end here -->

<script type="text/javascript">
    function check_email(email) {
        var status = false;
        var emailRegEx = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i;
        if (email.search(emailRegEx) == -1) {
            $("#to_error").html('');
            $("#to_error").html("<?=$this->lang->line('sponsorshipreport_mail_valid')?>").css("text-align", "left").css("color", 'red');
        } else {
            status = true;
        }
        return status;
    }

    $('#send_pdf').click(function() { 
        var field = {
            'to'      : $('#to').val(), 
            'subject' : $('#subject').val(), 
            'message' : $('#message').val(),
            'typeID'  : '<?=$typeId?>',
        };

        var to = $('#to').val();
        var subject = $('#subject').val();
        var error = 0;

        $("#to_error").html("");
        $("#subject_error").html("");

        if(to == "" || to == null) {
            error++;
            $("#to_error").html("<?=$this->lang->line('sponsorshipreport_mail_to')?>").css("text-align", "left").css("color", 'red');
        } else {
            if(check_email(to) == false) {
                error++
            }
        }

        if(subject == "" || subject == null) {
            error++;
            $("#subject_error").html("<?=$this->lang->line('sponsorshipreport_mail_subject')?>").css("text-align", "left").css("color", 'red');
        } else {
            $("#subject_error").html("");
        }

        if(error == 0) {
            $('#send_pdf').attr('disabled','disabled');
            $.ajax({
                type: 'POST',
                url: "<?=base_url('sponsorshipreport/send_pdf_to_mail')?>",
                data: field,
                dataType: "html",
                success: function(data) {
                    var response = JSON.parse(data);
                    if (response.status == false) {
                        $('#send_pdf').removeAttr('disabled');
                        if(response.to) {
                            $("#to_error").html("<?=$this->lang->line('sponsorshipreport_mail_to')?>").css("text-align", "left").css("color", 'red');
                        } 

                        if( response.subject) {
                            $("#subject_error").html("<?=$this->lang->line('sponsorshipreport_mail_subject')?>").css("text-align", "left").css("color", 'red');
                        }

                        if(response.message) {
                            toastr["error"](response.message)
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
                    } else {
                        location.reload();
                    }
                }
            });
        }
    });
</script>