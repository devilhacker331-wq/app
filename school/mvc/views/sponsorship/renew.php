<div class="row">
    <div class="col-md-9">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-gg"></i> <?= $this->lang->line('panel_title') ?></h3>
                <ol class="breadcrumb">
                    <li><a href="<?= base_url("dashboard/index") ?>"><i
                                    class="fa fa-laptop"></i> <?= $this->lang->line('menu_dashboard') ?></a></li>
                    <li><a href="<?= base_url("sponsorship/index") ?>"><?= $this->lang->line('menu_sponsorship') ?></a>
                    </li>
                    <li class="active"><?= $this->lang->line('menu_renew') ?> <?= $this->lang->line('menu_sponsorship') ?></li>
                </ol>
            </div><!-- /.box-header -->
            <!-- form start -->
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-12">
                        <form class="form-horizontal" role="form" method="post">

                            <div class="form-group <?= form_error('sponsorID') ? 'has-error' : '' ?>">
                                <label for="sponsorID" class="col-sm-2 control-label">
                                    <?= $this->lang->line("sponsorship_sponsor") ?> <span class="text-red">*</span>
                                </label>
                                <div class="col-sm-6">
                                    <?php
                                    $sponsorArray[0] = $this->lang->line("sponsorship_select_sponsor");
                                    $sponsorArray[$sponsors->sponsorID] = $sponsors->name;
                                    echo form_dropdown("sponsorID", $sponsorArray, set_value("sponsorID", $sponsorship->sponsorID), "id='sponsorID' class='form-control select2' readonly");
                                    ?>
                                </div>
                                <span class="col-sm-4 control-label">
                            <?= form_error('sponsorID'); ?>
                        </span>
                            </div>

                            <div class="form-group <?= form_error('studentID') ? 'has-error' : '' ?>">
                                <label for="studentID" class="col-sm-2 control-label">
                                    <?= $this->lang->line("sponsorship_student") ?> <span class="text-red">*</span>
                                </label>
                                <div class="col-sm-6">
                                    <?php
                                    $studentArray = array('0' => $this->lang->line("sponsorship_select_student"));
                                    if (customCompute($candidates)) {
                                        foreach ($candidates as $candidate) {
                                            $studentArray[$candidate->candidateID] = $candidate->name . ' - ' . $candidate->registerNO;
                                        }
                                    }
                                    echo form_dropdown("studentID", $studentArray, set_value("studentID", $sponsorship->candidateID), "id='studentID' class='form-control select2'");
                                    ?>
                                </div>
                                <span class="col-sm-4 control-label">
                            <?= form_error('studentID'); ?>
                        </span>
                            </div>

                            <div class="form-group <?= form_error('start_date') ? 'has-error' : '' ?>">
                                <label for="start_date" class="col-sm-2 control-label">
                                    <?= $this->lang->line("sponsorship_start_date") ?> <span class="text-red">*</span>
                                </label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control datepicker" id="start_date" name="start_date"
                                           value="<?= set_value('start_date', date('d-m-Y', strtotime((string) $sponsorship->start_date))) ?>">
                                </div>
                                <span class="col-sm-4 control-label">
                            <?= form_error('start_date'); ?>
                        </span>
                            </div>

                            <div class="form-group <?= form_error('end_date') ? 'has-error' : '' ?>">
                                <label for="end_date" class="col-sm-2 control-label">
                                    <?= $this->lang->line("sponsorship_end_date") ?> <span class="text-red">*</span>
                                </label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control datepicker" id="end_date" name="end_date"
                                           value="<?= set_value('end_date', date('d-m-Y', strtotime((string) $sponsorship->end_date))) ?>">
                                </div>
                                <span class="col-sm-4 control-label">
                            <?= form_error('end_date'); ?>
                        </span>
                            </div>

                            <div class="form-group <?= form_error('amount') ? 'has-error' : '' ?>">
                                <label for="amount" class="col-sm-2 control-label">
                                    <?= $this->lang->line("sponsorship_amount") ?> <span class="text-red">*</span>
                                </label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="amount" name="amount"
                                           value="<?= set_value('amount', $sponsorship->amount) ?>">
                                </div>
                                <span class="col-sm-4 control-label">
                            <?= form_error('amount'); ?>
                        </span>
                            </div>

                            <div class="form-group <?= form_error('payment_date') ? 'has-error' : '' ?>">
                                <label for="payment_date" class="col-sm-2 control-label">
                                    <?= $this->lang->line("sponsorship_payment_date") ?>
                                </label>
                                <div class="col-sm-6">
                                    <?php
                                    $payment_date = '';
                                    if ($sponsorship->payment_date) {
                                        $payment_date = date('d-m-Y', strtotime((string) $sponsorship->payment_date));
                                    }
                                    ?>
                                    <input type="text" class="form-control datepicker" id="payment_date"
                                           name="payment_date" value="<?= set_value('payment_date', $payment_date) ?>">
                                </div>
                                <span class="col-sm-4 control-label">
                            <?= form_error('payment_date'); ?>
                        </span>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-8">
                                    <input type="submit" class="btn btn-success"
                                           value="<?= $this->lang->line("renew_sponsorship") ?>">
                                </div>
                            </div>
                        </form>
                    </div>
            
                </div>
            </div>
        </div>
    </div>

     <div class="col-md-3">

        <div class="box">


            <span id="movie-data"></span>

        </div>

    </div>
</div>

<script type="text/javascript">
    $('.select2').select2();
    $('.datepicker').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
    });

    var studentID = $('#studentID').val();
    $.ajax({
        type: 'POST',
        url: "<?=base_url('Sponsorship/getSingleStudent')?>",
        data: {'studentID': studentID},
        dataType: "html",
        success: function (data) {

            $("#movie-data").empty().append(data);

        }
    });

    $(document).on('change', '#studentID', function () {

    });
</script>