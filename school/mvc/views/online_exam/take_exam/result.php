<div class="col-sm-12 do-not-refresh" style="padding-top: 10px">
    <div class="callout callout-danger">
        <h4><?= $this->lang->line('take_exam_warning') ?></h4>
        <p><?= $this->lang->line('take_exam_page_refresh') ?></p>
    </div>
</div>

<section class="panel">
    <div class="panel-body">
        <div id="printablediv">
            <div class="row">

                <div class="col-sm-3">
                    <div class="box box-primary">
                        <div class="box-body box-profile">
                            <?= profileviewimage($student->photo) ?>
                            <h3 class="profile-username text-center"><?= $student->name ?></h3>
                            <ul class="list-group list-group-unbordered">
                                <li class="list-group-item" style="background-color: #FFF">
                                    <b><?= $this->lang->line('take_exam_registerNO') ?></b> <a
                                        class="pull-right"><?= $student->srregisterNO ?></a>
                                </li>
                                <li class="list-group-item" style="background-color: #FFF">
                                    <b><?= $this->lang->line('take_exam_roll') ?></b> <a
                                        class="pull-right"><?= $student->srroll ?></a>
                                </li>
                                <li class="list-group-item" style="background-color: #FFF">
                                    <b><?= $this->lang->line('take_exam_class') ?></b> <a
                                        class="pull-right"><?= customCompute($class) ? $class->classes : '' ?></a>
                                </li>
                                <li class="list-group-item" style="background-color: #FFF">
                                    <b><?= $this->lang->line('take_exam_section') ?></b> <a
                                        class="pull-right"><?= customCompute($section) ? $section->section : '' ?></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-sm-9">
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#profile"
                                    data-toggle="tab"><?= $this->lang->line('take_exam_exam_info') ?></a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="active tab-pane" id="profile">
                                <div class="panel-body profile-view-dis">
                                    <div class="row" style="min-height: 250px">
                                        <h2 class="showResult">
                                            <?php
                                            if (customCompute($onlineExam)  && $onlineExam->showResultAfterExam == 1) {
                                                if ($onlineExam->markType == 5) {
                                                    $percentage = 0;
                                                    if ($totalCorrectMark > 0 && $totalQuestionMark > 0) {
                                                        $percentage = (($totalCorrectMark / $totalQuestionMark) * 100);
                                                    }

                                                    if ($percentage >= $onlineExam->percentage) {
                                                        echo '<span class="text-green">' . $this->lang->line('take_exam_pass') . '</span>';
                                                    } else {
                                                        echo '<span class="text-red">' . $this->lang->line('take_exam_fail') . '</span>';
                                                    }
                                                } elseif ($onlineExam->markType == 10) {
                                                    if ($totalCorrectMark >= $onlineExam->percentage) {
                                                        echo '<span class="text-green">' . $this->lang->line('take_exam_pass') . '</span>';
                                                    } else {
                                                        echo '<span class="text-red">' . $this->lang->line('take_exam_fail') . '</span>';
                                                    }
                                                }
                                            }
                                            if ($onlineExam->showResultAfterExam == 1 && $onlineExam->showMarkAfterExam == 1) { ?>
                                            <a href="<?= base_url('take_exam/getAnswerList/' . $onlineExamID . '/' . $attemptedID . '/' . $student->studentID) ?>"
                                                class="btn btn-sm btn-info"><?= $this->lang->line('take_exam_show_answer_script') ?></a>
                                            <?php } ?>
                                        </h2>
                                        <?php
                                        if (customCompute($onlineExam) && $onlineExam->showResultAfterExam == 1) {
                                        ?>
                                        <table class="table table-bordered">
                                            <tr>
                                                <td><?= $this->lang->line('take_exam_total_question') ?> :
                                                    <?= customCompute($onlineExamQuestions) ?></td>
                                                <td><?= $this->lang->line('take_exam_total_answer') ?> :
                                                    <?= $totalAnswer ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><?= $this->lang->line('take_exam_total_current_answer') ?> :
                                                    <?= $correctAnswer ?></td>
                                                <td><?= $this->lang->line('take_exam_total_mark') ?> :
                                                    <?= $totalQuestionMark ?></td>
                                            </tr>
                                            <tr>
                                                <td><?= $this->lang->line('take_exam_total_obtained_mark') ?> :
                                                    <?= $totalCorrectMark ?></td>
                                                <td><?= $this->lang->line('take_exam_total_percentage') ?> :
                                                    <?= ($totalCorrectMark > 0 && $totalQuestionMark > 0) ? number_format((($totalCorrectMark / $totalQuestionMark) * 100), 2) . '%' : '0%' ?>
                                                </td>
                                            </tr>
                                        </table>
                                        <?php
                                        } else {
                                            if (!customCompute($userExamCheck)) {
                                            ?>
                                        <h3><?= $this->lang->line('take_exam_show_result_msg') ?></h3>
                                        <?php
                                            }
                                            ?>
                                        <?php
                                        }
                                        ?>

                                        <?php if (customCompute($userExamCheck) && customCompute($onlineExam) && $onlineExam->showResultAfterExam == 1) {
                                            foreach ($userExamCheck as $examCheck) { ?>
                                        <h2 class="showResult">
                                            <?php
                                                    if (customCompute($onlineExam)) {
                                                        if ($onlineExam->markType == 5) {
                                                            $percentage = 0;
                                                            if ($examCheck->totalObtainedMark > 0 && $examCheck->totalMark > 0) {
                                                                $percentage = (($examCheck->totalObtainedMark / $examCheck->totalMark) * 100);
                                                            }

                                                            if ($percentage >= $onlineExam->percentage) {
                                                                echo '<span class="text-green">' . $this->lang->line('take_exam_pass') . '</span>';
                                                            } else {
                                                                echo '<span class="text-red">' . $this->lang->line('take_exam_fail') . '</span>';
                                                            }
                                                        } elseif ($onlineExam->markType == 10) {
                                                            if ($examCheck->totalObtainedMark >= $onlineExam->percentage) {
                                                                echo '<span class="text-green">' . $this->lang->line('take_exam_pass') . '</span>';
                                                            } else {
                                                                echo '<span class="text-red">' . $this->lang->line('take_exam_fail') . '</span>';
                                                            }
                                                        }
                                                    }
                                                    if ($showResult[$examCheck->onlineExamID] == 1) { ?>

                                            <a href="<?= base_url('take_exam/getAnswerList/' . $examCheck->onlineExamID . '/' . $examCheck->examtimeID . '/' . $student->studentID) ?>"
                                                class="btn btn-sm btn-info"><?= $this->lang->line('take_exam_show_answer_script') ?></a>
                                            <?php } ?>
                                        </h2>
                                        <table class="table table-bordered">
                                            <tr>
                                                <td><?= $this->lang->line('take_exam_total_question') ?> :
                                                    <?= $examCheck->totalQuestion ?></td>
                                                <td><?= $this->lang->line('take_exam_total_answer') ?> :
                                                    <?= $examCheck->totalAnswer ?></td>
                                            </tr>
                                            <tr>
                                                <td><?= $this->lang->line('take_exam_total_current_answer') ?> :
                                                    <?= $examCheck->totalCurrectAnswer ?></td>
                                                <td><?= $this->lang->line('take_exam_total_mark') ?> :
                                                    <?= $examCheck->totalMark ?></td>
                                            </tr>
                                            <tr>
                                                <td><?= $this->lang->line('take_exam_total_obtained_mark') ?> :
                                                    <?= $examCheck->totalObtainedMark ?></td>
                                                <td><?= $this->lang->line('take_exam_total_percentage') ?> :
                                                    <?= number_format($examCheck->totalPercentage, 2) ?> % </td>
                                            </tr>
                                        </table>
                                        <?php }
                                        } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
$('.sidebar-menu li a').css('pointer-events', 'none');


function disableF5(e) {
    if (((e.which || e.keyCode) == 116) || (e.keyCode == 82 && e.ctrlKey)) {
        e.preventDefault();
    }
}

$(document).bind("keydown", disableF5);

function Disable(event) {
    if (event.button == 2) {
        window.oncontextmenu = function() {
            return false;
        }
    }
}
document.onmousedown = Disable;
</script>