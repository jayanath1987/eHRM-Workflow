<?php
if ($mode == '1') {
    $editMode = false;
    $disabled = '';
} else {
    $editMode = true;
    $disabled = 'disabled="disabled"';
}
?>
<script type="text/javascript" src="<?php echo public_path('../../scripts/jquery/jquery.validate.js')?>"></script>
    <div class="formpage4col">
        <div class="navigation">

                <?php echo message()?>
        </div>
        <div id="status"></div>
        <div class="outerbox">
            <div class="mainHeading"><h2><?php echo __("Add Approval Group")?></h2></div>
            	<form name="frmSave" id="frmSave" method="post"  action="">
                    <div class="leftCol">
                    &nbsp;
                </div>
                <div class="centerCol">
                    <label class="languageBar"><?php echo __("English")?></label>
                </div>
                <div class="centerCol">
                    <label class="languageBar"><?php echo __("Sinhala")?></label>
                </div>
                <div class="centerCol">
                    <label class="languageBar"><?php echo __("Tamil")?></label>
                </div>
                <br class="clear"/>
                <div class="leftCol">
                 <label class="controlLabel" for="txtLocationCode"><?php echo __("Approval Group")?> <span class="required">*</span></label>
                </div>
                <div class="centerCol">
                     <input id="txtAppGrp"  name="txtAppGrp" type="text"  class="formInputText" value="<?php echo $wfappgrp->wfappgrp_description; ?>" tabindex="1"  />
                </div>

                     <div class="centerCol">
                     <input id="txtAppGrpSi"  name="txtAppGrpSi" type="text"  class="formInputText" value="<?php echo $wfappgrp->wfappgrp_description_si; ?>" tabindex="1" size="100"  />
                     <input id="txtHiddenReqID"  name="txtHiddenReqID" type="hidden"  class="formInputText" value="<?php echo $wfappgrp->wfappgrp_code; ?>" maxlength="100" />
                     </div>
                     <div class="centerCol">
                     <input id="txtAppGrpTa"  name="txtAppGrpTa" type="text"  class="formInputText" value="<?php echo $wfappgrp->wfappgrp_description_ta; ?>" tabindex="1" size="100"  />
                     </div>
                     <br class="clear"/>
                <div class="formbuttons">
                     <input type="button" class="<?php echo $editMode ? 'editbutton' : 'savebutton'; ?>" name="EditMain" id="editBtn"
                       value="<?php echo $editMode ? __("Edit") : __("Save"); ?>"
                       title="<?php echo $editMode ? __("Edit") : __("Save"); ?>"
                       onmouseover="moverButton(this);" onmouseout="moutButton(this);"/>
                <input type="reset" class="clearbutton" id="btnClear" tabindex="5"
                       onmouseover="moverButton(this);" onmouseout="moutButton(this);"	<?php echo $disabled; ?>
                       value="<?php echo __("Reset"); ?>" />
                <input type="button" class="backbutton" id="btnBack"
                       value="<?php echo __("Back") ?>" tabindex="10" />
                </div>
            </form>
        </div>

   </div>

   <script type="text/javascript">

		$(document).ready(function() {

buttonSecurityCommon(null,"editBtn",null,null);

			//Disable all fields
<?php if ($mode == 0) { ?>
                            $("#editBtn").show();
                            buttonSecurityCommon(null,null,"editBtn",null);
                            $('#frmSave :input').attr('disabled', true);
                            $('#editBtn').removeAttr('disabled');
                            $('#btnBack').removeAttr('disabled');
<?php } ?>

			$("#frmSave").validate({

				 rules: {
				 	txtAppGrp: { required: true,maxlength: 100,noSpecialChars: true},
                                        txtAppGrpSi:{ maxlength: 100,noSpecialChars: true},
                                        txtAppGrpTa:{ maxlength: 100,noSpecialChars: true}
			 	 },
			 	 messages: {

			 		txtAppGrp: {required: "<?php echo __('This field is required')?>",maxlength: "<?php echo __('Maximum length should be 100 characters')?>",noSpecialChars: "<?php echo __('No invalid characters are allowed')?>"},
                                        txtAppGrpSi: {maxlength: "<?php echo __('Maximum length should be 100 characters')?>",noSpecialChars: "<?php echo __('No invalid characters are allowed')?>"},
                                        txtAppGrpTa: {maxlength: "<?php echo __('Maximum length should be 100 characters')?>",noSpecialChars: "<?php echo __('No invalid characters are allowed')?>"}

			 	 }
			 });

			 

			//Disable all fields
                    $("#frmSave").data('edit', <?php echo $editMode ? '1' : '0' ?>);


                        // When click edit button
                        $("#editBtn").click(function() {
                            var editMode = $("#frmSave").data('edit');
                            if (editMode == 1) {
                                // Set lock = 1 when requesting a table lock

                                location.href="<?php echo url_for('workflow/SaveAppGroup?id=' . $wfappgrp->wfappgrp_code . '&lock=1') ?>";
                            }
                            else {

                                $('#frmSave').submit();
                            }

                        });

			
			//When click reset buton
				$("#btnClear").click(function() {
					 location.href="<?php echo url_for('workflow/SaveAppGroup?id=' . $wfappgrp->wfappgrp_code . '&lock=0') ?>";
				 });

			 //When Click back button
			 $("#btnBack").click(function() {
				 location.href = "<?php echo url_for(public_path('../../symfony/web/index.php/workflow/approvalGroupsSummary')) ?>";
				});

			//When click Add Pay Grade
			 $("#addPayGrade").click(function() {
				 location.href = "<?php echo url_for(public_path('../../symfony/web/index.php/workflow/SaveAppGroup')) ?>";
				});

			//When click Edit Pay Grade
			 $("#editPayGrade").click(function() {
				 location.href = "<?php echo url_for(public_path('../../symfony/web/index.php/workflow/SaveAppGroup')) ?>";
				});
		 });
</script>
