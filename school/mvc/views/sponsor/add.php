<div class="box">
    <div class="box-header">
        <h3 class="box-title"><i class="fa fa-odnoklassniki"></i> <?=$this->lang->line('panel_title')?></h3>
        <ol class="breadcrumb">
            <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
            <li><a href="<?=base_url("sponsor/index")?>"><?=$this->lang->line('menu_sponsor')?></a></li>
            <li class="active"><?=$this->lang->line('menu_add')?> <?=$this->lang->line('menu_sponsor')?></li>
        </ol>
    </div><!-- /.box-header -->
    <!-- form start -->
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12">
                <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data">
                    <input class="form-check-input  checked" type="radio" style="width: 20px !important;height: 21px !important;" name="checked" id="individual"  value="individual" <?=($checked=='individual') ? 'checked' : ''?>>
                    <label class="form-check-label" for="individual" style="font-size: 18px;font-weight: bold;"><?=$this->lang->line("sponsor_individual")?></label>
                    <?php
                        if (form_error('title')) {
    echo "<div class='form-group has-error' >";
} else {
    echo "<div class='form-group' >";
}
                    ?>
                        <label for="title" class="col-sm-2 control-label">
                            <?=$this->lang->line("sponsor_title")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <?php
                                echo form_dropdown("title", $titles, set_value("title"), "id='title' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label"><?php echo form_error('title'); ?></span>
                    </div>

                    <?php
                        if (form_error('sponsor_person_name')) {
                                echo "<div class='form-group has-error' >";
                            } else {
                                echo "<div class='form-group' >";
                            }
                    ?>
                        <label for="sponsor_person_name" class="col-sm-2 control-label">
                            <?=$this->lang->line("sponsor_person_name")?>  <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="sponsor_person_name" name="sponsor_person_name" value="<?=set_value('sponsor_person_name')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                                <?php echo form_error('sponsor_person_name'); ?>
                            </span>
                    </div>

                    <?php
                        if (form_error('organisation_name')) {
                        echo "<div class='form-group has-error' >";
                    } else {
                        echo "<div class='form-group' >";
                    }
                    ?>
                        <label for="organisation_name" class="col-sm-2 control-label">
                            <?=$this->lang->line("sponsor_organisation_name")?>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="organisation_name" name="organisation_name" value="<?=set_value('organisation_name')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                             <?php echo form_error('organisation_name'); ?>
                        </span>
                    </div>
                    <br/>

                    <input id="organisation" class="form-check-input  checked" type="radio" style="width: 20px !important;height: 21px !important;" name="checked"  value="organisation" <?=($checked=='organisation') ? 'checked' : ''?>>
                    <label class="form-check-label" for="organisation" style="font-size: 18px;font-weight: bold;">
                        <?=$this->lang->line("sponsor_organisation")?>
                    </label>

                    <?php
                        if (form_error('sponsor_organisation_name')) {
                        echo "<div class='form-group has-error' >";
                    } else {
                        echo "<div class='form-group' >";
                    }
                    ?>
                        <label for="sponsor_organisation_name" class="col-sm-2 control-label">
                            <?=$this->lang->line("sponsor_sponsor_organisation_name")?>  <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="sponsor_organisation_name" name="sponsor_organisation_name" value="<?=set_value('sponsor_organisation_name')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                             <?php echo form_error('sponsor_organisation_name'); ?>
                         </span>
                    </div>

                    <?php
                        if (form_error('organisation_title')) {
                        echo "<div class='form-group has-error' >";
                    } else {
                        echo "<div class='form-group' >";
                    }
                    ?>
                        <label for="organisation_title" class="col-sm-2 control-label">
                            <?=$this->lang->line("sponsor_title")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <?php echo form_dropdown("organisation_title", $titles, set_value("organisation_title"), "id='organisation_title' class='form-control select2'"); ?>
                        </div>
                        <span class="col-sm-4 control-label"><?php echo form_error('organisation_title'); ?></span>
                    </div>

                    <?php
                        if (form_error('contact_person_name')) {
                        echo "<div class='form-group has-error' >";
                    } else {
                        echo "<div class='form-group' >";
                    }
                    ?>
                        <label for="contact_person_name" class="col-sm-2 control-label">
                            <?=$this->lang->line("sponsor_contact_person_name")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="contact_person_name" name="contact_person_name" value="<?=set_value('contact_person_name')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('contact_person_name'); ?>
                        </span>
                    </div>

                    <?php
                        if (form_error('email')) {
                        echo "<div class='form-group has-error' >";
                    } else {
                        echo "<div class='form-group' >";
                    }
                    ?>
                        <label for="email" class="col-sm-2 control-label">
                            <?=$this->lang->line("sponsor_email")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="email" name="email" value="<?=set_value('email')?>" >
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('email'); ?>
                        </span>
                    </div>

                    <?php
                        if (form_error('phone')) {
                        echo "<div class='form-group has-error' >";
                    } else {
                        echo "<div class='form-group' >";
                    }
                    ?>
                        <label for="phone" class="col-sm-2 control-label">
                            <?=$this->lang->line("sponsor_phone")?> <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="phone" name="phone" value="<?=set_value('phone')?>">
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('phone'); ?>
                        </span>
                    </div>

                    <?php
                        if (form_error('country')) {
                        echo "<div class='form-group has-error' >";
                    } else {
                        echo "<div class='form-group' >";
                    }
                    ?>
                        <label for="country" class="col-sm-2 control-label">
                            <?=$this->lang->line("sponsor_country")?>  <span class="text-red">*</span>
                        </label>
                        <div class="col-sm-6">
                            <?php
                            $country['0'] = $this->lang->line('sponsor_select_country');
                            foreach ($allcountry as $allcountryKey => $allcountryit) {
                                $country[$allcountryKey] = $allcountryit;
                            }
                            ?>
                            <?php
                            echo form_dropdown("country", $country, set_value("country"), "id='country' class='form-control select2'");
                            ?>
                        </div>
                        <span class="col-sm-4 control-label">
                            <?php echo form_error('country'); ?>
                        </span>
                    </div>

                    <?php
                        if (form_error('photo')) {
                                echo "<div class='form-group has-error' >";
                            } else {
                                echo "<div class='form-group' >";
                            }
                    ?>
                        <label for="photo" class="col-sm-2 control-label">
                            <?=$this->lang->line("sponsor_photo")?>
                        </label>
                        <div class="col-sm-6">
                            <div class="input-group image-preview">
                                <input type="text" class="form-control image-preview-filename" disabled="disabled">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default image-preview-clear" style="display:none;">
                                        <span class="fa fa-remove"></span>
                                        <?=$this->lang->line('sponsor_clear')?>
                                    </button>
                                    <div class="btn btn-success image-preview-input">
                                        <span class="fa fa-repeat"></span>
                                        <span class="image-preview-input-title">
                                        <?=$this->lang->line('sponsor_file_browse')?></span>
                                        <input type="file" accept="image/png, image/jpeg, image/gif" name="photo"/>
                                    </div>
                                </span>
                            </div>
                        </div>

                        <span class="col-sm-4">
                            <?php echo form_error('photo'); ?>
                        </span>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-8">
                            <input type="submit" class="btn btn-success" value="<?=$this->lang->line("add_sponsor")?>" >
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $('.select2').select2();
   var checked =  "<?=$checked?>";
    if(checked == 'organisation'){
        $('#sponsor_person_name').attr("disabled", true)
        $('#title').attr("disabled", true)
        $('#organisation_name').attr("disabled", true)
    }else {
        $('#sponsor_organisation_name').attr("disabled", true)
        $('#organisation_title').attr("disabled", true)
        $('#contact_person_name').attr("disabled", true)
    }


    $('input[type=radio][name=checked]').change(function() {
        if (this.value == 'organisation') {
            $('#sponsor_organisation_name').attr("disabled", false)
            $('#organisation_title').attr("disabled", false)
            $('#contact_person_name').attr("disabled", false)

            $('#sponsor_person_name').attr("disabled", true)
            $('#title').attr("disabled", true)
            $('#organisation_name').attr("disabled", true)

        }else{
            $('#sponsor_person_name').attr("disabled", false)
            $('#title').attr("disabled", false)
            $('#organisation_name').attr("disabled", false)

            $('#sponsor_organisation_name').attr("disabled", true)
            $('#organisation_title').attr("disabled", true)
            $('#contact_person_name').attr("disabled", true)

        }
    });


$(document).on('click', '#close-preview', function(){
    $('.image-preview').popover('hide');
    // Hover befor close the preview
    $('.image-preview').hover(
        function () {
           $('.image-preview').popover('show');
           $('.content').css('padding-bottom', '100px');
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
        $(".image-preview-input-title").text("<?=$this->lang->line('parents_file_browse')?>");
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
            $(".image-preview-input-title").text("<?=$this->lang->line('parents_file_browse')?>");
            $(".image-preview-clear").show();
            $(".image-preview-filename").val(file.name);
            img.attr('src', e.target.result);
            $(".image-preview").attr("data-content",$(img)[0].outerHTML).popover("show");
            $('.content').css('padding-bottom', '100px');
        }
        reader.readAsDataURL(file);
    });
});

</script>

