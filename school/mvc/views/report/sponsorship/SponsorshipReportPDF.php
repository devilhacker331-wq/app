<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div>
        <?=reportheader($siteinfos, $schoolyearsessionobj, true)?>
        <h3><?=$this->lang->line('sponsorshipreport_type')?> : <?=isset($types[$typeId]) ? $types[$typeId] : ''?></h3>
        <?php if(customCompute($sponsorships)) { ?>
            <table>
                <thead>
                    <tr>
                        <th><?=$this->lang->line('sponsorshipreport_slno')?></th>
                        <th><?=$this->lang->line('sponsorshipreport_candidate_name')?></th>
                        <th><?=$this->lang->line('sponsorshipreport_candidate_phone')?></th>
                        <th><?=$this->lang->line('sponsorshipreport_candidate_email')?></th>
                        <th><?=$this->lang->line('sponsorshipreport_sponsor_name')?></th>
                        <th><?=$this->lang->line('sponsorshipreport_start_date')?></th>
                        <th><?=$this->lang->line('sponsorshipreport_end_date')?></th>
                    </tr>
                </thead>
                <tbody>
                   <?php $i = 1; foreach($sponsorships as $sponsorship) { ?>
                    <tr>
                        <td><?=$i?></td>
                        <td><?=$sponsorship->cname?></td>
                        <td><?=$sponsorship->cphone?></td>
                        <td><?=$sponsorship->cemail?></td>
                        <td><?=$sponsorship->name?></td>
                        <td><?=date('d M Y', strtotime((string) $sponsorship->start_date)); ?></td>
                        <td><?=date('d M Y', strtotime((string) $sponsorship->end_date)); ?></td>
                    </tr>
                    <?php $i++; } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="notfound">
                <p><b class="text-info"><?=$this->lang->line('sponsorshipreport_data_not_found')?></b></p>
            </div>
        <?php } ?>
        <?=reportfooter($siteinfos, $schoolyearsessionobj, true)?>
    </div><!-- row -->
</body>
</html>