<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-gg"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i>
                    <?=$this->lang->line('menu_dashboard')?></a></li>
            <li class="active"><?=$this->lang->line('menu_sponsorship')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <h5 class="page-header">
                    <?php if(permissionChecker('sponsorship_add')) { ?>
                        <a href="<?=base_url('sponsorship/add') ?>">
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
                                <th class="col-lg-2"><?=$this->lang->line('candidate_name')?></th>
                                <th class="col-lg-2"><?=$this->lang->line('sponsorship_name')?></th>
                                <th class="col-lg-1.5"><?=$this->lang->line('sponsorship_start_date')?></th>
                                <th class="col-lg-1.5"><?=$this->lang->line('sponsorship_end_date')?></th>
                                <th class="col-lg-1.5"><?=$this->lang->line('sponsorship_amount')?></th>
                                <th class="col-lg-1"><?=$this->lang->line('sponsorship_status')?></th>
                                <?php if(permissionChecker('sponsorship_edit') || permissionChecker('sponsorship_delete')) { ?>
                                    <th class="col-lg-2"><?=$this->lang->line('action')?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(customCompute($sponsorships)) {$i = 1; foreach($sponsorships as $sponsorship) { ?>
                            <tr>
                                <td data-title="<?=$this->lang->line('slno')?>">
                                    <?=$i; ?>
                                </td>
                                <td data-title="<?=$this->lang->line('candidate_name')?>">
                                    <?=$sponsorship->studentname; ?>
                                </td>
                                <td data-title="<?=$this->lang->line('sponsorship_name')?>">
                                    <?=$sponsorship->sponsorname; ?>
                                </td>
                                <td data-title="<?=$this->lang->line('sponsorship_start_date')?>">
                                    <?=date('d M Y', strtotime((string) $sponsorship->start_date)); ?>
                                </td>
                                <td data-title="<?=$this->lang->line('sponsorship_end_date')?>">
                                    <?=date('d M Y', strtotime((string) $sponsorship->end_date)); ?>
                                </td>
                                <td data-title="<?=$this->lang->line('sponsorship_amount')?>">
                                    <?=$sponsorship->amount; ?>
                                </td>
                                <td data-title="<?=$this->lang->line('sponsorship_status')?>">
                                    <?php 
                                        if (is_null($sponsorship->payment_date)) {
                                            echo "<p class='text-center text-black bg-blue'>".$this->lang->line('sponsorship_pending')."</p>";
                                        } elseif (strtotime(date('d-m-Y H:i:s')) > strtotime((string) $sponsorship->end_date)) {
                                            echo "<p class='text-center text-white bg-red'>".$this->lang->line('sponsorship_expired')."</p>";
                                        } elseif (strtotime(date('d-m-Y H:i:s')) < strtotime((string) $sponsorship->end_date)) {
                                            $date = date_diff(date_create($sponsorship->end_date), date_create(date('y-m-d')));
                                            if($date->days < 90) {
                                                echo "<p class='text-center text-black bg-yellow'>".$this->lang->line('sponsorship_expiring')."</p>";
                                            }
                                            else
                                            {
                                                echo  "<p class='text-center text-white bg-green'>".$this->lang->line('sponsorship_active')."</p>";
                                            }
                                        }
                                    ?>
                                </td>
                                <?php if(permissionChecker('sponsorship_add') || permissionChecker('sponsorship_edit') || permissionChecker('sponsorship_delete')) { ?>
                                <td data-title="<?=$this->lang->line('action')?>">
                                    <?php if(permissionChecker('sponsorship_add')) { ?>
                                        <a href="<?=base_url('sponsorship/renew/'. $sponsorship->sponsorshipID) ?>" class="btn btn-primary btn-xs mrg" data-placement="top" data-toggle="tooltip" data-original-title="Renew"><i class="fa fa-plus"></i></a>
                                    <?php } ?>
                                    <?=btn_edit('sponsorship/edit/'.$sponsorship->sponsorshipID, $this->lang->line('edit')) ?>
                                    <?php 
                                        if(is_null($sponsorship->payment_date)) {
                                            echo btn_delete('sponsorship/delete/'.$sponsorship->sponsorshipID, $this->lang->line('delete'));
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