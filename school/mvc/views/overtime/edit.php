<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-retweet"></i> <?=$this->lang->line('panel_title')?></h3>

        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i>
                    <?=$this->lang->line('menu_dashboard')?></a></li>
            <li><a href="<?=base_url("overtime/index")?>"><?=$this->lang->line('menu_overtime')?></a></li>
            <li class="active"><?=$this->lang->line('menu_edit')?> <?=$this->lang->line('menu_overtime')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body" style="min-height: 400px">
        <div class="row">
            <div class="col-sm-10">
                <form class="form-horizontal" role="form" method="post">
                    <div class="form-group <?=form_error('roleId') ? 'has-error' : ''?>">
                        <label for="roleId" class="col-sm-2 control-label">
                            <?=$this->lang->line("overtime_role")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                           <?php
                                $array = [ 0 => $this->lang->line("overtime_select_role")];
                                if(customCompute($roles)) {
                                    foreach ($roles as $key => $role) {
                                        $array[$role->usertypeID] = $role->usertype;
                                    }
                                }
                                
                                echo form_dropdown("roleId", $array, set_value("roleId",$overtime->usertypeID), "id='roleId' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('roleId'); ?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('userId') ? 'has-error' : ''?>">
                        <label for="userId" class="col-sm-2 control-label">
                            <?=$this->lang->line("overtime_user")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <div class="select2-wrapper">
                                <?php
                                    $array = array();
                                    $array[0] = $this->lang->line("overtime_select_user");
               
                                    if($users != null) {
                                        foreach ($users as $user) {
                                            if($user->usertypeID == 1) {
                                                $array[$user->systemadminID] = $user->name;
                                            } elseif($user->usertypeID == 2) {
                                                $array[$user->teacherID] = $user->name;
                                            } elseif($user->usertypeID == 3) {
                                                $array[$user->studentID] = $user->name;
                                            } elseif($user->usertypeID == 4) {
                                                $array[$user->parentsID] = $user->name;
                                            } else {
                                                 $array[$user->userID] = $user->name;
                                            }
                                        }
                                    }

                                    $usrID = 0;
                                    $usrID = $setUserId == 0 ? 0 : $setUserId;

                                    echo form_dropdown("userId", $array, set_value("userId", $overtime->userID), "id='userId' class='form-control'");
                                ?>
                            </div>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('userId'); ?>
                        </span>
                    </div>
              
                    <div class="form-group <?=form_error('overtime_date') ? 'has-error' : ''?>">
                        <label for="overtime_date" class="col-sm-2 control-label">
                            <?=$this->lang->line("overtime_date")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="overtime_date" name="overtime_date" value="<?=set_value('overtime_date', date('d m Y h:i A', strtotime((string) $overtime->date)))?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('overtime_date'); ?>
                        </span>
                    </div>

                    <div class="form-group <?=form_error('overtime_hours') ? 'has-error' : ''?>">
                        <label for="overtime_hours" class="col-sm-2 control-label">
                            <?=$this->lang->line("overtime_hours")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="overtime_hours" name="overtime_hours"
                                value="<?=set_value('overtime_hours', $overtime->hours)?>">
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('overtime_hours'); ?>
                        </span>
                    </div>

                    <div class="form-group">
                        <label for="overtime_total_amount" class="col-sm-2 control-label">
                            <?=$this->lang->line("overtime_total_amount")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input readonly="readonly" type="text" class="form-control" id="overtime_total_amount" name="overtime_total_amount"
                                value="<?=set_value('overtime_total_amount', $overtime->total_amount)?>">
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('overtime_total_amount'); ?>
                        </span>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-8">
                            <input type="submit" class="btn btn-success" value="<?=$this->lang->line("update_overtime")?>">
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">

$('#overtime_date').datetimepicker({
    format: 'DD-MM-YYYY hh:mm A'
});


$('.select2').select2();
$('#roleId').click(function(event) {
    var roleId = $(this).val();
    if(roleId === '0') {
        $('#roleId').val(0);
    } else {
        $.ajax({
            type: 'POST',
            url: "<?=base_url('overtime/userscall')?>",
            data: "roleId=" + roleId,
            dataType: "html",
            success: function(data) {
               $('#userId').html(data);
            }
        });
    }
});




    var roleId = $('#roleId').val();
    
    $.ajax({
        type:'POST',
        url:'<?=base_url('overtime/userscall')?>',
        data:{'roleId':roleId},
        success:function(data) {
            $('#userId').html(data);
               $('#userId').val(<?=$overtime->userID?>); 
           
        }
    });


$(document).on('keyup, change', '#overtime_hours, #userId', function() {
    var roleId      = $('#roleId').val();
    var userId      = $('#userId').val();
    var hours       = $('#overtime_hours').val();
    if(roleId === '0' || userId === '0') {
        $('#overtime_total_amount').val(0);
    } else {
        $.ajax({
            type: 'POST',
            url: "<?=base_url('overtime/get_overtime_amount')?>",
            data: { "roleId" : roleId, "userId" : userId, "hours" : hours },
            dataType: "html",
            success: function(data) {
               $('#overtime_total_amount').val(data);
            }
        });
    }
});

</script>