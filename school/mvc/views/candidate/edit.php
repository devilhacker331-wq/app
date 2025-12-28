<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-get-pocket"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li><a href="<?=base_url("candidate/index")?>"><?=$this->lang->line('menu_candidate')?></a></li>
            <li class="active"><?=$this->lang->line('menu_edit')?> <?=$this->lang->line('menu_candidate')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-10">
                <form class="form-horizontal" role="form" method="post">

                    <div class="form-group <?=form_error('studentID') ? 'has-error' : ''?>">
                        <label for="studentID" class="col-sm-2 control-label">
                            <?=$this->lang->line("candidate_student")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <?php
                                $studentArray = array('0' => $this->lang->line("candidate_select_student"));
                                if(customCompute($students)) {
                                    foreach ($students as $student) {
                                        $studentArray[$student->srstudentID] = $student->srname;
                                    }
                                }
                                echo form_dropdown("studentID", $studentArray, set_value("studentID", $studentInfo->student_id), "id='studentID' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?=form_error('studentID'); ?>
                        </span>
                    </div>
                    <div class="form-group <?=form_error('registration_no') ? 'has-error' : ''?>">
                        <label for="grade" class="col-sm-2 control-label">
                            <?=$this->lang->line("candidate_registration_no")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="registration_no" name="registration_no" value="<?=set_value('registration_no', $studentInfo->registration_no)?>" readonly>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?=form_error('registration_no'); ?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('classesID') ? 'has-error' : ''?>">
                        <label for="classesID" class="col-sm-2 control-label">
                            <?=$this->lang->line("candidate_class")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="classes" value="<?=$studentInfo->class?>" readonly>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?=form_error('classesID'); ?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('sectionID') ? 'has-error' : ''?>">
                        <label for="sectionID" class="col-sm-2 control-label">
                            <?=$this->lang->line("candidate_section")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="section" value="<?=$studentInfo->section?>" readonly>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?=form_error('sectionID'); ?>
                        </span>
                    </div>



                    <div class="form-group <?=form_error('verified_by') ? 'has-error' : ''?>">
                        <label for="verified_by" class="col-sm-2 control-label">
                            <?=$this->lang->line("candidate_verified_by")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="verified_by" name="verified_by" value="<?=set_value('verified_by',$candidate->verified_by)?>">
                        </div>
                        <span class="col-sm-4 control-label">
                            <?=form_error('verified_by'); ?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('date_verification') ? 'has-error' : ''?>">
                        <label for="date_verification" class="col-sm-2 control-label">
                            <?=$this->lang->line("candidate_date_of_verification")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="date"   autocomplete="off" name="date_verification" value="<?=set_value('date', date("d-m-Y", strtotime((string) $candidate->date_verification)))?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?=form_error('date_verification'); ?>
                        </span>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-8">
                            <input type="submit" class="btn btn-success" value="<?=$this->lang->line("update_candidate")?>" >
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('.select2').select2();

    $("#date").datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        startDate:'<?=$schoolyearsessionobj->startingdate?>',
        endDate:'<?=$schoolyearsessionobj->endingdate?>',
        daysOfWeekDisabled: "<?=$siteinfos->weekends?>",
        datesDisabled: ["<?=$get_all_holidays;?>"],
    });

    $(document).on('change', '#studentID', function() {
        var studentID = $('#studentID').val();
        $.ajax({
            type: 'POST',
            url: "<?=base_url('candidate/getSingleStudent')?>",
            data: { 'studentID' : studentID},
            dataType: "html",
            success: function(data) {
                var response = JSON.parse(data);
                $('#classes').val(response.class);
                $('#section').val(response.section);
                $('#registration_no').val(response.registration_no);
                // $('#sex').val(response.sex);
                // $('#grade').val(response.grade);
            }
        });
    });

</script>
