<?php

if(!$outputTemplates){
	$limit = (@intval($_GET['limit']) > 0) ? @intval($_GET['limit']) : 500;
	$otype = 'reg';
}else{
	$limit = (@intval($_GET['limit_t']) > 0) ? @intval($_GET['limit_t']) : 500;
	$otype = 'temp';
}
$total = 0;
if(!$no_sliders){
?>
	<table class='wp-list-table widefat fixed unite_table_items'>
		<thead>
			<tr>
				<th width='20px'><?php echo RevsliderPrestashop::$lang['ID'];  ?></th>
				<th width='25%'><?php echo RevsliderPrestashop::$lang['Name'];  ?><a href="?page=revslider&order=asc&ot=name&type=<?php echo $otype; ?>" class="eg-icon-down-dir"></a> <a href="?page=revslider&order=desc&ot=name&type=<?php echo $otype; ?>" class="eg-icon-up-dir"></a></th>
				<th width='100'><?php echo RevsliderPrestashop::$lang['Source'];  ?></th>
				<?php if(!$outputTemplates): ?>
				<th width='150px'><?php echo RevsliderPrestashop::$lang['Display_Hook'];  ?></th>
				<?php endif; ?>
				<?php if($outputTemplates): ?>
				<th width='70px'><?php echo RevsliderPrestashop::$lang['N_Slides'];  ?></th>
				<?php endif; ?>
				<th width='50%'><?php echo RevsliderPrestashop::$lang['Actions'];  ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			if($outputTemplates){
				$useSliders = $arrSlidersTemplates;
				$pagenum = isset( $_GET['pagenumt'] ) ? absint( $_GET['pagenumt'] ) : 1;
				$offset = ( $pagenum - 1 ) * $limit;
			}else{
				$useSliders = $arrSliders;
				$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
				$offset = ( $pagenum - 1 ) * $limit;
			}
			
			$cur_offset = 0;
			$current_id_shop_group = Context::getcontext()->shop->id_shop_group;
			$current_shop_id = Context::getcontext()->shop->id;
			$shop_context = Context::getcontext()->cookie->shopContext;
			if(!empty($shop_context) && isset($shop_context)){
				$shop_contexttype = substr($shop_context,0,1);
			}else{
				$shop_contexttype = 'a';
			}
			foreach($useSliders as $slider){
					if($outputTemplates){
						$slider->arrParams['id_shop'] = $current_shop_id;
					}
				$id_shop = @$slider->arrParams['id_shop'];
				$id_shop_group = Shop::getGroupFromShop($id_shop);
				if(($current_shop_id == $id_shop && $shop_contexttype == 's') || ($id_shop_group == $current_shop_id && $shop_contexttype == 'g') ||  $shop_contexttype == 'a' || !isset($slider->arrParams['id_shop'])) //start Filter
				{
				$total++;
				$cur_offset++;
				if($cur_offset <= $offset) continue; //if we are lower then the offset, continue;
				if($cur_offset > $limit + $offset) continue; // if we are higher then the limit + offset, continue
				try{
					$id = $slider->getID();
					$showTitle = $slider->getShowTitle();
					$title = $slider->getTitle();
					$alias = $slider->getAlias();
					$isFromPosts = $slider->isSlidesFromPosts();
					$strSource = RevsliderPrestashop::$lang['Gallery'];
					$preicon = "revicon-picture-1";
					if($outputTemplates) $strSource = "Template";
					if ($strSource=="Template") $preicon ="templateicon";
					$rowClass = "";					
					if($isFromPosts == true){
						$strSource = RevsliderPrestashop::$lang['Posts'];
						$preicon ="revicon-doc";
						$rowClass = "class='row_alt'";
					}
					if($outputTemplates){
						$editLink = self::getViewUrl(RevSliderAdmin::VIEW_SLIDER_TEMPLATE,"id=$id");
					}else{
						$editLink = self::getViewUrl(RevSliderAdmin::VIEW_SLIDER,"id=$id");
					}
					$editSlidesLink = self::getViewUrl(RevSliderAdmin::VIEW_SLIDES,"id=$id");
					$showTitle = UniteFunctionsRev::getHtmlLink($editLink, $showTitle);
					$shortCode = $slider->getShortcode();
					$numSlides = $slider->getNumSlides();
				}catch(Exception $e){
					$errorMessage = "ERROR: ".$e->getMessage();
					$strSource = "";
					$numSlides = "";
				}
				?>
				<tr <?php echo $rowClass?>>
					<td><?php echo $id?><span id="slider_title_<?php echo $id?>" class="hidden"><?php echo $title?></span></td>								
					<td>
						<?php echo $showTitle?>
						<?php if(!empty($errorMessage)):?>
							<div class='error_message'><?php echo $errorMessage?></div>
						<?php endif?>
					</td>
					<td><?php echo "<i class=".$preicon."></i>".$strSource?>
					<?php if(!$outputTemplates):
						echo ' ('.(int)$numSlides.')';
					endif; 	?>
					</td>
					<?php if(!$outputTemplates): ?>
					<td><?php 
					$hooks = $slider->getParam('displayhook');
					if(isset($hooks) && !empty($hooks)){
						echo $hooks;
					}else{
						echo 'Select Hook';
					}
					?></td>
					<?php endif; ?>
					<?php if($outputTemplates): ?>
					<td><?php echo $numSlides?></td>
					<?php endif; ?>
					<td>
						<a class="button-primary revgreen" href='<?php echo $editLink ?>' title=""><i class="revicon-cog"></i><?php echo RevsliderPrestashop::$lang['Settings'];  ?></a>
						<a class="button-primary revblue" href='<?php echo $editSlidesLink ?>' title=""><i class="revicon-pencil-1"></i><?php echo RevsliderPrestashop::$lang['Edit_Slides'];  ?></a>
						<a class="button-primary revcarrot export_slider_overview" id="export_slider_<?php echo $id?>" href="javascript:void(0);" title=""><i class="revicon-export"></i><?php echo RevsliderPrestashop::$lang['Export_Slider'];  ?></a>
						<?php
						$generalSettings = self::getSettings("general");
						$show_dev_export = $generalSettings->getSettingValue("show_dev_export",'off');
						if($show_dev_export == 'on'){
							?>
							<a class="button-primary revpurple export_slider_standalone" id="export_slider_standalone_<?php echo $id?>" href="javascript:void(0);" title=""><i class="revicon-export"></i><?php echo RevsliderPrestashop::$lang['HTML'];  ?></a>
							<?php
						}
						?>
						<a class="button-primary revred button_delete_slider"id="button_delete_<?php echo $id?>" href='javascript:void(0)' title="<?php echo RevsliderPrestashop::$lang['Delete'];  ?>"><i class="revicon-trash"></i></a>
						<a class="button-primary revyellow button_duplicate_slider" id="button_duplicate_<?php echo $id?>" href='javascript:void(0)' title="<?php echo RevsliderPrestashop::$lang['Duplicate'];  ?>"><i class="revicon-picture"></i></a>
						<div id="button_preview_<?php echo $id?>" class="button_slider_preview button-primary revgray" title="<?php echo RevsliderPrestashop::$lang['Preview'];  ?>"><i class="revicon-search-1"></i></div>
					</td>
	
				</tr>							
				<?php
				} //End Filter
			}
			?>
		</tbody>		 
	</table>
<?php
}
?>	
	<p>
		<div style="float: left;">
			<?php
			if($outputTemplates){
				?>
				<a class='button-primary revblue' href='<?php echo $addNewTemplateLink?>'><?php echo RevsliderPrestashop::$lang['New_Template_Slider'];  ?></a>
				<?php
			}else{
				?>		
				<a class='button-primary revblue' href='<?php echo $addNewLink?>'><?php echo RevsliderPrestashop::$lang['New_Slider'];  ?></a>
				<?php
			}
			?>
		</div>
		<?php
			
		if(!$outputTemplates){
			?>
			<div style="float: right;"><a id="button_import_slider" class='button-primary float_right revgreen' href='javascript:void(0)'><?php echo RevsliderPrestashop::$lang['Import_Slider'];  ?> </a></div>
			<?php
		}
		?>
		<div style="clear:both; height:10px"></div>
	</p>
	<?php require_once self::getPathTemplate("dialog_preview_slider");?>


	