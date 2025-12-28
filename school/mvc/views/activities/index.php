
<div class="row">
    <?php if(($siteinfos->school_year == $this->session->userdata('defaultschoolyearID')) || ($this->session->userdata('usertypeID') == 1)) { ?>
        <?php if(permissionChecker('activities_add')) { ?>
            <div class="col-md-4">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title"><i class="fa fa-fighter-jet"></i> <?=$this->lang->line('panel_title')?></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <?php $colors = array("maroon", "green", "aqua", "blue", "olive", "navy", "purple", "black");?>
                                <?php foreach($activitiescategories as $category) { ?>
                                    <?php $randIndex = array_rand($colors); ?>
                                    <a href="<?=base_url('activities/add/'.$category->activitiescategoryID)?>" class="col-sm-1 btn btn-app bg-<?=$colors[$randIndex];?>">
                                        <i class="fa <?=$category->fa_icon?>"></i> <?=$category->title?>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php } ?>
    <div class="col-md-8 activity-padd-left">
        <div  id="load_data"></div>
        <div id="load_data_message"></div>
    </div>
</div>

<script type='text/javascript'>
    $(document).ready(function(){

        var limit = 5;
        var start = 0;
        var action = 'inactive';

        function lazzy_loader(limit)
        {
            var output = '';
            for(var count=0; count<limit; count++)
            {
                output += '<div class="activities_data">';
                output += '<p><span class="content-placeholder" style="width:100%; height: 30px;">&nbsp;</span></p>';
                output += '<p><span class="content-placeholder" style="width:100%; height: 100px;">&nbsp;</span></p>';
                output += '</div>';
            }
            $('#load_data_message').html(output);
        }

        lazzy_loader(limit);

        function load_data(limit, start)
        {
            $.ajax({
                url:"<?=base_url('activities/loadRecord')?>",
                method:"POST",
                data:{limit:limit, start:start},
                cache: false,
                success:function(data)
                {
                    if(data == '')
                    {
                        $('#load_data_message').html("");
                        action = 'active';
                    }
                    else
                    {
                        $('#load_data').append(JSON.parse(data));
                        $('#load_data_message').html("");
                        action = 'inactive';
                    }
                }
            })
        }

        if(action == 'inactive')
        {
            action = 'active';
            load_data(limit, start);
        }

        $(window).scroll(function(){
            if($(window).scrollTop() + $(window).height() > $("#load_data").height() && action == 'inactive')
            {
                lazzy_loader(limit);
                action = 'active';
                start = start + limit;
                setTimeout(function(){
                    load_data(limit, start);
                }, 1000);
            }
        });

    });
</script>
