<div class="well">
    <div class="row">
        <div class="col-sm-6">

            <button class="btn-cs btn-sm-cs" onclick="javascript:printDiv('printablediv')"><span class="fa fa-print"></span> <?=$this->lang->line('print')?> </button>
            <?=btn_add_pdf('sponsor/print_preview/'.$sponsor->sponsorID, $this->lang->line('pdf_preview'))?>

            <?php if(permissionChecker('sponsor_edit')) { echo btn_sm_edit('sponsor/edit/'.$sponsor->sponsorID, $this->lang->line('edit')); }
            ?>
            <button class="btn-cs btn-sm-cs" data-toggle="modal" data-target="#mail"><span class="fa fa-envelope-o"></span> <?=$this->lang->line('mail')?></button>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb">
                <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
                <li><a href="<?=base_url("sponsor/index")?>"><?=$this->lang->line('menu_sponsor')?></a></li>
                <li class="active"><?=$this->lang->line('view')?></li>
            </ol>
        </div>
    </div>
</div>


<div id="printablediv">
    <div class="row">
        <div class="col-sm-3">
                <div class="box box-primary">
                    <div class="box-body box-profile">
                        <?=profileviewimage($sponsor->photo)?>
                        <h3 class="profile-username text-center"><?=$sponsor->name?></h3>
                        <?php if($sponsor->type == 'individual') { ?>
                        <p class="text-muted text-center">Individual</p>
                       <?php } else { ?> <p class="text-muted text-center">Organisation</p <?php } ?>
                        <ul class="list-group list-group-unbordered">
                            <li class="list-group-item" style="background-color: #FFF">
                                <b><?=$this->lang->line('sponsor_phone')?></b> <a class="pull-right"><?=$sponsor->phone?></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        <div class="col-sm-9">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#profile" data-toggle="tab"><?=$this->lang->line('sponsor_sponsor')?></a></li>
                </ul>
                <div class="tab-content">
                    <div class="active tab-pane" id="profile">
                        <div class="panel-body profile-view-dis">
                            <div class="row">
                                <?php if($sponsor->type == 'individual') { ?>
                                    <div class="profile-view-tab">
                                        <p><span><?=$this->lang->line("sponsor_title")?> </span>: <?=isset($titles[$sponsor->title]) ? $titles[$sponsor->title] : ''?></p>
                                    </div>
                                    <div class="profile-view-tab">
                                        <p><span><?=$this->lang->line("sponsor_person_name")?> </span>: <?=$sponsor->name;?></p>
                                    </div>
                                    <div class="profile-view-tab">
                                        <p><span><?=$this->lang->line("sponsor_organisation_name")?> </span>: <?=$sponsor->organisation_name;?></p>
                                    </div>
                                <?php } else { ?>
                                    <div class="profile-view-tab">
                                        <p><span><?=$this->lang->line("sponsor_sponsor_organisation_name")?> </span>: <?=$sponsor->name;?></p>
                                    </div>
                                    <div class="profile-view-tab">
                                        <p><span><?=$this->lang->line("sponsor_title")?> </span>: <?=isset($titles[$sponsor->title]) ? $titles[$sponsor->title] : ''?></p>
                                    </div>
                                    <div class="profile-view-tab">
                                        <p><span><?=$this->lang->line("sponsor_contact_person_name")?> </span>: <?=$sponsor->organisation_name;?></p>
                                    </div>
                                <?php } ?>
                                <div class="profile-view-tab">
                                    <p><span><?=$this->lang->line("sponsor_email")?> </span>: <?=$sponsor->email;?></p>
                                </div>


                                <div class="profile-view-tab">
                                    <p><span><?=$this->lang->line("sponsor_country")?> </span>:
                                    <?=isset($allcountry[$sponsor->country]) ? $allcountry[$sponsor->country] : ''?>
                                    </p>
                                </div>
                                <div class="profile-view-tab">
                                    <p><span><?=$this->lang->line("sponsor_create_date")?> </span>: <?=date("d M Y", strtotime((string) $sponsor->create_date));?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- email modal starts here -->
<form class="form-horizontal" role="form" action="<?=base_url('sponsor/send_mail');?>" method="post">
    <div class="modal fade" id="mail">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title"><?=$this->lang->line('mail')?></h4>
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
                        <?=$this->lang->line("to")?> <span class="text-red">*</span>
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
                        <?=$this->lang->line("subject")?> <span class="text-red">*</span>
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
                        <?=$this->lang->line("message")?>
                    </label>
                    <div class="col-sm-6">
                        <textarea class="form-control" id="message" style="resize: vertical;" name="message" value="<?=set_value('message')?>" ></textarea>
                    </div>
                </div>

            
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" style="margin-bottom:0px;" data-dismiss="modal"><?=$this->lang->line('close')?></button>
                <input type="button" id="send_pdf" class="btn btn-success" value="<?=$this->lang->line("send")?>" />
            </div>
        </div>
      </div>
    </div>
</form>
<!-- email end here -->

<script language="javascript" type="text/javascript">
    function printDiv(divID) {
        //Get the HTML of div
        var divElements = document.getElementById(divID).innerHTML;
        //Get the HTML of whole page
        var oldPage = document.body.innerHTML;

        //Reset the page's HTML with div's HTML only
        document.body.innerHTML = 
          "<html><head><title></title></head><body>" + 
          divElements + "</body>";

        //Print Page
        window.print();

        //Restore orignal HTML
        document.body.innerHTML = oldPage;
        window.location.reload();

    }
    function closeWindow() {
        location.reload(); 
    }

    function check_email(email) {
        var status = false;     
        var emailRegEx = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i;
        if (email.search(emailRegEx) == -1) {
            $("#to_error").html('');
            $("#to_error").html("<?=$this->lang->line('mail_valid');?>").css("text-align", "left").css("color", 'red');
        } else {
            status = true;
        }
        return status;
    }

    $('#send_pdf').click(function() {
        var to = $('#to').val();
        var subject = $('#subject').val();
        var message = $('#message').val();
        var sponsorID = "<?=$sponsor->sponsorID?>";
        var error = 0;

        $("#to_error").html("");
        if(to == "" || to == null) {
            error++;
            $("#to_error").html("");
            $("#to_error").html("<?=$this->lang->line('mail_to')?>").css("text-align", "left").css("color", 'red');
        } else {
            if(check_email(to) == false) {
                error++
            }
        } 

        if(subject == "" || subject == null) {
            error++;
            $("#subject_error").html("");
            $("#subject_error").html("<?=$this->lang->line('mail_subject')?>").css("text-align", "left").css("color", 'red');
        } else {
            $("#subject_error").html("");
        }

        if(error == 0) {
            $('#send_pdf').attr('disabled','disabled');
            $.ajax({
                type: 'POST',
                url: "<?=base_url('sponsor/send_mail')?>",
                data: 'to='+ to + '&subject=' + subject + "&sponsorID=" + sponsorID+ "&message=" + message,
                dataType: "html",
                success: function(data) {
                    var response = JSON.parse(data);
                    if (response.status == false) {
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
