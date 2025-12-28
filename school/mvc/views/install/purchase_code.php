<div class="panel panel-default">
    <div class="panel-heading-install">
        <ul class="nav nav-pills">
            <li><a href="<?=base_url('install/index')?>"><span class="fa fa-check"></span> Checklist</a></li>
            <li class="active"><a href="<?=base_url('install/purchasecode')?>">License Code</a> </li>
            <li><a href="#">Database</a></li>
            <li><a href="#">Time Zone</a></li>
            <li><a href="#">Site Config</a></li>
            <li><a href="#">Done</a></li>
        </ul>
    </div>
    <div class="panel-body ins-bg-col">
        <h4>License Code</h4>
        
        <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data">

            <?php 
            if (form_error('license_code')) {
                echo "<div class='form-group has-error' >";
            } else {
                echo "<div class='form-group' >";
            }
            ?>
                <label for="license_code" class="col-sm-2 control-label">
                    <p>License Code <span class="text-aqua">*</span> </p>
                </label>
                <div class="col-sm-6 d-flex">
                    <input type="text" class="form-control" id="license_code" name="license_code" value="<?=set_value('license_code')?>" >
                    <span class=" text-danger">
                        <?php echo form_error('license_code'); ?>
                    </span>
                </div>
                <a class="text-warning" data-toggle="modal" data-target="#myModal" title="Click" href="#" > ( Please click to see License activation process )</a>
            </div>

            <div class="form-group">
                <div class="col-sm-4">
                    <a href="<?=base_url('install/index')?>" class="btn btn-default pull-right">Previous Step</a>
                </div>
                <div class="col-sm-4 col-sm-offset-2">
                    <input type="submit" class="btn btn-success" value="Next Step" >
                </div>
            </div>
        </form>
    </div>
</div>



<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Activate license code process</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <section class="mb-5">
                    <h6>1. Goto inilabs official site <a href="<?=config_item('upgradeLicenseCodeUrl')?>">inilabs.net</a></h6>
                    <h6>2. Now create an account in our site. If you have login information then login.</h6>
                    <br>
                    <picture>
                        <img src="<?=base_url('uploads/installer/register.png')?>" class="img-fluid img-thumbnail image-css"  alt="...">
                    </picture>
                </section>

                <section class="mb-5">
                    <br>
                    <h6>3. Click the below link and <b>verify</b> your email.</h6>
                    <br>
                    <picture>
                        <img src="<?=base_url('uploads/installer/verify.png')?>" class="img-fluid img-thumbnail image-css"  alt="...">
                    </picture>
                </section>

                <section class="mb-5">
                    <br>
                    <h6>4. Now click <b>Active Purchase Key</b> from home page and fill you information.</h6>
                    <ol class="list">
                        <li class="py">Select your product</li>
                        <li class="py">Enter your domain, Which domain you would
                            be using this product</li>
                        <li class="py">Enter your envato/codecanyon purchase key and click
                            submit</li>
                    </ol>
                    <br>
                    <picture class="mt-1">
                        <img src="<?=base_url('uploads/installer/active.png')?>" class="img-fluid img-thumbnail image-css"  alt="...">
                    </picture>
                </section>

                <section class="mb-5">
                    <br>
                    <h6>5. Now copy your <b>Active License</b> and install the product by this license.</h6>
                    <br>
                    <picture class="mt-1">
                        <img src="<?=base_url('uploads/installer/active-license.png')?>" class="img-fluid img-thumbnail image-css"  alt="...">
                    </picture>
                </section>
            </div>
        </div>
    </div>
</div>