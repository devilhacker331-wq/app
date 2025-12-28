<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa iniicon-onlineexamquestionanswerreport"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_onlineexamquestionanswerreport')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->

    <div class="box-body">
        <div class="row">
            <div class="col-sm-3">
                <div class="form-group col-sm-12" id="examDiv">
                    <label><?=$this->lang->line("onlineexamquestionanswerreport_exam")?></label><span class="text-red">*</span>
                    <?php
                        $array = array("0" => $this->lang->line("onlineexamquestionanswerreport_please_select"));
                        if(customCompute($online_exams)) {
                            foreach ($online_exams as $online_exam) {
                                 $array[$online_exam->onlineExamID] = $online_exam->name;
                            }
                        }
                        echo form_dropdown("onlineExamID", $array, set_value("onlineExamID"), "id='onlineExamID' class='form-control select2'");
                     ?>
                </div>
            </div>
            <div class="col-sm-9">
                <div class="form-group col-sm-4" id="studentDiv">
                    <label for="studentID"><?=$this->lang->line("onlineexamquestionanswerreport_student")?></label> <span class="text-red">*</span>
                    <select id="studentID" name="studentID" class="form-control select2">
                        <option value="0"><?php echo $this->lang->line("onlineexamquestionanswerreport_please_select"); ?></option>
                    </select>
                </div>
                <div class="form-group col-sm-4" id="attemptDiv">
                    <label for="attemptID"><?=$this->lang->line("onlineexamquestionanswerreport_attempt")?></label> <span class="text-red">*</span>
                    <select id="attemptID" name="attemptID" class="form-control select2">
                        <option value="0"><?php echo $this->lang->line("onlineexamquestionanswerreport_please_select"); ?></option>
                    </select>
                </div>

                <div class="col-sm-4">
                    <button id="get_question_list" class="btn btn-success" style="margin-top:23px;"> <?=$this->lang->line("onlineexamquestionanswerreport_submit")?></button>
                </div>
            </div>
        </div><!-- row -->
    </div><!-- Body -->
</div><!-- /.box -->

<div id="load_onlineexamquestionanswerreport"></div>


<script type="text/javascript">
    $('.select2').select2();
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
    }


    $(document).on('change', "#onlineExamID", function() {
        var onlineExamID = $(this).val();
        if(onlineExamID == '0') {
            $('#studentID').html('<option value="0">'+"<?=$this->lang->line("onlineexamquestionanswerreport_please_select")?>"+'</option>');
            $('#studentID').val(0);

            $('#attemptID').html('<option value="0">'+"<?=$this->lang->line("onlineexamquestionanswerreport_please_select")?>"+'</option>');
            $('#attemptID').val(0);

        } else {
            $('#studentID').html('<option value="0">'+"<?=$this->lang->line("onlineexamquestionanswerreport_please_select")?>"+'</option>');
            $('#studentID').val(0);

            $('#attemptID').html('<option value="0">'+"<?=$this->lang->line("onlineexamquestionanswerreport_please_select")?>"+'</option>');
            $('#attemptID').val(0);
            $.ajax({
                type: 'POST',
                url: "<?=base_url('onlineexamquestionanswerreport/getStudent')?>",
                data: {'onlineExamID' : onlineExamID},
                dataType: "html",
                success: function(data) {
                    console.log(data);
                    $('#studentID').html(data);
                }
            });
        }
    });

    $(document).on('change', "#studentID", function() {
        var studentID = $(this).val();
        var onlineExamID =   $('#onlineExamID').val();
        if(studentID == '0') {
            $('#studentID').html('<option value="0">'+"<?=$this->lang->line("onlineexamquestionanswerreport_please_select")?>"+'</option>');
            $('#studentID').val(0);

        } else {

            $.ajax({
                type: 'POST',
                url: "<?=base_url('onlineexamquestionanswerreport/getAttempt')?>",
                data: {"studentID" : studentID, "onlineExamID" : onlineExamID},
                dataType: "html",
                success: function(data) {
                    $('#attemptID').html(data);
                }
            });
        }
    });



    $("#get_question_list").click(function() {
        var error = 0 ;
        var field ={
            'onlineExamID' : $('#onlineExamID').val(),
            'studentID' : $('#studentID').val(),
            'attemptID' : $('#attemptID').val(),
        }

        if (field['onlineExamID'] == 0) {
            $('#examDiv').addClass('has-error');
            error++;
        } else {
            $('#examDiv').removeClass('has-error');
        }


        if (field['studentID'] == 0) {
            $('#studentDiv').addClass('has-error');
            error++;
        } else {
            $('#studentDiv').removeClass('has-error');
        }
        if (field['attemptID'] == 0) {
            $('#attemptDiv').addClass('has-error');
            error++;
        } else {
            $('#attemptDiv').removeClass('has-error');
        }

        if(error === 0) {
            makingPostDataPreviousofAjaxCall(field);
        }
    });

    function makingPostDataPreviousofAjaxCall(field) {
        passData = field;
        ajaxCall(passData);
    }

    function ajaxCall(passData) {
        $.ajax({
            type: 'POST',
            url: "<?=base_url('onlineexamquestionanswerreport/getQuestionList')?>",
            data: passData,
            dataType: "html",
            success: function(data) {
                var response = JSON.parse(data);
                renderLoder(response, passData);
            }
        });
    }

    function renderLoder(response, passData) {
        if(response.status) {
            $('#load_onlineexamquestionanswerreport').html(response.render);
            for (var key in passData) {
                if (passData.hasOwnProperty(key)) {
                    $('#'+key).parent().removeClass('has-error');
                }
            }
        } else {
            for (var key in passData) {
                if (passData.hasOwnProperty(key)) {
                    $('#'+key).parent().removeClass('has-error');
                }
            }

            for (var key in response) {
                if (response.hasOwnProperty(key)) {
                    $('#'+key).parent().addClass('has-error');
                }
            }
        }
    }
</script>
