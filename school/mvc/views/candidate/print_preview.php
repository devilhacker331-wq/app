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
          <img class="studentImg" src="<?=pdfimagelink($profile->photo)?>" alt="">
        </div>
        <div class="studentProfile">
          <div class="singleItem">
            <div class="single_label"><?=$this->lang->line('candidate_name')?></div>
            <div class="single_value">: <?=$profile->srname?></div>
          </div>
          <div class="singleItem">
            <div class="single_label"><?=$this->lang->line('candidate_type')?></div>
            <div class="single_value">: <?=isset($usertypes[$profile->usertypeID]) ? $usertypes[$profile->usertypeID] : ''?></div>
          </div>
          <div class="singleItem">
            <div class="single_label"><?=$this->lang->line('candidate_registerNO')?></div>
            <div class="single_value">: <?=$profile->srregisterNO?></div>
          </div>
          <div class="singleItem">
            <div class="single_label"><?=$this->lang->line('candidate_roll')?></div>
            <div class="single_value">: <?=$profile->srroll?></div>
          </div>
          <div class="singleItem">
            <div class="single_label"><?=$this->lang->line('candidate_classes')?></div>
            <div class="single_value">: <?=customCompute($classes) ? $classes->classes : ''?></div>
          </div>
          <div class="singleItem">
            <div class="single_label"><?=$this->lang->line('candidate_section')?></div>
            <div class="single_value">: <?=customCompute($section) ? $section->section : ''?></div>
          </div>
        </div>
      </div>
      <div class="areaBottom">
        <table class="table table-bordered">
          <tr>
            <td width="30%"><?=$this->lang->line('candidate_religion')?></td>
            <td width="70%"><?=$profile->religion?></td>
          </tr>
          <tr>
            <td width="30%"><?=$this->lang->line('candidate_verified_by')?></td>
            <td width="70%"><?=$candidate->verified_by?></td>
          </tr>
          <tr>
            <td width="30%"><?=$this->lang->line('candidate_date_of_verification')?></td>
            <td width="70%"><?=date('d M Y', strtotime((string) $candidate->date_verification))?></td>
          </tr>
          <tr>
            <td width="30%"><?=$this->lang->line('candidate_email')?></td>
            <td width="70%"><?=$profile->email?></td>
          </tr>
          <tr>
            <td width="30%"><?=$this->lang->line('candidate_phone')?></td>
            <td width="70%"><?=$profile->phone?></td>
          </tr>
          <tr>
            <td width="30%"><?=$this->lang->line('candidate_address')?></td>
            <td width="70%"><?=$profile->address?></td>
          </tr>
          <tr>
            <td width="30%"><?=$this->lang->line('candidate_username')?></td>
            <td width="70%"><?=$profile->username?></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
  <?php featurefooter($siteinfos);?>
</body>
</html>