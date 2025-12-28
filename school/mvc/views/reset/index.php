 <div class="login-form m-0" id="login-box">
    <h5 class="header text-center mt-3 mb-3">Reset Password.</h5>
    <form role="form " method="post"> 
            <?php
            if ($form_validation == "No") {
            } elseif (customCompute($form_validation)) {
                echo "<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">
                    <button type=\"button\" class=\"btn-close alertClose \" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
                    $form_validation
                    </div>";
            }
            if ($this->session->flashdata('reset_send')) {
                $message = $this->session->flashdata('reset_send');
                echo "<div class=\"alert alert-success alert-dismissible fade show\" role=\"alert\">
                    <button type=\"button\" class=\"btn-close alertClose\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
                    $message
                    </div>";
            } elseif ($this->session->flashdata('reset_error')) {
                $message = $this->session->flashdata('reset_error');
                echo "<div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">
                    <button type=\"button\" class=\"btn-close alertClose \" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
                    $message
                    </div>";
            }
            ?> 
            <div class="form-group">
                <label class="form-label required">Your Email</label>
                <input class="form-control" placeholder="Email" name="email" type="text">
            </div>
            <button type="submit" class="btn btn-inline">Send</button> 
   
    </form>
</div> 