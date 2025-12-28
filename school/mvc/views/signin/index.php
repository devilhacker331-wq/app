<div class="login-content">
    <h3>welcome back!</h3>
    <p>Please login to your account</p> 
 
    <form class="login-form" method="post"> 
        <?php
        if ($form_validation == "No") {
        } elseif (customCompute($form_validation)) {
            echo "<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">
            <button type=\"button\" class=\"btn-close alertClose \" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
            $form_validation
        </div>";
        } 
        if ($this->session->flashdata('reset_success')) {
            $message = $this->session->flashdata('reset_success');
            echo "<div class=\"alert alert-success alert-dismissible fade show\" role=\"alert\">
            <button type=\"button\" class=\"btn-close alertClose\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
           $message
         </div>";
        }
        ?> 
        <div class="form-group">
            <label class="form-label required">User Name</label>
            <input class="form-control" placeholder="Username" name="username" type="text" autofocus value="<?= set_value('username') ?>">
        </div>
        <div class="form-group">
            <label class="form-label required">Password</label>
            <input class="form-control" placeholder="Password" name="password" type="password">
        </div>
        <div class="checkbox d-flex align-items-center justify-content-between">
            <label class="mb-2">
                <input type="checkbox" value="Remember Me" name="remember">
                <span> &nbsp; Remember Me</span>
            </label>
            <span class="pull-right">
                <label>
                    <a class="mb-2 forgotPass" href="<?= base_url('reset/index') ?>"> Forgot Password?</a>
                </label>
            </span>
        </div>
        <?php if (isset($siteinfos->captcha_status) && $siteinfos->captcha_status == 0) { ?>
            <div class="form-group">
                <?php echo $recaptcha['widget'];
                echo $recaptcha['script']; ?>
            </div>
        <?php } ?>

        <button type="submit" class="btn btn-inline">sign in</button>
    </form>
    <?php if (config_item('demo')) { ?>
        <h4>For Quick Demo Login Click Below...</h4>
        <nav>
            <button type="button" id="admin">admin</button>
            <button type="button" id="teacher">teacher</button>
            <button type="button" id="student">student</button>
            <button type="button" id="parent">parent</button>
            <button type="button" id="accountant">accountant</button>
            <button type="button" id="librarian">Librarian</button>
            <button type="button" id="recep">Receptionist</button>
        </nav>
    <?php } ?>
</div>


<div class="login-banner">
    <img src="<?= base_url('frontend/default/assets/images/login.jpg') ?>" alt="login">
    <div>
        <blockquote>“Education is the most powerful weapon which can use to change the world.”</blockquote>
        <label>--Nelson Mandela</label>
    </div>
</div>