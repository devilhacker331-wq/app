<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <?php if(customCompute($questions)) { ?>
        <?=reportheader($siteinfos,$schoolyearsessionobj,true)?>
        <div class="headerInfo">
            <h4 class="pull-left text-bold"><?=$this->lang->line('onlineexamquestionanswerreport_exam')?> : <?=$exam->name?></h4>
        </div>
        <div>
            <?php
            if(customCompute($questions)) {
                $i = 0;
                foreach($questions as $question) {
                    $optionCount = $question->totalOption;
                    $i++; ?>
                    <div class="clearfix">
                        <div class="question-body">
                            <label><b><?=$i?>.</b> <?=$question->question?></label>
                        </div>

                        <?php if($question->upload != '') { ?>
                            <div>
                                <img style="width:250px;height:150px;padding-left: 20px" src="<?=base_url('uploads/images/'.$question->upload)?>" alt="">
                            </div>
                        <?php } ?>

                        <div class="question-answer">
                            <table class="table">
                                <tr>
                                    <?php
                                    $oc = 1;
                                    $tdCount = 0;
                                    $questionoptions = isset($question_options[$question->questionBankID]) ? $question_options[$question->questionBankID] : [];
                                    if(customCompute($questionoptions)) {
                                        $optionLabel = 'A';
                                        foreach ($questionoptions as $option) {
                                            if($optionCount >= $oc) { $oc++;
                                                if (isset($examquestionsuseranswer[$question->questionBankID]) && $option->optionID == $examquestionsuseranswer[$question->questionBankID]->optionID) {
                                                    if(isset($examquestionsanswer[$question->questionBankID]) && $examquestionsanswer[$question->questionBankID]->optionID==$examquestionsuseranswer[$question->questionBankID]->optionID) {
                                                        ?>
                                                        <td style="background: green;">
                                                            <span style="color: #ffffff"><?= $optionLabel ?>.</span>
                                                            <span style="color: #ffffff"><?= $option->name ?></span>
                                                            <label for="option<?= $option->optionID ?>">
                                                                <?php
                                                                if (!is_null($option->img) && $option->img != "") { ?>
                                                                    <img class="questionimg"
                                                                         src="<?= base_url('uploads/images/' . $option->img) ?>"/>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </label>
                                                        </td>
                                                        <?php
                                                        $optionLabel++;
                                                    }else { ?>
                                                        <td style="background: red;">
                                                                 <span style="color: #ffffff"><?= $optionLabel ?>.</span>
                                                            <span style="color: #ffffff"><?= $option->name ?></span>
                                                            <label for="option<?= $option->optionID ?>">
                                                                <?php
                                                                if (!is_null($option->img) && $option->img != "") { ?>
                                                                    <img class="questionimg"
                                                                         src="<?= base_url('uploads/images/' . $option->img) ?>"/>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </label>
                                                        </td>

                                                        <?php

                                                        $optionLabel++;
                                                    }
                                                } elseif (isset($examquestionsanswer[$question->questionBankID]) && $option->optionID == $examquestionsanswer[$question->questionBankID]->optionID) {
                                                    ?>
                                                        <td>
                                                            <span>
                                                    <?=$optionLabel?>
                                                    .</span>
                                                            <span>
                                                    <?=$option->name?>
                                                    </span>
                                                            <span ><img style="height: 20px;width: 20px" src="
                                                    <?=base_url('uploads/images/check.png')?>
                                                    "></span>
<!--                                                            <span class="selected_div" <i class="fa fa-check text-white"></i></span>-->
                                                            <label for="option
                                                    <?=$option->optionID?>
                                                    ">
                                                                <?php 
                                                    if(!is_null($option->img) && $option->img != "") { ?>
                                                                    <img class="questionimg" src="<?=base_url('uploads/images/'.$option->img)?>"/>
                                                                    <?php
                                                                }
                                                    ?>
                                                            </label>
                                                        </td>
                                                        <?php 
                                                    $optionLabel++;
                                                } else {?>

                                                    <td>
                                                        <span><?=$optionLabel?>.</span>
                                                        <span><?=$option->name?></span>
                                                        <label for="option<?=$option->optionID?>">
                                                            <?php
                                                                if(!is_null($option->img) && $option->img != "") { ?>
                                                                    <img class="questionimg" src="<?=base_url('uploads/images/'.$option->img)?>"/>
                                                                <?php
                                                                }
                                                            ?>
                                                            </label>
                                                    </td>
                                                    <?php
                                                        $optionLabel++;
                                                }
                                            }
                                            $tdCount++;
                                            if($tdCount == 2) {
                                                $tdCount = 0;
                                                echo "</tr><tr>";
                                            }
                                        }
                                    }
                                    ?>
                                </tr>
                            </table>
                        </div>

                    </div>
                <?php } } else { ?>
                <div class="callout callout-danger">
                    <p><b class="text-info"><?=$this->lang->line('onlineexamquestionanswerreport_data_not_found')?></b></p>
                </div>
            <?php } ?>
        </div>
        <?=reportfooter($siteinfos,$schoolyearsessionobj)?>
    <?php }
    else { ?>
        <div class="notfound">
            <p><?=$this->lang->line('onlineexamquestionreport_data_not_found')?></p>
        </div>
    <?php } ?>
</body>
</html>
