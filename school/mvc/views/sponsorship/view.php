<div class="well">
    <div class="row">
        <div class="col-sm-6">
            <button class="btn-cs btn-sm-cs" onclick="javascript:printDiv('printablediv')"><span class="fa fa-print"></span> <?=$this->lang->line('print')?> </button>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb">
                <li><a href="<?=base_url("dashboard/index")?>"><i class="fa fa-laptop"></i> <?=$this->lang->line('menu_dashboard')?></a></li>
                <li><a href="<?=base_url("candidate/index")?>"><?=$this->lang->line('menu_candidate')?></a></li>
                <li class="active"><?=$this->lang->line('view')?></li>
            </ol>
        </div>
    </div>
</div>


<div id="printablediv">
    <div class="row">
        <div class="col-sm-3">
            <div class="box box-primary">
                <div class="box-body box-profile">
                    <?=profileviewimage($profile->photo)?>
                    <h3 class="profile-username text-center"><?=$profile->name?></h3>
                    <p class="text-muted text-center"><?=isset($usertypes[$profile->usertypeID]) ? $usertypes[$profile->usertypeID] : ''?></p>
                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item" style="background-color: #FFF">
                            <b><?=$this->lang->line('candidate_sex')?></b> <a class="pull-right"><?=$profile->sex?></a>
                        </li>
                        <li class="list-group-item" style="background-color: #FFF">
                            <b><?=$this->lang->line('candidate_dob')?></b> <a class="pull-right"><?=date('d M Y',strtotime((string) $profile->dob))?></a>
                        </li>
                        <li class="list-group-item" style="background-color: #FFF">
                            <b><?=$this->lang->line('candidate_phone')?></b> <a class="pull-right"><?=$profile->phone?></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-sm-9">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#profile" data-toggle="tab"><?=$this->lang->line('candidate_view')?></a></li>
                </ul>

                <div class="tab-content">
                    <div class="active tab-pane" id="profile">
                        <div class="panel-body profile-view-dis">
                            <div class="row">
                                <div class="profile-view-tab">
                                    <p><span><?=$this->lang->line("candidate_religion")?> </span>: <?=$profile->religion?></p>
                                </div>
                                <div class="profile-view-tab">
                                    <p><span><?=$this->lang->line("candidate_email")?> </span>: <?=$profile->email?></p>
                                </div>
                                <div class="profile-view-tab">
                                    <p><span><?=$this->lang->line("candidate_address")?> </span>: <?=$profile->address?></p>
                                </div>

                                <div class="profile-view-tab">
                                    <p><span><?=$this->lang->line("candidate_username")?> </span>: <?=$profile->username?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>

<script language="javascript" type="text/javascript">
    
    function printDiv(divID) {
        //Get the HTML of div
        var divElements = document.getElementById(divID).innerHTML;
        //Get the HTML of whole page
        var oldPage = document.body.innerHTML;

        //Reset the page's HTML with div's HTML only
        document.body.innerHTML = "<html><head><title></title></head><body>" + divElements + "</body>";

        //Print Page
        window.print();

        //Restore orignal HTML
        document.body.innerHTML = oldPage;
        window.location.reload();
    }

</script>