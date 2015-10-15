	<div class="wrap settings_wrap">
		<div class="clear_both"></div>
			<div class="title_line">
				<div id="icon-options-general" class="icon32"></div>
				<?php
				if($sliderTemplate){
					?>
					<div class="view_title"><i class="revicon-pencil-1"></i><?php echo RevsliderPrestashop::$lang['New_Slider_Temp'];  ?></div>
					<?php
					$template_value = 'true';
				}else{
					?>
					<div class="view_title"><i class="revicon-pencil-1"></i><?php echo RevsliderPrestashop::$lang['New_Sldr'];  ?></div>
					<?php
					$template_value = 'false';
				}
				?>
				<input type="hidden" id="revslider_template" value="<?php echo $template_value; ?>"></input>

				<a href="<?php echo GlobalsRevSlider::LINK_HELP_SLIDER?>" class="button-secondary float_right mtop_10 mleft_10" target="_blank"><?php echo RevsliderPrestashop::$lang['help'];  ?></a>

			</div>
			<div class="settings_panel">
				<div class="settings_panel_left">
					<div id="main_dlier_settings_wrapper" class="postbox unite-postbox ">
					  <h3 class="box-closed"><span><?php echo RevsliderPrestashop::$lang['Main_Slider_Settings'];  ?></span></h3>
					  <div class="p10">
					  
							<?php $settingsSliderMain->draw("form_slider_main",true)?>

							<?php require self::getPathTemplate("multi-shop"); ?>

							<div id="layout-preshow">
								<strong>Layout Example</strong><?php echo RevsliderPrestashop::$lang['theme_style'];  ?>
								<div class="divide20"></div>
								<div id="layout-preshow-page">
									<div class="layout-preshow-text"><?php echo RevsliderPrestashop::$lang['BROWSER'];  ?></div>
									<div id="layout-preshow-theme">
											<div class="layout-preshow-text"><?php echo RevsliderPrestashop::$lang['PAGE'];  ?></div>
									</div>
									<div id="layout-preshow-slider">
											<div class="layout-preshow-text"><?php echo RevsliderPrestashop::$lang['SLIDER'];  ?></div>
									</div>
									<div id="layout-preshow-grid">
											<div class="layout-preshow-text"><?php echo RevsliderPrestashop::$lang['LAYERS_GRID'];  ?></div>		
									</div>
								</div>
							</div>
							
							<div class="divide20"></div>
							<a id="button_save_slider" class='button-primary revgreen' href='javascript:void(0)' ><i class="revicon-cog"></i><span id="create_slider_text"><?php echo RevsliderPrestashop::$lang['Create_Slider'];  ?></span></a>

							<span class="hor_sap"></span>
							<a id="button_cancel_save_slider" class='button-primary revred' href='<?php echo self::getViewUrl("sliders") ?>' ><i class="revicon-cancel"></i><?php echo RevsliderPrestashop::$lang['Close'];  ?> </a>
					  </div>
					</div>
				</div>
				<div class="settings_panel_right">
					<?php $settingsSliderParams->draw("form_slider_params",true); ?>
				</div>
				<div class="clear"></div>
			</div>
	</div>

	<script type="text/javascript">
		var g_jsonTaxWithCats = <?php echo $jsonTaxWithCats?>;

		jQuery(document).ready(function(){

			RevSliderAdmin.initAddSliderView();
			
			<?php if($sliderTemplate){ ?>
			RevSliderAdmin.initSliderViewTemplate();
			<?php } ?>
		});
	</script>

