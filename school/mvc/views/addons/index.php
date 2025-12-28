<div class="row">
    <div class="col-sm-4 ">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-crosshairs"></i> <?=$this->lang->line('panel_title')?></h3>
            </div>
            <div class="box-body">
                <form role="form" method="post" enctype="multipart/form-data">
                    <div class="<?=form_error('file') ? 'form-group has-error' : 'form-group' ?>">
                        <label for="file" class="control-label"><?=$this->lang->line("addons_file")?> <span class="text-red">*</span></label>
                        <div class="input-group image-preview">
                            <input type="text" class="form-control image-preview-filename" disabled="disabled">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default image-preview-clear" style="display:none;">
                                    <span class="fa fa-remove"></span>
                                    <?=$this->lang->line('addons_clear')?>
                                </button>
                                <div class="btn btn-success image-preview-input">
                                    <span class="fa fa-repeat"></span>
                                    <span class="image-preview-input-title">
                                    <?=$this->lang->line('addons_file_browse')?></span>
                                    <input id="uploadBtn" type="file" name="file"/>
                                </div>
                            </span>
                        </div>
                        <span class="control-label">
                            <?php echo form_error('file'); ?>
                        </span>
                    </div>

                    <input id="addons" type="submit" class="btn btn-success" value="<?=$this->lang->line("addons_upload")?>" >
                </form>
            </div>
        </div>
    </div>

    <div class="col-sm-8">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-table"></i> <?=$this->lang->line('addons_list')?></h3>
                <ol class="breadcrumb">
                    <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
                    <li class="active"><?=$this->lang->line('menu_addons')?></li>
                </ol>
            </div>
            <div class="box-body">
                <div class="row">
                    <?php if(customCompute($addons)) {foreach($addons as $addon) { ?>
                        <div class="col-sm-4">
                            <div class="box card-box-ini">
                                <img class="card-img-ini" width="100%" src="<?=base_url('uploads/addons/'.$addon->slug.'/src/image/'. $addon->preview_image);?>" alt="<?=$addon->package_name?>">
                                <div class="box-body">
                                    <h3 class="box-title"><?=$addon->package_name?></h3>
                                    <h4 class="box-title"><?=$addon->version?></h4>
                                    <p class="box-text"><?=namesorting($addon->description, 100)?></p>
                                    <a href="<?=base_url('addons/rollback/'.$addon->addonsID)?>" class="btn btn-danger"><i class="fa fa-trash"></i> <?=$this->lang->line('addons_delete')?></a>
                                </div>
                            </div>
                        </div>
                    <?php }} ?>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
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

            $('.image-preview-clear').click(function(){
                $('.image-preview').attr("data-content","").popover('hide');
                $('.image-preview-filename').val("");
                $('.image-preview-clear').hide();
                $('.image-preview-input input:file').val("");
                $(".image-preview-input-title").text("<?=$this->lang->line('addons_file_browse')?>");
            });

            // Set preview image into the popover data-content
            reader.onload = function (e) {
                $(".image-preview-input-title").text("<?=$this->lang->line('addons_file_browse')?>");
                $(".image-preview-clear").show();
                $(".image-preview-filename").val(file.name);
                $('.content').css('padding-bottom', '100px');
            }
            reader.readAsDataURL(file);
        });
    });
</script>