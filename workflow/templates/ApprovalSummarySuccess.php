<style>
    img{
        padding-top:10px;
    }

    h2.trigger {

        padding-bottom: 10px;
        height: 25px;
        width: 100%;
        font-size: 2em;
        font-weight: bold;

    }
    h2.trigger a {
        color: #000;
        text-decoration: none;
        display: block;
        border:none;
    }
    h2.trigger a:hover { color: #000; }
    h2.active {background-position: left bottom;} /*--When toggle is triggered, it will shift the image to the bottom to show its "opened" state--*/
    .toggle_container {
        border-top: 1px solid #d6d6d6;
        background: #f0f0f0 url(toggle_block_stretch.gif) repeat-y left top;
        overflow: hidden;
        font-size: 1.2em;
        clear: both;
    }
    .toggle_container .block {
        padding: 20px; /*--Padding of Container--*/
        background: url(toggle_block_btm.gif) no-repeat left bottom; /*--Bottom rounded corners--*/
    }
    #pendingStatus{
        float:right; margin-top: 10px; margin-right: 550px;
    }
    .hiddenViewName{
        display:none;
    }
    #detailSummary{
        display:none;
    }
    img{
        border: none;
    }

   }

</style>
<script type="text/javascript" src="<?php echo public_path('../../scripts/jquery/jquery.autocomplete.js') ?>"></script>
<?php
$encrypt = new EncryptionHandler();
?>
<div class="outerbox">
    <div class="maincontent" style="height:auto">

        <div class="mainHeading"><h2><?php echo __("Approval  Summary List") ?></h2></div>
        <?php echo message(); ?>

        <?php
        $row = 0;

        foreach ($apSummary as $approveList) {
            //die(print_r($approveList));
            $cssClass = ($row % 2) ? 'even' : 'odd';
            $row = $row + 1;
            ?>

            <?php
            if ($culture == "en") {
                $feild = "name";
            } else {
                $feild = "module_name_" . $culture;
            }
            ?>
            <?php
            $image = "<img src='" . public_path("../../themes/orange/icons/add.gif") . "'  alt='' />";
            ?>
            <h2 id="<?php echo $approveList['wfmod_view_name']; ?>" class="trigger <?php echo $cssClass ?>"><a href="#"><?php echo $image; ?>
    <?php            

            if ($culture == "en") {
                $feil = "wfmod_name";
            } else {
                $feil = "wfmod_name_" . $culture;
            }
            ?>
                    <?php echo $approveList[$feil]; ?>  <div id="pendingStatus"><?php echo $approveList['COUNT(wfmod_name)']; ?> <?php echo __("Pending Approvals") ?> </div>

                    <div class="hiddenViewName" > <?php echo $approveList['wfmod_view_name']; ?> </div>
                </a>
            </h2>


            <div class="toggle_container">
                <div class="block" id="<?php echo $approveList['wfmod_id']; ?>">

                </div>
            </div>


        <?php } ?>
        

    </div>
    <div id="detailSummary">

    </div>

</div>
<?php $en_col="en_col";?>
<script type="text/javascript">

    function validateform(){

        if($("#searchValue").val()=="")
        {

            alert("<?php echo __('Please enter search value') ?>");
            return false;

        }
        if($("#searchMode").val()=="all"){
            alert("<?php echo __('Please select the search mode') ?>");
            return false;
        }
        else{
            $("#frmSearchBox").submit();
        }

    }
        function myFunction(value){
            alert(value);
        }

    function loadAppDetails(redirectUrl,wfId,eleId){
        $.ajax({
                            type: "POST",
                            async:false,
                            url: "<?php echo url_for('workflow/AjaxEncryption') ?>",
                                        data: { wfId: wfId },
                                        dataType: "json",
                                        success: function(data){wfId = data;}
                                    });
       setSessionFoID(eleId);   
        location.href="<?php echo url_for(public_path('../../symfony/web/index.php/')) ?>"+redirectUrl+"?wfID="+wfId;
        //e.log("<?php echo url_for(public_path('../../symfony/web/index.php/')) ?>");
                                
    }
    function localizationAjax(text){
        var localizedText;
        $.ajax({
                 type: "POST",
                 async:false,
                 url: "<?php echo url_for('default/TransLateText') ?>",
                 data: { text: text },
                 dataType: "json",
                 success: function(data){
                     localizedText = data;
                 }
             });            
        
        return localizedText;
        
    }
    function setSessionFoID(id){
        
        $.ajax({
                 type: "POST",
                 async:false,
                 url: "<?php echo url_for('workflow/SetSessionForID') ?>",
                 data: { id: id },
                 dataType: "json",
                 success: function(data){
                   
                 }
             });            
        
      
    }
    function UnsetsetSessionFoID(id){
        
        $.ajax({
                 type: "POST",
                 async:false,
                 url: "<?php echo url_for('workflow/UnSetSessionForID') ?>",
                 data: { id: id },
                 dataType: "json",
                 success: function(data){
                   
                 }
             });            
        
      
    }
    function onloadDisplay(){
        
        $("#<?php echo $_SESSION['workFlowElementId']?>").toggleClass("active").next().slideToggle("slow");
            var viewName=$("#<?php echo $_SESSION['workFlowElementId']?>").attr('id');

            //display approval list ajax method
            var html="";
            $.ajax({
                type: "POST",
                async:false,
                url: "<?php echo url_for('workflow/getApprovalByModuleView') ?>",
                data: { viewName: viewName ,appEmpName:<?php echo $_SESSION['empNumber'] ?> },
                dataType: "json",
                success: function(data){
                    
                    html+="<table>";
                    var c=0;
                    $.each(data.resultArr[0], function(index, value) {
                        c=c+1;
                        if(c>6){
                            
                            var columName=index;
                            var patt1=/_en/gi;
                            var patt2=/_si/gi;
                            var patt3=/_ta/gi;
                            var culture="<?php echo $culture ?>";
                            
                            if(columName.match(patt1)!=null && culture=="en"){ 
                                var en_col=columName.replace(/_en/i, "");                                                                       
                                html+="<th style='width:130px;'>"+ localizationAjax(en_col) +"</th>";
                            }else if(columName.match(patt2)!=null && culture=="si"){
                                var si_col=columName.replace(/_si/i, "");
                                html+="<th>"+ localizationAjax(si_col) +"</th>";
                            }else if(columName.match(patt3)!=null && culture=="ta"){
                                
                                var ta_col=columName.replace(/_ta/i, "");
                                html+="<th>"+ localizationAjax(ta_col) +"</th>";
                            }
                            else if(columName.match(patt1)==null && columName.match(patt2)==null && columName.match(patt3)==null) {
                                html+="<th>"+localizationAjax(index)+"</th>";
                            }
                            
                        }

                    });
                    for(i=0;i<data.resultArr.length;i++){
                        
                        var moduleId;
                        moduleId=data.resultArr[i]["Module ID"];
                        //alert(moduleId);
                        var wfmainId;
                        wfmainId=data.resultArr[i]["ID"];
                        //alert(wfmainId);

                        html+="<tr>";
                        var z=0;
                        $.each(data.resultArr[i], function(index, value) {
                            z=z+1;
                            if(z>6){
                                //serach for _en/_si/_ta in column name
                                var columName=index;
                                var patt1=/_en/gi;
                                var patt2=/_si/gi;
                                var patt3=/_ta/gi;
                                var culture="<?php echo $culture ?>";
                                //                                console.log(columName.match(patt1));
                                if(columName.match(patt1)!=null && culture=="en"){
                                    html+="<td align='center'>"+value+"</td>";
                                }else if(columName.match(patt2)!=null && culture=="si"){
                                    html+="<td align='center'>"+value+"</td>";
                                }else if(columName.match(patt3)!=null && culture=="ta"){
                                    html+="<td align='center'>"+value+"</td>";
                                }
                                else if(columName.match(patt1)==null && columName.match(patt2)==null && columName.match(patt3)==null) {
                                    html+="<td align='center'>"+value+"</td>";
                                }
                                
                                
                                
                            }

                        });
                        
                        var approveEmp=<?php echo $_SESSION['empNumber'] ?>;                       
                        html+="<td><input type='button' class='plainbtn' value='<?php echo __("View") ?> ' onclick="+"loadAppDetails("+data.redirectUrl+","+wfmainId+");"+" /></td>";
                        html+="</tr>";

                        
                    }
                    html+="</table>";
                    $("#"+moduleId).html(html);
                }

            });
            return false; //Prevent the browser jump to the link anchor
    }    

    $(document).ready(function() {
           
        //Hide (Collapse) the toggle containers on load
        $(".toggle_container").hide();
        var sessionID="<?php echo $_SESSION['workFlowElementId']?>";
        //alert(sessionID);
        if(sessionID!=""){
            
        
        onloadDisplay();
        }
        //Switch the "Open" and "Close" state per click then slide up/down (depending on open/close state)
        $("h2.trigger").click(function(){
           
            
            $(this).toggleClass("active").next().slideToggle("slow");
            var viewName=$(this).attr('id');
             UnsetsetSessionFoID($(this).attr('id'));
            //display approval list ajax method
            var html="";
            $.ajax({
                type: "POST",
                async:false,
                url: "<?php echo url_for('workflow/getApprovalByModuleView') ?>",
                data: { viewName: viewName ,appEmpName:<?php echo $_SESSION['empNumber'] ?> },
                dataType: "json",
                success: function(data){
                    
                    html+="<table>";
                    var c=0;
                    $.each(data.resultArr[0], function(index, value) {
                        c=c+1;
                        if(c>6){
                            
                            var columName=index;
                            var patt1=/_en/gi;
                            var patt2=/_si/gi;
                            var patt3=/_ta/gi;
                            var culture="<?php echo $culture ?>";
                            //                                console.log(columName.match(patt1));
                            if(columName.match(patt1)!=null && culture=="en"){ 
                                var en_col=columName.replace(/_en/i, "");                                                                       
                                html+="<th style='width:160px;'>"+ localizationAjax(en_col) +"</th>";
                            }else if(columName.match(patt2)!=null && culture=="si"){
                                var si_col=columName.replace(/_si/i, "");
                                html+="<th>"+ localizationAjax(si_col) +"</th>";
                            }else if(columName.match(patt3)!=null && culture=="ta"){
                                
                                var ta_col=columName.replace(/_ta/i, "");
                                html+="<th>"+ localizationAjax(ta_col) +"</th>";
                            }
                            else if(columName.match(patt1)==null && columName.match(patt2)==null && columName.match(patt3)==null) {
                                html+="<th>"+localizationAjax(index)+"</th>";
                            }
                            
                        }

                    });
                    for(i=0;i<data.resultArr.length;i++){
                        
                        var moduleId;
                        moduleId=data.resultArr[i]["Module ID"];
                        //alert(moduleId);
                        var wfmainId;
                        wfmainId=data.resultArr[i]["ID"];
                        //alert(wfmainId);

                        html+="<tr>";
                        var z=0;
                        $.each(data.resultArr[i], function(index, value) {
                            z=z+1;
                            if(z>6){
                                //serach for _en/_si/_ta in column name
                                var columName=index;
                                var patt1=/_en/gi;
                                var patt2=/_si/gi;
                                var patt3=/_ta/gi;
                                var culture="<?php echo $culture ?>";
                                //                                console.log(columName.match(patt1));
                                if(columName.match(patt1)!=null && culture=="en"){
                                    html+="<td align='center'>"+value+"</td>";
                                }else if(columName.match(patt2)!=null && culture=="si"){
                                    html+="<td align='center'>"+value+"</td>";
                                }else if(columName.match(patt3)!=null && culture=="ta"){
                                    html+="<td align='center'>"+value+"</td>";
                                }
                                else if(columName.match(patt1)==null && columName.match(patt2)==null && columName.match(patt3)==null) {
                                    html+="<td align='center'>"+value+"</td>";
                                }
                                                                                                
                            }

                        });
                        
                        var approveEmp=<?php echo $_SESSION['empNumber'] ?>;                       
                        html+="<td><input type='button' class='plainbtn' value='<?php echo __("View") ?> ' onclick="+"loadAppDetails("+data.redirectUrl+","+wfmainId+","+"'"+viewName+"'"+");"+" /></td>";
                        html+="</tr>";

                        
                    }
                    html+="</table>";
                    $("#"+moduleId).html(html);
                }

            });
            return false; //Prevent the browser jump to the link anchor
        });

        buttonSecurityCommon("buttonAdd",null,"editBtn","buttonRemove");
        var answer=0;


        $("#buttonRemove").click(function() {
            $("#mode").attr('value', 'delete');
            if($('input[name=chkLocID[]]').is(':checked')){
                answer = confirm("<?php echo __("Do you really want to Delete?") ?>");
            }


            else{
                alert("<?php echo __("select at least one check box to delete") ?>");

            }

            if (answer !=0)
            {

                $("#standardView").submit();

            }
            else{
                return false;
            }

        });

        //When click add button
        $("#buttonAdd").click(function() {
            location.href = "<?php echo url_for(public_path('../../symfony/web/index.php/workflow/SaveAppGroup')) ?>";
        });

        // When Click Main Tick box
        $("#allCheck").click(function() {
            if ($('#allCheck').attr('checked')){

                $('.innercheckbox').attr('checked','checked');
            }else{
                $('.innercheckbox').removeAttr('checked');
            }
        });

        $(".innercheckbox").click(function() {
            if($(this).attr('checked'))
            {

            }else
            {
                $('#allCheck').removeAttr('checked');
            }
        });

        $("#resetBtn").click(function() {
            document.forms[0].reset('');
            location.href = "<?php echo url_for(public_path('../../symfony/web/index.php/workflow/approvalGroupsSummary')) ?>";
        });


    });


</script>

