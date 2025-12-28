<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-asterisk"></i>
            <?= $this->lang->line('panel_title') ?>
        </h3>


        <ol class="breadcrumb">
            <li><a href="<?= base_url("dashboard/index") ?>"><i class="fa fa-laptop"></i>
                    <?= $this->lang->line('menu_dashboard') ?>
                </a></li>
            <li class="active">
                <?= $this->lang->line('menu_frontend_setting') ?>
            </li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->

    <style type="text/css">
        .setting-fieldset {
            border: 1px solid #DBDEE0 !important;
            padding: 15px !important;
            margin: 0 0 25px 0 !important;
            box-shadow: 0px 0px 0px 0px #000;
        }

        .setting-legend {
            font-size: 1.1em !important;
            font-weight: bold !important;
            text-align: left !important;
            width: auto;
            color: #428BCA;
            padding: 5px 15px;
            border: 1px solid #DBDEE0 !important;
            margin: 0px;
        }
    </style>


    <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data">
        <div class="box-body">
            <fieldset class="setting-fieldset">
                <legend class="setting-legend">
                    <?= $this->lang->line('frontend_setting_frontend_configaration') ?>
                </legend>
                <div class="row">

                    <div class="col-sm-4">
                        <div class="form-group <?php if (form_error('login_menu_status')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="login_menu_status">
                                    <?= $this->lang->line("frontend_setting_login_menu_status") ?>
                                    <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="Enable/Disable login menu for frontend top menu"></i>
                                </label>
                                <?php
                                $loginMenuStatusArray[1] = $this->lang->line('frontend_setting_enable');
                                $loginMenuStatusArray[0] = $this->lang->line('frontend_setting_disable');
                                echo form_dropdown("login_menu_status", $loginMenuStatusArray, set_value("login_menu_status", $frontend_setting->login_menu_status), "id='login_menu_status' class='form-control select2'");
                                ?>
                                <span class="control-label">
                                    <?php echo form_error('login_menu_status'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group <?php if (form_error('teacher_email_status')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="teacher_email_status">
                                    <?= $this->lang->line("frontend_setting_teacher_email") ?>
                                    <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="Enable/Disable teacher email for frontend teahcer list"></i>
                                </label>
                                <?php
                                $teacherEmailStatusArray[1] = $this->lang->line('frontend_setting_enable');
                                $teacherEmailStatusArray[0] = $this->lang->line('frontend_setting_disable');
                                echo form_dropdown("teacher_email_status", $teacherEmailStatusArray, set_value("teacher_email_status", $frontend_setting->teacher_email_status), "id='teacher_email_status' class='form-control select2'");
                                ?>
                                <span class="control-label">
                                    <?php echo form_error('teacher_email_status'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group <?php if (form_error('teacher_phone_status')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="teacher_phone_status">
                                    <?= $this->lang->line("frontend_setting_teacher_phone") ?>
                                    <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="Enable/Disable teacher phone for frontend teahcer list"></i>
                                </label>
                                <?php
                                $teacherPhoneStatusArray[1] = $this->lang->line('frontend_setting_enable');
                                $teacherPhoneStatusArray[0] = $this->lang->line('frontend_setting_disable');
                                echo form_dropdown("teacher_phone_status", $teacherPhoneStatusArray, set_value("teacher_phone_status", $frontend_setting->teacher_phone_status), "id='teacher_phone_status' class='form-control select2'");
                                ?>
                                <span class="control-label">
                                    <?php echo form_error('teacher_phone_status'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group <?= form_error('online_admission_status') ? 'has-error' : '' ?>">
                            <div class="col-sm-12">
                                <label for="online_admission_status">
                                    <?= $this->lang->line("frontend_setting_onlineadmission") ?>&nbsp;<i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="right"
                                        title="Enable/Disable for Online Admission"></i>
                                </label>
                                <?php
                                $onlineadmissionArray[1] = $this->lang->line('frontend_setting_enable');
                                $onlineadmissionArray[0] = $this->lang->line('frontend_setting_disable');
                                echo form_dropdown("online_admission_status", $onlineadmissionArray, set_value("online_admission_status", $frontend_setting->online_admission_status), "id='online_admission_status' class='form-control select2'");
                                ?>
                                <span class="control-label">
                                    <?= form_error('online_admission_status'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group <?= form_error('description') ? 'has-error' : '' ?>">
                            <div class="col-sm-12">
                                <label for="description">
                                    <?= $this->lang->line("frontend_setting_description") ?> <i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="right"
                                        title="Set frontend footer short description"></i>
                                </label>
                                <textarea class="form-control" style="resize:none;" id="description"
                                    name="description"><?= set_value('description', $frontend_setting->description) ?></textarea>
                                <span class="control-label">
                                    <?= form_error('description'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="setting-fieldset">
                <legend class="setting-legend">
                    <?= $this->lang->line('frontend_setting_hero_section') ?>
                </legend>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group <?php if (form_error('hero_section_video')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="hero_section_video">
                                    <?= $this->lang->line("frontend_setting_hero_section_video") ?>
                                    <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="Hero Section Video URL"></i>
                                </label>
                                <input type="text" class="form-control" id="hero_section_video"
                                    name="hero_section_video"
                                    value="<?= set_value('hero_section_video', isset($frontend_setting->hero_section_video) ? $frontend_setting->hero_section_video : null) ?>">
                                <span class="control-label">
                                    <?php echo form_error('hero_section_video'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group <?php if (form_error('hero_section_since')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="hero_section_since">
                                    <?= $this->lang->line("frontend_setting_hero_section_since") ?> <i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="Hero Section Since"></i>
                                </label>
                                <input type="number" class="form-control" id="hero_section_since"
                                    name="hero_section_since" min="1900" max="2099" step="1"
                                    value="<?= set_value('hero_section_since', isset($frontend_setting->hero_section_since) ? $frontend_setting->hero_section_since : null) ?>">
                                <span class="control-label">
                                    <?php echo form_error('hero_section_since'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="setting-fieldset">
                <legend class="setting-legend">
                    <?= $this->lang->line('frontend_setting_google_map_setting') ?>
                </legend>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group <?php if (form_error('embed_map')) { echo 'has-error'; } ?>">
                            <div class="col-sm-12">
                                <label for="embed_map">
                                    <?= $this->lang->line("frontend_setting_embed_map") ?>
                                    <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="Google Map Iframe inner URL"></i>
                                </label>
                                <input type="text" class="form-control" id="embed_map"
                                    name="embed_map"
                                    value="<?= set_value('embed_map', isset($frontend_setting->embed_map) ? $frontend_setting->embed_map : null) ?>">
                                <span class="control-label">
                                    <?php echo form_error('embed_map'); ?>
                                </span>
                            </div>
                        </div>
                    </div> 
                </div>
            </fieldset>

            <fieldset class="setting-fieldset">
                <legend class="setting-legend">
                    <?= $this->lang->line('frontend_setting_feature') ?>
                </legend>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group <?php if (form_error('message_one')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="message_one">
                                    <?= $this->lang->line("frontend_setting_feature_one") ?> <i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="message One"></i>
                                </label>
                                <textarea type="text" class="form-control" id="message_one"
                                    name="message_one"> <?= set_value('message_one', isset($frontend_setting->message_one) ? $frontend_setting->message_one : null) ?></textarea>
                                <span class="control-label">
                                    <?php echo form_error('message_one'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group <?php if (form_error('message_two')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="message_two">
                                    <?= $this->lang->line("frontend_setting_feature_two") ?> <i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="message Two"></i>
                                </label>
                                <textarea type="text" class="form-control" id="message_two"
                                    name="message_two"><?= set_value('message_two', isset($frontend_setting->message_two) ? $frontend_setting->message_two : null) ?></textarea>
                                <span class="control-label">
                                    <?php echo form_error('message_two'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group <?php if (form_error('message_three')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="message_three">
                                    <?= $this->lang->line("frontend_setting_feature_three") ?> <i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="message Three"></i>
                                </label>
                                <textarea type="text" class="form-control" id="message_three"
                                    name="message_three"><?= set_value('message_three', isset($frontend_setting->message_three) ?  $frontend_setting->message_three : null) ?></textarea>
                                <span class="control-label">
                                    <?php echo form_error('message_three'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group <?php if (form_error('message_four')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="message_four">
                                    <?= $this->lang->line("frontend_setting_feature_four") ?> <i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="message Four"></i>
                                </label>
                                <textarea type="text" class="form-control" id="message_four"
                                    name="message_four"><?= set_value('message_four', isset($frontend_setting->message_four) ? $frontend_setting->message_four : null) ?></textarea>
                                <span class="control-label">
                                    <?php echo form_error('message_four'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
            
            <fieldset class="setting-fieldset">
                <legend class="setting-legend">
                    <?= $this->lang->line('frontend_setting_admission_section') ?>
                </legend>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group <?php if (form_error('admission_title')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="admission_title">
                                    <?= $this->lang->line("frontend_setting_admission_title") ?> <i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="<?= $this->lang->line("frontend_setting_admission_title") ?>"></i>
                                </label>
                                <input type="text" class="form-control" id="admission_title" name="admission_title"
                                    value="<?= set_value('admission_title', isset($frontend_setting->admission_title) ? $frontend_setting->admission_title : null) ?>">
                                <span class="control-label">
                                    <?php echo form_error('admission_title'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group <?php if (form_error('admission_description')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="admission_description">
                                    <?= $this->lang->line("frontend_setting_admission_description") ?> <i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="<?= $this->lang->line("frontend_setting_admission_description") ?>"></i>
                                </label>
                                <input type="text" class="form-control" id="admission_description" name="admission_description"
                                    value="<?= set_value('admission_description', isset($frontend_setting->admission_description) ? $frontend_setting->admission_description : null) ?>">
                                <span class="control-label">
                                    <?php echo form_error('admission_description'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
              
                </div>
            </fieldset>

            <fieldset class="setting-fieldset">
                <legend class="setting-legend">
                    <?= $this->lang->line('frontend_setting_principle_message') ?>
                </legend>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group <?php if (form_error('principle_name')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="principle_name">
                                    <?= $this->lang->line("frontend_setting_principle_name") ?> <i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="Principle Name"></i>
                                </label>
                                <input type="text" class="form-control" id="principle_name" name="principle_name"
                                    value="<?= set_value('principle_name', isset($frontend_setting->principle_name) ? $frontend_setting->principle_name : null) ?>">
                                <span class="control-label">
                                    <?php echo form_error('principle_name'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group <?= form_error('photo') ? 'has-error' : '' ?>">
                            <div class="col-sm-12">
                                <label for="photo"><?= $this->lang->line("frontend_setting_principle_photo") ?>&nbsp;<i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="right"
                                        title="Set organization logo here"></i>
                                </label>
                                <div class="input-group image-preview">
                                    <input type="text" name="photo" class="form-control image-preview-filename" disabled="disabled">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default image-preview-clear"
                                            style="display:none;">
                                            <span class="fa fa-remove"></span>
                                            <?= $this->lang->line('frontend_setting_clear') ?>
                                        </button>
                                        <div class="btn btn-success image-preview-input">
                                            <span class="fa fa-repeat"></span>
                                            <span class="image-preview-input-title">
                                                <?= $this->lang->line('setting_file_browse') ?></span>
                                            <input type="file" accept="image/png, image/jpeg, image/gif" name="photo" />
                                        </div>
                                    </span>
                                </div>
                                <span class="control-label">
                                    <?= form_error('photo'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group <?php if (form_error('principle_message')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="principle_message">
                                    <?= $this->lang->line("frontend_setting_principle_message") ?> <i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="Principle message"></i>
                                </label>
                                <textarea type="text" class="form-control" id="principle_message"
                                    name="principle_message"><?= set_value('principle_message', isset($frontend_setting->principle_message) ? $frontend_setting->principle_message : null) ?></textarea>
                                <span class="control-label">
                                    <?php echo form_error('principle_message'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="setting-fieldset">
                <legend class="setting-legend">
                    <?= $this->lang->line('frontend_setting_social') ?>
                </legend>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group <?php if (form_error('facebook')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="facebook">
                                    <?= $this->lang->line("frontend_setting_facebook") ?> <i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="Facebook Link for frontend"></i>
                                </label>
                                <input type="text" class="form-control" id="facebook" name="facebook"
                                    value="<?= set_value('facebook', $frontend_setting->facebook) ?>">
                                <span class="control-label">
                                    <?php echo form_error('facebook'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group <?php if (form_error('twitter')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="twitter">
                                    <?= $this->lang->line("frontend_setting_twitter") ?> <i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="Twitter Link for frontend"></i>
                                </label>
                                <input type="text" class="form-control" id="twitter" name="twitter"
                                    value="<?= set_value('twitter', $frontend_setting->twitter) ?>">
                                <span class="control-label">
                                    <?php echo form_error('twitter'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group <?php if (form_error('linkedin')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="linkedin">
                                    <?= $this->lang->line("frontend_setting_linkedin") ?> <i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="Linkedin Link for frontend"></i>
                                </label>
                                <input type="text" class="form-control" id="linkedin" name="linkedin"
                                    value="<?= set_value('linkedin', $frontend_setting->linkedin) ?>">
                                <span class="control-label">
                                    <?php echo form_error('linkedin'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group <?php if (form_error('youtube')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="youtube">
                                    <?= $this->lang->line("frontend_setting_youtube") ?> <i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="Youtube Link for frontend"></i>
                                </label>
                                <input type="text" class="form-control" id="youtube" name="youtube"
                                    value="<?= set_value('youtube', $frontend_setting->youtube) ?>">
                                <span class="control-label">
                                    <?php echo form_error('youtube'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div class="form-group <?php if (form_error('google')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="google">
                                    <?= $this->lang->line("frontend_setting_google") ?> <i class="fa fa-question-circle"
                                        data-toggle="tooltip" data-placement="bottom"
                                        title="Google + Link for frontend"></i>
                                </label>
                                <input type="text" class="form-control" id="google" name="google"
                                    value="<?= set_value('google', $frontend_setting->google) ?>">
                                <span class="col-sm-4 control-label">
                                    <?php echo form_error('google'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="setting-fieldset">
                <legend class="setting-legend">
                    <?= $this->lang->line('frontend_setting_school_history') ?>
                </legend>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group <?php if (form_error('school_origin')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="school_origin">
                                    <?= $this->lang->line("frontend_setting_school_origin") ?> <i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="message One"></i>
                                </label>
                                <textarea type="text" class="form-control" id="school_origin"
                                    name="school_origin"><?= set_value('school_origin', isset($frontend_setting->school_origin) ? $frontend_setting->school_origin : null) ?></textarea>
                                <span class="control-label">
                                    <?php echo form_error('school_origin'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group <?php if (form_error('school_campus')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="school_campus">
                                    <?= $this->lang->line("frontend_setting_school_campus") ?> <i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="message Two"></i>
                                </label>
                                <textarea type="text" class="form-control" id="school_campus"
                                    name="school_campus"><?= set_value('school_campus', isset($frontend_setting->school_campus) ? $frontend_setting->school_origin : null) ?></textarea>
                                <span class="control-label">
                                    <?php echo form_error('school_campus'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group <?php if (form_error('school_success')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="school_success">
                                    <?= $this->lang->line("frontend_setting_school_success") ?> <i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="message Three"></i>
                                </label>
                                <textarea type="text" class="form-control" id="school_success"
                                    name="school_success"><?= set_value('school_success', isset($frontend_setting->school_success) ? $frontend_setting->school_success : null) ?></textarea>
                                <span class="control-label">
                                    <?php echo form_error('school_success'); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group <?php if (form_error('school_vision')) {
                            echo 'has-error';
                        } ?>">
                            <div class="col-sm-12">
                                <label for="school_vision">
                                    <?= $this->lang->line("frontend_setting_school_vision") ?> <i
                                        class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom"
                                        title="message Four"></i>
                                </label>
                                <textarea type="text" class="form-control" id="school_vision"
                                    name="school_vision"><?= set_value('school_vision', isset($frontend_setting->school_vision) ? $frontend_setting->school_vision : null) ?></textarea>
                                <span class="control-label">
                                    <?php echo form_error('school_vision'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>

            <div class="form-group">
                <div class="col-sm-8">
                    <input type="submit" class="btn btn-success btn-md"
                        value="<?= $this->lang->line("update_frontend_setting") ?>">
                </div>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
    $('.select2').select2();


    $(document).on('click', '#close-preview', function(){ 
        $('.image-preview').popover('hide');
        // Hover befor close the preview
        $('.image-preview').hover(
            function () {
               $('.image-preview').popover('show');
               $('.content').css('padding-bottom', '120px');
            }, 
             function () {
               $('.image-preview').popover('hide');
               $('.content').css('padding-bottom', '20px');
            }
        );    
    });

    $(function() {
        // Create the close button
        var closebtn = $('<button/>', {
            type:"button",
            text: 'x',
            id: 'close-preview',
            style: 'font-size: initial;',
        });
        closebtn.attr("class","close pull-right");
        // Set the popover default content
        $('.image-preview').popover({
            trigger:'manual',
            html:true,
            title: "<strong>Preview</strong>"+$(closebtn)[0].outerHTML,
            content: "There's no image",
            placement:'bottom'
        });
        // Clear event
        $('.image-preview-clear').click(function(){
            $('.image-preview').attr("data-content","").popover('hide');
            $('.image-preview-filename').val("");
            $('.image-preview-clear').hide();
            $('.image-preview-input input:file').val("");
            $(".image-preview-input-title").text("<?=$this->lang->line('setting_file_browse')?>"); 
        }); 
        // Create the preview image
        $(".image-preview-input input:file").change(function (){     
            var img = $('<img/>', {
                id: 'dynamic',
                width:250,
                height:200,
                overflow:'hidden'
            });      
            var file = this.files[0];
            var reader = new FileReader();
            // Set preview image into the popover data-content
            reader.onload = function (e) {
                $(".image-preview-input-title").text("<?=$this->lang->line('frontend_setting_clear')?>");
                $(".image-preview-clear").show();
                $(".image-preview-filename").val(file.name);            
                img.attr('src', e.target.result);
                $(".image-preview").attr("data-content",$(img)[0].outerHTML).popover("show");
                $('.content').css('padding-bottom', '120px');
            }        
            reader.readAsDataURL(file);
        });  
    });

</script>