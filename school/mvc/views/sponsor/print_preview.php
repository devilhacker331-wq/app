<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <div class="profileArea">
        <?php featureheader($siteinfos);?>
        <div class="mainArea">
            <div class="areaBottom">
                <h3><?=$this->lang->line('sponsor_sponsor_information')?></h3>
                <table class="table table-bordered">
                    <?php if($sponsor->type == 'individual') { ?>
                        <tr>
                            <td width="30%"><?=$this->lang->line('sponsor_title')?></td>
                            <td width="70%"><?=isset($titles[$sponsor->title]) ? $titles[$sponsor->title] : ''?></td>
                        </tr>
                        <tr>
                            <td width="30%"><?=$this->lang->line('sponsor_person_name')?></td>
                            <td width="70%"><?=$sponsor->name;?></td>
                        </tr>
                        <tr>
                            <td width="30%"><?=$this->lang->line('sponsor_organisation_name')?></td>
                            <td width="70%"><?=$sponsor->organisation_name;?></td>
                        </tr>
                    <?php } else { ?>
                        <tr>
                            <td width="30%"><?=$this->lang->line('sponsor_sponsor_organisation_name')?></td>
                            <td width="70%"><?=$sponsor->name;?></td>
                        </tr>
                        <tr>
                            <td width="30%"><?=$this->lang->line('sponsor_title')?></td>
                            <td width="70%"><?=isset($titles[$sponsor->title]) ? $titles[$sponsor->title] : ''?></td>
                        </tr>
                        <tr>
                            <td width="30%"><?=$this->lang->line('sponsor_contact_person_name')?></td>
                            <td width="70%"><?=$sponsor->organisation_name;?></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td width="30%"><?=$this->lang->line('sponsor_email')?></td>
                        <td width="70%"><?=$sponsor->email;?></td>
                    </tr>
                    <tr>
                        <td width="30%"><?=$this->lang->line('sponsor_phone')?></td>
                        <td width="70%"><?=$sponsor->phone;?></td>
                    </tr>
                    <tr>
                        <td width="30%"><?=$this->lang->line('sponsor_country')?></td>
                        <td width="70%"><?=isset($allcountry[$sponsor->country]) ? $allcountry[$sponsor->country] : ''?></td>
                    </tr>
                    <tr>
                        <td width="30%"><?=$this->lang->line('sponsor_create_date')?></td>
                        <td width="70%"><?=date("d M Y", strtotime((string) $sponsor->create_date));?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <?php featurefooter($siteinfos)?>
</body>
</html>
