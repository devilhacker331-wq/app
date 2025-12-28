<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
</head>
<body>
<div class="box-body">
    <div class="row">
        <div class="col-sm-12">
            <?=reportheader($siteinfos, $schoolyearsessionobj, true)?>
        </div>

        <h3 style="margin-bottom: 0px;"><?=$this->lang->line('overtimereport_report_for')?> - <?=$this->lang->line('overtimereport_overtime')?></h3>

        <?php if($fromdate != 0 && $todate != 0 ) { ?>
            <div class="col-sm-12">
                <h5 class="pull-left" style="margin-top:5px">
                    <?=$this->lang->line('overtimereport_fromdate')?> : <?=date('d M Y', $fromdate)?></p>
                </h5>
                <h5 class="pull-right" style="margin-top:5px">
                    <?=$this->lang->line('overtimereport_todate')?> : <?=date('d M Y', $todate)?></p>
                </h5>
            </div>
        <?php } elseif($usertypeID != 0 && $userID != 0 ) { ?>
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
        <?php } elseif($usertypeID != 0) { ?>
            <div class="col-sm-12">
                <h5 class="pull-left" style="margin-top:5px">
                    <?php
                        echo $this->lang->line('overtimereport_role')." : ";
                        echo $usertypes[$usertypeID];
                    ?>
                </h5>
            </div>
        <?php } elseif($usertypeID == 0) { ?>
            <div class="col-sm-12">
                <h5 class="pull-left" style="margin-top:5px">
                    <?php
                        echo $this->lang->line('overtimereport_role')." : ";
                        echo $this->lang->line('overtimereport_alluser');
                    ?>
                </h5>
            </div>
        <?php } ?>

        <div class="col-sm-12">
        <?php if(customCompute($overtimes)) { ?>
            <table>
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
                foreach($overtimes as $overtime) { 
                    $totalOvertimeAmount = $overtime->total_amount;?>
                    <tr>
                        <td><?=$i?></td>
                        <td>
                            <?=isset($usertypes[$overtime->usertypeID]) ? $usertypes[$overtime->usertypeID] : ''?>
                        </td>
                        <td>
                            <?=isset($allUsers[$overtime->usertypeID][$overtime->userID]) ? $allUsers[$overtime->usertypeID][$overtime->userID]->name : '' ?>
                        </td>
                        <td>
                            <?=date('d-M-Y h:i A', strtotime((string) $overtime->date))?>
                        </td>
                        <td>
                            <?=$overtime->hours;?>
                        </td>
                        <td>
                            <?=$overtime->amount;?>
                        </td>
                        <td>
                            <?=$overtime->total_amount;?>
                        </td>
                    </tr>
                    <?php $i++; } ?>
                <tr>
                    <td colspan="6" class="text-bold text-right"><?=$this->lang->line('overtimereport_grand_total')?> <?=empty($siteinfos->currency_code) ? '' : "(".$siteinfos->currency_code.")"?></td>
                    <td class="text-bold"><?=number_format($totalOvertimeAmount,2)?></td>
                </tr>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="notfound">
                <b><?=$this->lang->line('overtimereport_data_not_found'); ?></b>
            </div>
        <?php } ?>
        </div><!-- row -->
        <div class="col-sm-12 text-center footerAll">
            <?=reportfooter($siteinfos, $schoolyearsessionobj, true)?>
        </div>
    </div><!-- Body -->
</div><!-- Body -->
</body>
</html>