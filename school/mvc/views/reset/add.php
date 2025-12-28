<div class="login-form m-0" id="login-box">
    <h5 class="header text-center mt-3 mb-3">Reset Password.</h5>
    <form role="form" method="post">
        <?php
        if ($form_validation == "No") {
        } elseif (customCompute($form_validation)) {
            echo "<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">
                    <button type=\"button\" class=\"btn-close alertClose \" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
                    $form_validation
                    </div>";
        }
        ?>
        <div class="form-group">
            <label class="form-label required">New Password</label>
            <input class="form-control" placeholder="New Password" name="newpassword" type="password">
        </div>
        <div class="form-group">
            <label class="form-label required">Confirm Password</label>
            <input class="form-control" placeholder="Re-Password" name="repassword" type="password">
        </div>    
        <button type="submit" class="btn btn-inline">Reset Password</button>
    </form>
</div>