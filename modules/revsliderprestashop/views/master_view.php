<?php

	$revSliderVersion = GlobalsRevSlider::SLIDER_REVISION;

	

	$wrapperClass = "";

	if(GlobalsRevSlider::$isNewVersion == false)

		 $wrapperClass = " oldwp";

	

	//$nonce = wp_create_nonce("revslider_actions");        

	

?>



<script type="text/javascript">



    <?php 

    $sds_admin_url = admin_url();

    // $sds_admin_upload_url =  _MODULE_DIR_ ."revsliderprestashop/filemanager/dialog.php?type=0&lang=en&popup=0&field_id=0&fldr=&5473a39f286af";
    // $sds_admin_upload_url =  admin_url('?view=dialog');
    $sds_admin_upload_url =  controller_upload_url('&view=dialog');


    

    ?>

        var rev_php_ver = '<?php echo phpversion()?>';

	var g_uniteDirPlagin = "<?php echo self::$dir_plugin?>";

	var g_urlContent = "<?php echo UniteFunctionsWPRev::getUrlContent()?>";

       

//        var ajaxurl = g_urlContent+'ajax.php?returnurl=<?php echo urlencode(htmlspecialchars_decode($sds_admin_url))?>';
        ajaxurl += '&returnurl=<?php echo urlencode(htmlspecialchars_decode($sds_admin_url))?>';

        
        var uploadurl = '<?php echo htmlspecialchars_decode($sds_admin_upload_url)?>';



	var g_urlAjaxShowImage = "<?php echo htmlspecialchars_decode(UniteBaseClassRev::$url_ajax_showimage)?>";



	var g_urlAjaxActions = "<?php echo htmlspecialchars_decode(UniteBaseClassRev::$url_ajax_actions)?>";



	var g_settingsObj = {};

	

</script>



<div id="div_debug"></div>



<div class='unite_error_message' id="error_message" style="display:none;"></div>



<div class='unite_success_message' id="success_message" style="display:none;"></div>



<div id="viewWrapper" class="view_wrapper<?php echo $wrapperClass?>">



<?php

	self::requireView($view);

	

?>



</div>



<div id="divColorPicker" style="display:none;"></div>



<?php self::requireView("system/video_dialog")?>

<?php self::requireView("system/update_dialog")?>

<?php self::requireView("system/general_settings_dialog")?>



<div class="tp-plugin-version">&copy; All rights reserved, <a href="http://themepunch.com" target="_blank">Themepunch</a>  ver. <?php echo $revSliderVersion?>	

</div>



<?php if(GlobalsRevSlider::SHOW_DEBUG == true): ?>



	Debug Functions (for developer use only): 

	<br><br>

	

	<a id="button_update_text" class="button-primary revpurple" href="javascript:void(0)">Update Text</a>

	

<?php endif?>



