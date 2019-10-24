<?php
if ($lockMode == '1') {
    $editMode = false;
    $disabled = '';
} else {
    $editMode = true;
    $disabled = 'disabled="disabled"';
}

?>

<script type="text/javascript" src="<?php echo public_path('../../scripts/jquery/jquery.validate.js') ?>"></script>
<div class="formpage4col">
    <div class="navigation">


    </div>
    <div id="status"></div>
    <div class="outerbox">
        <div class="mainHeading"><h2><?php echo __("Assign to approval groups") ?></h2></div>
        <?php echo message() ?>
        <form name="frmSave" id="frmSave" method="post"  action="">


            <br class="clear"/>
            <div class="leftCol">
                <label class="controlLabel" for="txtLocationCode"><?php echo __("Approval group  name") ?> <span class="required">*</span></label>
            </div>
            <div class="centerCol">

                <select class="formSelect"  id="cmbGroupName" name="cmbGroupName" onchange="LoadEmployeeByGroups(this.value)" <?php echo $disabled ?>><span class="required">*</span>
                    <option value=""><?php echo __("--Select--"); ?></option>
                    <?php
                    //Define data columns according culture

                    $groupNameCol = ($userCulture == "en") ? "wfappgrp_description" : "wfappgrp_description_" . $userCulture;
                   
                    if ($groupList) {
                        
                        foreach ($groupList as $groups) {
                            $selected = ($groupID == $groups->wfappgrp_code) ? 'selected="selected"' : '';
                            $groupName = $groups->$groupNameCol == "" ? $groups->wfappgrp_description : $groups->$groupNameCol;
                            echo "<option {$selected} value='{$groups->wfappgrp_code}'>{$groups->$groupNameCol}</option>";
                        }
                    }
                    ?>

                </select>
                 
            </div>

            <br class="clear"/>

            <div class="leftCol">
                <label class="controlLabel"  for="txtLocationCode"  style='padding-top:5px;'><?php echo __("Employee List") ?></label>
            </div>


            <div id="employeeGrid" class="centerCol" style="margin-left:10px; margin-top: 8px; width: 590px; height: 100%; border-style:  solid; border-color: #FAD163">
                <div style="">
                    <div class="centerCol" style='width:150px; background-color:#FAD163;'>
                        <label class="languageBar" style="padding-left:2px; padding-top:2px;padding-bottom: 1px; background-color:#FAD163; margin-top: 0px;  color:#444444;"><?php echo __("Employee Id") ?></label>
                    </div>
                    <div class="centerCol" style='width:220px;  background-color:#FAD163;'>
                        <label class="languageBar" style="padding-left:2px; padding-top:2px;padding-bottom: 1px; background-color:#FAD163; margin-top: 0px; color:#444444; text-align:inherit"><?php echo __("Employee Name") ?></label>
                    </div>
                    <div class="centerCol" style='width:100px;  background-color:#FAD163;'>
                        <label class="languageBar" style="width:100px; padding-left:8px; padding-top:2px;padding-bottom: 1px; background-color:#FAD163; margin-top: 0px; color:#444444; text-align:inherit"><?php echo __("Division") ?></label>
                    </div>
                    <div class="centerCol" style='width:120px;   background-color:#FAD163;'>
                        <label class="languageBar" style="width:100px; padding-left:20px; padding-top:2px;padding-bottom: 1px; background-color:#FAD163; margin-top: 0px; color:#444444; text-align:inherit"><?php echo __("Remove") ?></label>
                    </div>

                </div>
                <br class="clear"/>

                <div id="tohide">
                    <?php
                    if (strlen($childDiv)) {
                        
                        echo $sf_data->getRaw('childDiv');
                    }
                    ?>

                </div>
                <br class="clear"/>
            </div>
            <br class="clear"/>

            <br class="clear"/>
            <input type="hidden" name="hiddeni" id="hiddeni" value="<?php if (strlen($i)
                        )echo $i; ?>"/>
            <div class="formbuttons">
                <input type="button" class="<?php echo $editMode ? 'editbutton' : 'savebutton'; ?>" name="EditMain" id="editBtn"
                       value="<?php echo $editMode ? __("Edit") : __("Save"); ?>"
                       title="<?php echo $editMode ? __("Edit") : __("Save"); ?>"
                       onmouseover="moverButton(this);" onmouseout="moutButton(this);"/>
                <input type="reset" class="clearbutton" id="btnClear" 
                       onmouseover="moverButton(this);" onmouseout="moutButton(this);"	<?php echo $disabled; ?>
                       value="<?php echo __("Reset"); ?>" />
                <input type="button" class="savebutton" id="empRepPopBtn" value="<?php echo __("Add Employee") ?>" <?php echo $disabled; ?>/>
                <input type="button" class="savebutton" id="buttonRemove" value="<?php echo __("Delete") ?>" <?php echo $disabled; ?>/>
            </div>
        </form>
    </div>

</div>
<div class="requirednotice"><?php echo __("Fields marked with an asterisk") ?><span class="required"> * </span> <?php echo __("are required") ?></div>
<script type="text/javascript">
    //ajax start to load to the grid ///
    var courseId="";
    var empIDMaster
    var myArray2= new Array();

         

   
    function removeByValue(arr, val) {
        for(var i=0; i<arr.length; i++) {
            if(arr[i] == val) {

                arr.splice(i, 1);

                break;

            }
        }
    }
   

    function LoadEmployeeByGroups(id){
        
        window.location = "<?php echo url_for('workflow/AssignToGroup?id=') ?>"+id;
      
    }

    function SelectEmployee(data){

        myArr=new Array();
        lol=new Array();
        myArr = data.split('|');
       
        addtoGrid(myArr);
    }

   
    function addtoGrid(empid){
       

        var arraycp=new Array();

        var arraycp = $.merge([], myArray2);

        var items= new Array();
        for(i=0;i<empid.length;i++){

            items[i]=empid[i];
        }
             
        var u=1;
       
        $.each(items,function(key, value){
           

            if(jQuery.inArray(value, arraycp)!=-1)
            {

                // ie of array index find bug sloved here//
                if(!Array.indexOf){
                    Array.prototype.indexOf = function(obj){
                        for(var i=0; i<this.length; i++){
                            if(this[i]==obj){
                                return i;
                            }
                        }
                        return -1;
                    }
                }

                var idx = arraycp.indexOf(value);
               
                if(idx!=-1) arraycp.splice(idx, 1); // Remove it if really found!
                

                u=0;
                
            }
            else{

                arraycp.push(value);
       
            }


        }


    );

        $.each(myArray2,function(key, value){
          

            if(jQuery.inArray(value, arraycp)!=-1)
            {

                // ie of array index find bug sloved here//
                if(!Array.indexOf){
                    Array.prototype.indexOf = function(obj){
                        for(var i=0; i<this.length; i++){
                            if(this[i]==obj){
                                return i;
                            }
                        }
                        return -1;
                    }
                }

                var idx = arraycp.indexOf(value); // Find the index
                if(idx!=-1) arraycp.splice(idx, 1); // Remove it if really found!
               
                u=0;
   
            }
            else{

            }


        }

    );
        $.each(arraycp,function(key, value){
            myArray2.push(value);
        }


    );       
        if(u==0){
           
        }       
        $.post(

        "<?php echo url_for('workflow/LoadGrid') ?>", //Ajax file



        { 'empid[]' : arraycp },  // create an object will all values

        
        function(data){
                       
            var childDiv="";
            var testDiv="";
            var participated="";
            var testDiv="";
            var approved="";
            var comment="";
            var delete1="";
            var rowstart="";
            var rowend="";
            var childdiv="";


            $.each(data, function(key, value) {
                i=Number($('#hiddeni').val())+1;
                     
                var word=value.split("|");
                    

                childdiv="<div id='row_"+i+"' style='padding-top:0px; height:100%; display:inline-block;'>";
                childdiv+="<div class='centerCol' id='master' style='width:150px;'>";
                childdiv+="<div id='employeename'  padding-left:3px;'>"+word[0]+"</div>";
                childdiv+="</div>";

                childdiv+="<div class='centerCol' id='master' style='width:220px;'>";
                childdiv+="<div id='employeename'  padding-left:3px;'>"+word[1]+"</div>";
                childdiv+="</div>";
                childdiv+="<div class='centerCol' id='master' style='width:120px;'>";
                childdiv+="<div id='employeename'  padding-left:3px;'>"+word[2]+"</div>";
                childdiv+="</div>";
                childdiv+="<div class='centerCol' id='master' style='width:80px;'>";
                childdiv+="<div id='employeename'  padding-left:3px;'><input type='hidden' name='hiddenEmpNumber[]' value="+word[3]+" ></div>";
                childdiv+="</div>";
                childdiv+="</div>";
                childdiv+="</br>";
                //
                $('#employeeGrid').append(childdiv);
                //
                $('#hiddeni').val(i);

            });


           
        },

        //How you want the data formated when it is returned from the server.
        "json"

    );


    }

    function deleteCRow(id,value){

        answer = confirm("<?php echo __("Do you really want to Delete?") ?>");

        if (answer !=0)
        {

            $("#row_"+id).remove();
            removeByValue(myArray2, value);

            $('#hiddeni').val(Number($('#hiddeni').val())-1);
            //alert($('#hiddeni').val());
        }
        else{
            return false;
        }

    }
  

    
    function validationComment(e,id){


        if($('#'+id).val().length==200){
            alert("<?php echo __('Maximum length should be 200 characters') ?>");
            return false;
        }else {
            return true;
        }

    }
    function onkeyUpevent(e){

        var keynum;
        var keychar;
        var numcheck;


        if(window.event) // IE
        {
            keynum = e.keyCode;
        }
        else if(e.which) // Netscape/Firefox/Opera
        {
            keynum = e.which;
        }
        keychar = String.fromCharCode(keynum);
        numcheck = /^[^@\*\!#\$%\^&()~`\+=]+$/i;

        if(!numcheck.test(keychar)){
            alert("<?php echo __('No invalid characters are allowed') ?>");
            return false;
        }
    }

   


    $(document).ready(function() {

        <?php if ($editMode == true) { ?>
            $("#editBtn").show();

            buttonSecurityCommon(null,null,"editBtn","buttonRemove");
        <?php } else { ?>
            $("#editBtn").show();
            buttonSecurityCommon(null,"editBtn",null,"buttonRemove");
        <?php } ?>

        var currentGroup=$('#cmbGroupName').val();
    
        if(currentGroup!=""){
            $.post(

            "<?php echo url_for('workflow/GetListedEmpids') ?>", //Ajax file

            { currentGroup : currentGroup },  // create an object will all values

            //function that is called when server returns a value.
            function(data){

                $.each(data, function(key, value) {
                    myArray2.push(value);

                });
                    
            },

            //How you want the data formated when it is returned from the server.
            "json"

        );

        }



        //Validate the form
        $("#frmSave").validate({

            rules: {
                instName: { required: true },
                courseid: { required: true },
                txtyear: { required: true},
                gencomment_en: { noSpecialCharsOnly: true,maxlength:200 },
                gencomment_si: { noSpecialChars: true },
                gencomment_ta: { noSpecialChars: true }
            },
            messages: {
                instName: "<?php echo __("Invalid Institute Name") ?>",
                courseid: "<?php echo __("Training Name is required") ?>",
                txtyear: "<?php echo __("Training Year is required") ?>",
                gencomment_en: {noSpecialCharsOnly:"<?php echo __("No Special characters allowed") ?>",maxlength:"<?php echo __("Maximum length should be 200 characters") ?>"},
                gencomment_si: "<?php echo __("No Special characters allowed") ?>",
                gencomment_ta: "<?php echo __("No Special characters allowed") ?>"

            }
        });

        //var mode	=	'edit';
        var mode	='<?php echo $mode ?>';



        // When click edit button
        $("#frmSave").data('edit', <?php echo $editMode ? '1' : '0' ?>);

        $("#editBtn").click(function() {

            var editMode = $("#frmSave").data('edit');
            if (editMode == 1) {
                // Set lock = 1 when requesting a table lock

                location.href="<?php echo url_for('workflow/AssignToGroup?lock=1&id=' . $groupID) ?>";
            }
            else {
                if(myArray2.length==0){
                    alert("<?php echo __('Please select at least one employee') ?>");
                }
                else{
                         
                    $('#frmSave').submit();
                }
            }


        });

        $('#empRepPopBtn').click(function() {

            
      var popup=window.open('<?php echo public_path('../../symfony/web/index.php/pim/searchEmployee?type=multiple&method=SelectEmployee'); ?>','Locations','height=450,width=800,resizable=1,scrollbars=1');

                 
            if(!popup.opener) popup.opener=self;
            popup.focus();
        });


        $("#buttonRemove").click(function() {
                    
            if($('input[name=deleteEmp[]]').is(':checked')){
                answer = confirm("<?php echo __("Do you really want to Delete?") ?>");
            }


            else{
                alert("<?php echo __("select at least one check box to delete") ?>");

            }

            if (answer !=0)
            {

                var empIdsArray = [];
                $("input[name=deleteEmp[]]:checked").each(function()
                {
                    empIdsArray.push(this.value);
                });
                           

                $.post(

                "<?php echo url_for('workflow/deleteAssignedCapability') ?>", //Ajax file

                { 'empId[]' : empIdsArray,'groupID' : $('#cmbGroupName').val()  },  // create an object will all values

                //function that is called when server returns a value.
                function(data){

                    if(data=="ok"){
                  
                        alert("<?php echo __('Sucessfully Deleted') ?>");
                        location.href="<?php echo url_for('workflow/AssignToGroup?lock=0&id=' . $groupID) ?>";
                     

                    }


                    
                },

                //How you want the data formated when it is returned from the server.
                "json"

            );

            }
            else{
                return false;
            }

        });

        //When click reset buton
        $("#btnClear").click(function() {
          

            location.href="<?php echo url_for('workflow/AssignToGroup?lock=0&id=' . $groupID) ?>";
        });

        //When Click back button
        $("#btnBack").click(function() {
            location.href = "<?php echo url_for(public_path('../../symfony/web/index.php/training/trainsummery')) ?>";
        });

    });
</script>
