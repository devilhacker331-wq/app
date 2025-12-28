<?php echo doctype("html5"); ?>
<html lang="en">

<head>
    <!--=====================================
                META-TAG PART START
    =======================================-->
    <!-- REQUIRED META -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <!-- FAVICON -->
    <link rel="SHORTCUT ICON" href="<?= base_url("uploads/images/$siteinfos->photo") ?>" />
    <!-- FONTS -->
    <link rel="stylesheet" href="<?= base_url('frontend/default/assets/fonts/iconly/iconly.css') ?>">
    <link rel="stylesheet" href="<?= base_url('frontend/default/assets/fonts/lineicons/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('frontend/default/assets/fonts/fontawesome/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('frontend/default/assets/fonts/typography/timesnew.css') ?>">
    <link rel="stylesheet" href="<?= base_url('frontend/default/assets/fonts/typography/opensans.css') ?>">
    <link rel="stylesheet" href="<?= base_url('frontend/default/assets/lib/venobox/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('frontend/default/assets/lib/carousel/owl.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('frontend/default/assets/lib/bootstrap/all.min.css') ?>">

    <!-- CUSTOM -->
    <link rel="stylesheet" href="<?= base_url('frontend/default/assets/lib/bootstrap/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('frontend/default/assets/css/expanded/main.css') ?>">
    <link rel="stylesheet" href="<?= base_url('frontend/default/assets/css/expanded/login.css') ?>">
    <!--=====================================
                CSS LINK PART END
    =======================================-->
</head>

<body>
    <section class="login">
        <div class="login-group">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-6 col-md-10 ">
                    <div class="card p-4">
                        <div class="d-flex  flex-column justify-content-center align-items-center">
                            <?php
                            if (customCompute($siteinfos->photo)) {
                                echo "<center><img max-width='80' src=" . base_url('uploads/images/' . $siteinfos->photo) . " /></center>";
                            }
                            ?>
                            <h4 class="mt-3"><?php echo $siteinfos->sname; ?></h4>
                        </div>

                        <?php $this->load->view($subview); ?>
                    </div>
                        </div>
                </div>
            </div>
        </div>
    </section>



    <!--=====================================
                JS LINK PART START
    =======================================-->
    <script src="<?= base_url('frontend/default/assets/lib/jquery-3.5.0.min.js') ?>"></script>
    <script defer src="<?= base_url('frontend/default/assets/lib/carousel/owl.min.js') ?>"></script>
    <script defer src="<?= base_url('frontend/default/assets/lib/carousel/initialize.js') ?>"></script>
    <script defer src="<?= base_url('frontend/default/assets/lib/venobox/all.min.js') ?>"></script>
    <script defer src="<?= base_url('frontend/default/assets/lib/venobox/initialize.js') ?>"></script>
    <script defer src="<?= base_url('frontend/default/assets/lib/bootstrap/all.min.js') ?>"></script>
    <script defer src="<?= base_url('frontend/default/assets/js/script.js') ?>"></script>
    <!--=====================================
                JS LINK PART END
    =======================================-->
</body>

</html>