<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-retweet"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_overtime')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">

                <?php if(permissionChecker('overtime_add')) { ?>
                    <h5 class="page-header">
                        <a href="<?php echo base_url('overtime/add') ?>">
                            <i class="fa fa-plus"></i> 
                            <?=$this->lang->line('add_title')?>
                        </a>
                    </h5>
                <?php } ?>

                <div id="hide-table">
                    <table id="example1" class="table table-striped table-bordered table-hover dataTable no-footer">
                        <thead>
                            <tr>
                                <th class="col-sm-1"><?=$this->lang->line('slno')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('overtime_role')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('overtime_user')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('overtime_date')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('overtime_hours')?></th>
                                <th class="col-sm-2"><?=$this->lang->line('overtime_total_amount')?></th>
                                <?php if(permissionChecker('overtime_edit') || permissionChecker('overtime_delete')) { ?>
                                <th class="col-sm-2"><?=$this->lang->line('action')?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(customCompute($overtimes)) {$i = 1; foreach($overtimes as $overtime) { ?>
                                <tr>
                                    <td data-title="<?=$this->lang->line('slno')?>">
                                        <?=$i?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('overtime_role')?>">
                                    <?=isset($roles[$overtime->usertypeID]) ? $roles[$overtime->usertypeID] : ''?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('overtime_user')?>">
                                        <?=isset($allUsers[$overtime->usertypeID][$overtime->userID]) ? $allUsers[$overtime->usertypeID][$overtime->userID]->name : '' ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('overtime_date')?>">
                                        <?=date('d-M-Y h:i A', strtotime((string) $overtime->date))?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('overtime_hours')?>">
                                        <?php echo $overtime->hours; ?>
                                    </td>
                  
                                    <td data-title="<?=$this->lang->line('overtime_total_amount')?>">
                                        <?php echo $overtime->total_amount; ?>
                                    </td>
                                    <?php if(permissionChecker('overtime_edit') || permissionChecker('overtime_delete')) { ?>
                                        <td data-title="<?=$this->lang->line('action')?>">
                                            <?=btn_edit('overtime/edit/'.$overtime->id, $this->lang->line('edit')) ?>
                                            <?=btn_delete('overtime/delete/'.$overtime->id, $this->lang->line('delete')) ?>
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php $i++; }} ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>