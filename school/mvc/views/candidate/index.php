<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-get-pocket"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_candidate')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <h5 class="page-header">
                    <?php if(permissionChecker('candidate_add')) { ?>
                        <a href="<?=base_url('candidate/add') ?>">
                            <i class="fa fa-plus"></i> 
                            <?=$this->lang->line('add_title')?>
                        </a>
                    <?php } ?>
                </h5>

                <div id="hide-table">
                    <table id="example1" class="table table-striped table-bordered table-hover dataTable no-footer">
                        <thead>
                            <tr>
                                <th class="col-lg-1"><?=$this->lang->line('slno')?></th>
                                <th class="col-lg-1"><?=$this->lang->line('candidate_photo')?></th>
                                <th class="col-lg-2"><?=$this->lang->line('candidate_name')?></th>
                                <th class="col-lg-1"><?=$this->lang->line('candidate_registerNO')?></th>
                                <th class="col-lg-1"><?=$this->lang->line('candidate_class')?></th>
                                <th class="col-lg-2"><?=$this->lang->line('candidate_sponsor')?></th>
                                 <?php if(permissionChecker('candidate_view') || permissionChecker('candidate_edit') || permissionChecker('candidate_delete')) { ?>
                                    <th class="col-lg-2"><?=$this->lang->line('action')?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(customCompute($candidates)) {$i = 1; foreach($candidates as $candidate)  { ?>
                                <tr>
                                    <td data-title="<?=$this->lang->line('slno')?>">
                                        <?=$i; ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('candidate_photo')?>">
                                        <?=profileimage($candidate->photo); ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('candidate_name')?>">
                                        <?=$candidate->srname; ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('candidate_registration_no')?>">
                                        <?=$candidate->srregisterNO; ?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('candidate_class')?>">
                                        <?=$candidate->srclasses;?>
                                    </td>
                                    <td data-title="<?=$this->lang->line('candidate_sponsor')?>">
                                        <?=isset($sponsors[$candidate->sponsorID]) ? $sponsors[$candidate->sponsorID] : ''?>
                                    </td>
                                    <?php if(permissionChecker('candidate_view') || permissionChecker('candidate_edit') || permissionChecker('candidate_delete')) { ?>
                                    <td data-title="<?=$this->lang->line('action')?>">
                                        <?=btn_view('candidate/view/'.$candidate->candidateID, $this->lang->line('view')) ?>
                                        <?=btn_edit('candidate/edit/'.$candidate->candidateID, $this->lang->line('edit')) ?>
                                        <?php 
                                            if((int)$candidate->sponsorID === 0) {
                                                echo btn_delete('candidate/delete/'.$candidate->candidateID, $this->lang->line('delete'));
                                            }
                                        ?>
                                    </td>
                                    <?php } ?>
                                </tr>
                            <?php $i++; } } ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>