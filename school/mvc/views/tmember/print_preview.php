<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <div class="profileArea">
        <?php featureheader($siteinfos);?>
        <div class="mainArea">
            <div class="areaTop">
                <div class="studentImage">
                    <img class="studentImg" src="<?=pdfimagelink($student->photo)?>" alt="">
                </div>
                <div class="studentProfile">
                    <div class="singleItem">
                        <div class="single_label"><?=$this->lang->line('tmember_name')?></div>
                        <div class="single_value">: <?=$student->name?></div>
                    </div>
                    <div class="singleItem">
                        <div class="single_label"><?=$this->lang->line('tmember_type')?></div>
                        <div class="single_value">: <?=isset($usertypes[$student->usertypeID]) ? $usertypes[$student->usertypeID] : ''?></div>
                    </div>
                    <div class="singleItem">
                        <div class="single_label"><?=$this->lang->line('tmember_registerNO')?></div>
                        <div class="single_value">: <?=$student->srregisterNO?></div>
                    </div>
                    <div class="singleItem">
                        <div class="single_label"><?=$this->lang->line('tmember_roll')?></div>
                        <div class="single_value">: <?=$student->srroll?></div>
                    </div>
                    <div class="singleItem">
                        <div class="single_label"><?=$this->lang->line('tmember_classes')?></div>
                        <div class="single_value">: <?=customCompute($classes) ? $classes->classes : ''?></div>
                    </div>
                    <div class="singleItem">
                        <div class="single_label"><?=$this->lang->line('tmember_section')?></div>
                        <div class="single_value">: <?=customCompute($section) ? $section->section : ''?></div>
                    </div>
                </div>
            </div>
            <div class="areaBottom">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td width="30%"><?=$this->lang->line("tmember_dob")?></td>
                            <td width="70%"><?php if($student->dob) { echo date("d M Y", strtotime((string) $student->dob)); } ?></td>
                        </tr>
                        <tr>
                            <td width="30%"><?=$this->lang->line("tmember_sex")?></td>
                            <td width="70%"><?=$student->sex?></td>
                        </tr>
                        <tr>
                            <td width="30%"><?=$this->lang->line("tmember_email")?></td>
                            <td width="70%"><?=$student->email?></td>
                        </tr>
                        <tr>
                            <td width="30%"><?=$this->lang->line("tmember_phone")?></td>
                            <td width="70%"><?=$student->phone; ?></td>
                        </tr>
                        <tr>
                            <td width="30%"><?=$this->lang->line("tmember_route_name")?></td>
                            <td width="70%"><?=customCompute($transport) ? $transport->route : 'N/A'?></td>
                        </tr>
                        <tr>
                            <td width="30%"><?=$this->lang->line("tmember_tfee")?></td>
                            <td width="70%"><?=customCompute($tmember) ? $tmember->tbalance : 'N/A'?></td>
                        </tr>
                        <tr>
                            <td width="30%"><?=$this->lang->line("tmember_joindate")?></td>
                            <td width="70%"><?php if(customCompute($tmember)) { if($tmember->tjoindate) { echo date("d M Y", strtotime((string) $tmember->tjoindate)); } } else { echo 'N/A'; }   ?></td>
                        </tr>
                        <tr>
                            <td width="30%"><?=$this->lang->line("tmember_religion")?></td>
                            <td width="70%"><?=$student->religion?></td>
                        </tr>
                        <tr>
                            <td width="30%"><?=$this->lang->line("tmember_bloodgroup")?></td>
                            <td width="70%"><?php if(isset($allbloodgroup[$student->bloodgroup])) { echo $student->bloodgroup; } ?></td>
                        </tr>
                        <tr>
                            <td width="30%"><?=$this->lang->line("tmember_address")?></td>
                            <td width="70%"><?=$student->address?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php featurefooter($siteinfos);?>
</body>
</html>