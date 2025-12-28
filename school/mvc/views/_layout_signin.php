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
    <title>Sign in</title>
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
    <!--=====================================
                LOGIN PART START   
    =======================================-->
    <section class="login">
        <div class="login-group">
           <?php $this->load->view($subview); ?>
           
           
        </div>
    </section>
    <!--=====================================   
                LOGIN PART END   
    =======================================-->


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
    <?php if (config_item('demo')) { ?>
        <script type="text/javascript">
            $('#admin').click(function() {
                $("input[name=username]").val('admin');
                $("input[name=password]").val('123456');
            });
            $('#teacher').click(function() {
                $("input[name=username]").val('teacher1');
                $("input[name=password]").val('123456');
            });
            $('#student').click(function() {
                $("input[name=username]").val('student1');
                $("input[name=password]").val('123456');
            });
            $('#parent').click(function() {
                $("input[name=username]").val('parent1');
                $("input[name=password]").val('123456');
            });
            $('#accountant').click(function() {
                $("input[name=username]").val('accountant');
                $("input[name=password]").val('123456');
            });
            $('#librarian').click(function() {
                $("input[name=username]").val('librarian');
                $("input[name=password]").val('123456');
            });
            $('#recep').click(function() {
                $("input[name=username]").val('receptionist');
                $("input[name=password]").val('123456');
            });


            (function(i, s, o, g, r, a, m) {
                i['GoogleAnalyticsObject'] = r;
                i[r] = i[r] || function() {
                    (i[r].q = i[r].q || []).push(arguments)
                }, i[r].l = 1 * new Date();
                a = s.createElement(o),
                    m = s.getElementsByTagName(o)[0];
                a.async = 1;
                a.src = g;
                m.parentNode.insertBefore(a, m)
            })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

            ga('create', 'UA-61634883-2', 'auto');
            ga('send', 'pageview');
        </script>
    <?php } ?>
</body>

</html>