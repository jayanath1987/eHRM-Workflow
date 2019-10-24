<script type="text/javascript" src="<?php echo public_path('../../scripts/jquery/jquery.autocomplete.js') ?>"></script>
<?php
$encrypt = new EncryptionHandler();
?>
<div class="outerbox">
    <div class="maincontent">

        <div class="mainHeading"><h2><?php echo __("Approval status") ?></h2></div>
        <?php echo message();    ?>

        <br class="clear" />
        <form name="standardView" id="standardView" method="post" action="<?php echo url_for('workflow/DeleteGrpApp') ?>">
            <input type="hidden" name="mode" id="mode" value="">
            <table cellpadding="0" cellspacing="0" class="data-table">
                <thead>
                    <tr>                        
                        <td scope="col">
                           <?php echo __("Approved Person");?>

                        </td>
                        <td scope="col">
                           <?php echo __("Approval status");?>

                        </td>
                        <td scope="col">
                           <?php echo __("Approved Date");?>

                        </td>
                        <td scope="col">
                           <?php echo __("Application Date");?>

                        </td>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $row = 0;
                   
                        
                    foreach ($wfDetails as $GroupList) {

                        $cssClass = ($row % 2) ? 'even' : 'odd';
                        $row = $row + 1;
                    ?>
                        <tr class="<?php echo $cssClass ?>">
                            <td>    
                                
                                    <?php
                                    
                                    if ($culture == 'en') {
                                            $abc = "emp_display_name";
                                        } else {
                                            $abc = "emp_display_name_" . $culture;
                                        }
                                        if ($GroupList->wfmain_approving_emp_number == "") {

                                            echo __("Pending..");
                                        } else {
                                            if ($GroupList->Employee->$abc == "") {
                                                echo $GroupList->Employee->emp_display_name;
                                            } else {
                                                echo $GroupList->Employee->$abc;
                                            }
                                        }
                                    ?>
                               
                            </td>
                            <td>    
                               <?php
                               if ($GroupList->wfmain_iscomplete_flg == 1) {
                                   echo __("Approved");
                               }
                               else{
                                   echo __("Pending");
                               }
                               ?>
                            </td>
                            <td>    
                                <?php
                               if($GroupList->wfmain_app_date==null){
                                   echo __("Pending");
                               }else{
                                   echo $GroupList->wfmain_app_date;
                               }
                                ?>
                            </td>
                            <td>  
                               <?php
                                 echo  $GroupList->wfmain_application_date                             
                               ?>                               
                            </td>

                        </tr>
                    <?php } ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
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



            $(document).ready(function() {



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

