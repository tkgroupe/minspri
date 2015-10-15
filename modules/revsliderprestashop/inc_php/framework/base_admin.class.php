<?php

 class UniteBaseAdminClassRev extends UniteBaseClassRev{

		const ACTION_ADMIN_MENU = "admin_menu";

		const ACTION_ADMIN_INIT = "admin_init";	

		const ACTION_ADD_SCRIPTS = "admin_enqueue_scripts";

		const ACTION_ADD_METABOXES = "add_meta_boxes";

		const ACTION_SAVE_POST = "save_post";

		

		const ROLE_ADMIN = "admin";

		const ROLE_EDITOR = "editor";

		const ROLE_AUTHOR = "author";

		

		protected static $master_view;

		protected static $view;

		

		private static $arrSettings = array();

		private static $arrMenuPages = array();

		private static $tempVars = array();

		private static $startupError = "";

		private static $menuRole = self::ROLE_ADMIN;

		private static $arrMetaBoxes = "";
		public function __construct($mainFile,$t,$defaultView){

						

			parent::__construct($mainFile,$t);

			

			//set view

			self::$view = self::getGetVar("view");

			if(empty(self::$view))

				self::$view = $defaultView;

                        

			//add internal hook for adding a menu in arrMenus

			self::addAction(self::ACTION_ADMIN_MENU, "addAdminMenu");

//			self::addAction(self::ACTION_ADD_METABOXES, "onAddMetaboxes");

//			self::addAction(self::ACTION_SAVE_POST, "onSavePost");

			

			//if not inside plugin don't continue

			//if($this->isInsidePlugin() == true){

				self::addAction(self::ACTION_ADD_SCRIPTS, "addCommonScripts");

				self::addAction(self::ACTION_ADD_SCRIPTS, "onAddScripts");

			//}

			

			//a must event for any admin. call onActivate function.

			//$this->addEvent_onActivate();
                                
                        self::addActionAjax("ajax_action", "onAjaxAction");

			self::addActionAjax("show_image", "onShowImage");
                        
			

		}		

                public static function sds_init_error_warning(){
                    if(!(int)Configuration::get('PS_SHOP_ENABLE')){
                        echo "<div class='alert alert-warning'>Maintenance mode is enabled. This may cause functional problem at your slider revolution module.</div>";
                        if (!in_array(Tools::getRemoteAddr(), explode(',', Configuration::get('PS_MAINTENANCE_IP')))){
                            echo "<div class='alert alert-warning'>It's seemed that your IP is not present in Maintenance IP.</div>";
                        }
                    }
                    
                    if(get_magic_quotes_gpc()){
                        echo "<div class='alert alert-warning'>magic_quotes_gpc is enabled. This may cause functional problem at your slider revolution module. Please disable magic_quotes_gpc.</div>";                        
                    }
                    if(get_magic_quotes_runtime()){
                        echo "<div class='alert alert-warning'>magic_quotes_runtime is enabled. This may cause functional problem at your slider revolution module. Please disable magic_quotes_runtime.</div>";                        
                    }
                    if(!defined('ABSPATH')){
                        echo "<div class='alert alert-warning'>Fatal Error: 'ABSPATH' isn't defined.</div>";
                        return ;
                    }
                    if(!is_writable(ABSPATH.'/uploads')){
                        echo "<div class='alert alert-warning'>'".ABSPATH."/uploads' folder is not writeable. Change the folder permission.</div>";
                    }   
                    if(!is_writable(ABSPATH.'/rs-plugin/css')){
                        echo "<div class='alert alert-warning'>'".ABSPATH."/rs-plugin/css' folder is not writeable. Change the folder permission.</div>";
                    }   
//                    if(!is_executable(ABSPATH.'/rs-plugin/fileuploader/uploadify.php')){
//                        echo "<div class='alert alert-warning'>'".ABSPATH."/rs-plugin/fileuploader/uploadify.php' file is not executable. Change the file permission.</div>";
//                    }   
                    if(!is_writable(ABSPATH.'/cache')){
                        echo "<div class='alert alert-warning'>'".ABSPATH."/cache' folder is not writeable. Change the folder permission.</div>";
                    }   
                    
                    
                    
                }


                public static function delete_uploaded_file($data){

                    if(!isset(self::$wpdb))

                        $wpdb = RevsliderPrestashop::$wpdb;

                    else

                        $wpdb = self::$wpdb;

                    

                    $imgdir = ABSPATH . '/uploads/';

                    if(isset($data['img']) && !empty($data['img']) && file_exists("{$imgdir}/{$data['img']}")){

                        

                        $filename = $data['img'];

                        $thumbsize = GlobalsRevSlider::IMAGE_SIZE_THUMBNAIL;

                        $mediumsize = GlobalsRevSlider::IMAGE_SIZE_MEDIUM;

                        $largesize = GlobalsRevSlider::IMAGE_SIZE_LARGE;

                        $filerealname = substr($filename,0,strrpos($filename,'.'));

                        $fileext = substr($filename,strrpos($filename,'.'),strlen($filename)-strlen($filerealname));

                        

                        $images = array($filename);                        

                        $images[] = "{$filerealname}-{$thumbsize}x{$thumbsize}{$fileext}";

                        $images[] = "{$filerealname}-{$mediumsize}x{$mediumsize}{$fileext}";

                        $images[] = "{$filerealname}-{$largesize}x{$largesize}{$fileext}";

                        foreach($images as $image)

                            @unlink("{$imgdir}{$image}");

                            

                        $tablename = $wpdb->prefix.GlobalsRevSlider::TABLE_ATTACHMENT_IMAGES;

                        if($wpdb->query("DELETE FROM {$tablename} WHERE file_name='{$filename}'"))                                

                        echo json_encode(array(

                            'success'=>'1',

                            'output'=>self::get_uploaded_files_markup(self::get_uploaded_files_result())

                            ));

                        die();

                    }

                }

                public static function get_latest_uploaded_image(){

                    if(!isset(self::$wpdb))

                        $wpdb = RevsliderPrestashop::$wpdb;

                    else

                        $wpdb = self::$wpdb;

                    $tablename = $wpdb->prefix.GlobalsRevSlider::TABLE_ATTACHMENT_IMAGES;

//                    $latest = $wpdb->get_results("SELECT file_name FROM {$tablename} ORDER BY ID DESC LIMIT 1");

//                    if(isset($latest[0]->file_name))

//                        return $latest[0]->file_name;

                    
                    
                    $latest = $wpdb->get_var("SELECT file_name FROM {$tablename} ORDER BY ID DESC");
                    
                    if(!empty($latest))

                        return $latest;

                    

                    return '';

                }

                

                public static function get_uploaded_files_json(){

                    

                    echo json_encode (array(

                        'success'=>'1',

                        'latest' => self::get_latest_uploaded_image(),

                        'output'=>self::get_uploaded_files_markup(self::get_uploaded_files_result())

                        ));

                    die();

                }

                

                public static function get_uploaded_files_result(){

                    if(!isset(self::$wpdb))

                        $wpdb = RevsliderPrestashop::$wpdb;

                    else

                        $wpdb = self::$wpdb;

                    

                    $tablename = $wpdb->prefix.GlobalsRevSlider::TABLE_ATTACHMENT_IMAGES;

                    $db_results = $wpdb->get_results("SELECT * FROM {$tablename}");


                    $imgdir = ABSPATH . '/uploads/';

                    $results = array();

                    

                    if (is_dir($imgdir) && !empty($db_results)) {

                        

                        foreach($db_results as $dres){

                            $dres = (object)$dres;
                            
                            if(isset($dres->file_name) &&

                            !empty($dres->file_name) &&

                            file_exists($imgdir.$dres->file_name)){                                

                                $results["{$dres->ID}"] = $dres->file_name;                                

                            }                            

                        }                        

                        //$results = scandir($imgdir, 1);                       

                    }                    

                    //print_r($results);

                    return $results;

                }

                public static function bac_get_uploaded_files_markup($results = array()){

                    $url = uploads_url();

                    ob_start();

                     if (!empty($results)): ?><ul id="selectable" class=""><?php

                        $num = 0;

                        foreach ( $results as $id => $filename):

                            //$img = $results[$num];

                            $thumbsize = GlobalsRevSlider::IMAGE_SIZE_THUMBNAIL;

                            $mediumsize = GlobalsRevSlider::IMAGE_SIZE_MEDIUM;

                            $largesize = GlobalsRevSlider::IMAGE_SIZE_LARGE;

                            $filerealname = substr($filename,0,strrpos($filename,'.'));

                            $fileext = substr($filename,strrpos($filename,'.'),strlen($filename)-strlen($filerealname));

                            $thumbimg = $img = "{$filerealname}-{$thumbsize}x{$thumbsize}{$fileext}";

                            $mediumimg = "{$filerealname}-{$mediumsize}x{$mediumsize}{$fileext}";

                            $largeimg = "{$filerealname}-{$largesize}x{$largesize}{$fileext}";

                            

                            if (++$num % 4 === 1):

                                ?><li data-image="<?php echo $filename?>" data-large="<?php echo $largeimg ?>" data-medium="<?php echo $mediumimg ?>" data-thumb="<?php echo $thumbimg ?>" class="last"><a href="#"><img alt="<?php echo $img ?>" src="<?php echo $url . $img ?>" /></a></li><?php 

                            else:

                                ?><li data-image="<?php echo $filename?>" data-large="<?php echo $largeimg ?>" data-medium="<?php echo $mediumimg ?>" data-thumb="<?php echo $thumbimg ?>"><a  href="#"><img alt="<?php echo $img ?>" src="<?php echo $url . $img ?>" /></a></li><?php 

                            endif;

                        endforeach; ?></ul><?php 

                    endif; 

                    $content = ob_get_contents();

                    ob_end_clean();

                    return $content;

                }

				public static function get_uploaded_files_markup($results = array()){
$lan_iso = Context::getcontext()->language->iso_code;
include_once(_PS_ROOT_DIR_.'/modules/revsliderprestashop/views/config/config.php');
// include_once(_PS_ROOT_DIR_.'/modules/revsliderprestashop/views/lang/'.$lan_iso.'.php');
include_once(_PS_ROOT_DIR_.'/modules/revsliderprestashop/views/include/utils.php');

$upload_dir = __PS_BASE_URI__.'modules/revsliderprestashop/uploads/';

$current_path = _PS_ROOT_DIR_.'/modules/revsliderprestashop/uploads/';
                    
                    $url = uploads_url();

                    ob_start();

                     if (!empty($results)): ?>
                     <div id="divImageList" > <ul id="selectable" class="">
                     <?php

                        $num = 0;

                        foreach ( $results as $id => $filename):

                            //$img = $results[$num];

                            $thumbsize = GlobalsRevSlider::IMAGE_SIZE_THUMBNAIL;

                            $mediumsize = GlobalsRevSlider::IMAGE_SIZE_MEDIUM;

                            $largesize = GlobalsRevSlider::IMAGE_SIZE_LARGE;

                            $filerealname = substr($filename,0,strrpos($filename,'.'));

                            $fileext = substr($filename,strrpos($filename,'.'),strlen($filename)-strlen($filerealname));

                            $thumbimg = $img = "{$filerealname}-{$thumbsize}x{$thumbsize}{$fileext}";

                            $mediumimg = "{$filerealname}-{$mediumsize}x{$mediumsize}{$fileext}";

                            $largeimg = "{$filerealname}-{$largesize}x{$largesize}{$fileext}";
                            $file_path = 	$file_path = $current_path.$largeimg;
 
							$date = filemtime($file_path);
							$size = filesize($file_path);
							// $file_ext = substr(strrchr($file, '.'), 1);
							$file_infos = pathinfo($file_path);
							$file_ext = $file_infos['extension'];
							// $sorted[$k] = array('file' => $file, 'date' => $date, 'size' => $size, 'extension' => $file_ext);
							$extension_lower = fix_strtolower($file_ext);
 
							$is_img = true;
                            
							 list($img_width, $img_height, $img_type, $attr) = getimagesize($file_path);

                            // if (++$num % 4 === 1):

                                ?>
<li data-image="<?php echo $filename?>" data-image-id="<?php echo $id?>" data-large="<?php echo $upload_dir . $img ?>" data-medium="<?php echo $upload_dir . $img ?>" data-thumb="<?php echo $upload_dir . $img ?>" class="ff-item-type-2 file">
		<figure data-type="img" data-name="1117858_1577750_graph-1024x1024.png">
			<a data-function="apply" data-field_id="<?php echo $id?>" data-file="<?php echo $upload_dir . $img ?>" class="link" href="javascript:void('')">
				<div class="img-precontainer">
										<div class="img-container">
						<span></span>
						<img alt="<?php echo $img ?>" src="<?php echo $upload_dir . $img ?>"  class="original "  >
					</div>
				</div>
				<div class="img-precontainer-mini original-thumb">
					<div class="filetype png hide">png</div>
					<div class="img-container-mini">
						<span></span>
						<img src="<?php echo $upload_dir . $img ?>" class=" " alt="<?php echo $img ?> thumbnails">
											</div>
				</div>
							</a>

			<div class="box">
				<h4 class="ellipsis">
					<a data-function="apply" data-field_id="" data-file="<?php echo $img ?>" class="link" href="javascript:void('')">
						<?php echo $img ?></a></h4>
			</div>
			<?php $date =  filemtime($current_path.$img); ?>
			<input type="hidden" class="date" value="<?php echo $date; ?>"/>
			<input type="hidden" class="size" value="<?php echo $size ?>"/>
			<input type="hidden" class="extension" value="<?php echo $extension_lower; ?>"/>
			<input type="hidden" class="name" value=""/>

			<div class="file-date"><?php echo date(lang_Date_type, $date); ?></div>
			<div class="file-size"><?php echo makeSize($size); ?></div>
			<div class='img-dimension'><?php if ($is_img)
				{
					echo $img_width."x".$img_height;
				} ?></div>
			<div class='file-extension'><?php echo Tools::safeOutput($extension_lower); ?></div>

			<figcaption>
				
			</figcaption>
		</figure>
	</li>
                                <?php 

                            // else:

                                ?>
                              <!--   <li data-image="<?php echo $filename?>" data-large="<?php echo $largeimg ?>" data-medium="<?php echo $mediumimg ?>" data-thumb="<?php echo $thumbimg ?>"><a  href="#"><img alt="<?php echo $img ?>" src="<?php echo $url . $img ?>" /></a></li> -->
                                <?php 

                            // endif;

                        endforeach; ?></ul></div><?php 

                    endif; 

                    $content = ob_get_contents();

                    ob_end_clean();

                    return $content;

                }


		public static function addMetaBox($title,$content = null, $customDrawFunction = null,$location="post"){

			

			$box = array();

			$box["title"] = $title;

			$box["location"] = $location;

			$box["content"] = $content;

			$box["draw_function"] = $customDrawFunction;

			

			self::$arrMetaBoxes[] = $box;			

		}


		public static function onAddMetaboxes(){

			

			foreach(self::$arrMetaBoxes as $index=>$box){

				

				$title = $box["title"];

				$location = $box["location"];

				

				$boxID = "mymetabox_".self::$dir_plugin.'_'.$index;

				$function = array(self::$t, "onAddMetaBoxContent");

				

				if(is_array($location)){

					foreach($location as $loc)

						add_meta_box($boxID,$title,$function,$loc,'normal','default');

				}else

			    	add_meta_box($boxID,$title,$function,$location,'normal','default');

			}

		}


		public static function onSavePost(){

			

			//protection against autosave

			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){

				$postID = UniteFunctionsRev::getPostVariable("ID");

	        	return $postID;

			}

			

			$postID = UniteFunctionsRev::getPostVariable("ID");

			if(empty($postID))

				return(false);

				

				

			foreach(self::$arrMetaBoxes as $box){

				$content = UniteFunctionsRev::getVal($box, "content");

				if(gettype($content) != "object")

					continue;

					

				$arrSettingNames = $content->getArrSettingNames();

				foreach($arrSettingNames as $name){

					$value = UniteFunctionsRev::getPostVariable($name);

					update_post_meta( $postID, $name, $value );

				}	//end foreach settings



			} 

			

		}

	
		public static function onAddMetaBoxContent($post,$boxData){

			

			$postID = $post->ID;

			

			$boxID = UniteFunctionsRev::getVal($boxData, "id");

			$index = str_replace("mymetabox_".self::$dir_plugin.'_',"",$boxID);

			

			$arrMetabox = self::$arrMetaBoxes[$index];

			

			$content = UniteFunctionsRev::getVal($arrMetabox, "content");

			

			$contentType = getType($content);

			switch ($contentType){

				case "string":

					echo $content;

				break;

				default:		//settings object					

					$output = new UniteSettingsProductSidebarRev();

					$output->setDefaultInputClass(UniteSettingsProductSidebarRev::INPUT_CLASS_LONG);					

					$content->updateValuesFromPostMeta($postID);										

					$output->init($content);



					//draw element

					$drawFunction = UniteFunctionsRev::getVal($arrMetabox, "draw_function");

					if(!empty($drawFunction))

						call_user_func($drawFunction,$output);

					else

						$output->draw();

						

				break;

			}

			

		}



		public static function setMenuRole($menuRole){

			self::$menuRole = $menuRole;

		}

	

		public static function setStartupError($errorMessage){

			self::$startupError = $errorMessage;

		}

	

		private function isInsidePlugin(){

			$page = self::getGetVar("page");

			if($page == self::$dir_plugin)

				return(true);

			return(false);

		} 

		

		

		
		public static function addCommonScripts(){


			if(GlobalsRevSlider::$isNewVersion){

			}else{	
			}

		

		}

		

		

		public static function adminPages(){

			

		}

		



		protected static function isAdminPermissions(){

			

			if( is_admin())

				return(true);

				

			return(false);

		}

	

		protected static function validateAdminPermissions(){

			if(!self::isAdminPermissions()){

				echo "access denied";

				return(false);

			}			

		}

	

		protected static function setMasterView($masterView){

			self::$master_view = $masterView;

		}

		

	

		protected static function requireView($view){

			try{

				//require master view file, and 

				if(!empty(self::$master_view) && !isset(self::$tempVars["is_masterView"]) ){

					$masterViewFilepath = self::$path_views.self::$master_view.".php";

					UniteFunctionsRev::validateFilepath($masterViewFilepath,"Master View");

					

					self::$tempVars["is_masterView"] = true;

					require $masterViewFilepath;

				}

				else{		//simple require the view file.

					$viewFilepath = self::$path_views.$view.".php";

					

					UniteFunctionsRev::validateFilepath($viewFilepath,"View");

					require $viewFilepath;

				}

				

			}catch (Exception $e){

				echo "<br><br>View ($view) Error: <b>".$e->getMessage()."</b>";

				

				if(self::$debugMode == true)

					dmp($e->getTraceAsString());

			}

		}

		

	

		protected static function getPathTemplate($templateName){

			

			$pathTemplate = self::$path_templates.$templateName.".php";

			UniteFunctionsRev::validateFilepath($pathTemplate,"Template");

			

			return($pathTemplate);

		}

		

	

		public static function requireSettings($settingsFile){

			

			try{                                

				require self::$path_plugin."settings/$settingsFile.php";

                                

			}catch (Exception $e){

				echo "<br><br>Settings ($settingsFile) Error: <b>".$e->getMessage()."</b>";

				dmp($e->getTraceAsString());

			}

		}

		


		protected static function getSettingsFilePath($settingsFile){

			

			$filepath = self::$path_plugin."settings/$settingsFile.php";

			return($filepath);

		}

		

		



		protected static function addMediaUploadIncludes(){

			

			self::addWPScript("thickbox");

			self::addWPStyle("thickbox");

			self::addWPScript("media-upload");

			

		}

		



		public static function addAdminMenu(){

			

			$role = "manage_options";

			

			switch(self::$menuRole){

				case self::ROLE_AUTHOR:

					$role = "edit_published_posts";

				break;

				case self::ROLE_EDITOR:

					$role = "edit_pages";

				break;		

				default:		

				case self::ROLE_ADMIN:

					$role = "manage_options";

				break;

			}

			//var_dump(self::$arrMenuPages);

			foreach(self::$arrMenuPages as $menu){

				$title = $menu["title"];

				$pageFunctionName = $menu["pageFunction"];


                                call_user_func(array(self::$t, $pageFunctionName));

			}

			

		}

		


		protected static function addMenuPage($title,$pageFunctionName){

						

			self::$arrMenuPages[] = array("title"=>$title,"pageFunction"=>$pageFunctionName);

			

		}





		public static function getViewUrl($viewName,$urlParams=""){
                    $params = "&view=".$viewName;
                    if(!empty($urlParams))
                            $params .= "&".$urlParams;
                    if(isset($_GET['returnurl'])){
                        $link = urldecode($_GET['returnurl']);
                        $links = explode('&view=',$link);
                        $link = $links[0];
                        $link .= "&view={$viewName}";
                        $link .= "&{$urlParams}";   
                    }
                    else{                        
                        $link = admin_url( "admin.php?page=".self::$dir_plugin.$params);
                    }
                    return htmlspecialchars_decode(urldecode($link));
		}

		


		protected function addEvent_onActivate($eventFunc = "onActivate"){


		}


		protected static function storeSettings($key,$settings){

			self::$arrSettings[$key] = $settings;

		}

		protected static function getSettings($key){

			if(!isset(self::$arrSettings[$key]))

				UniteFunctionsRev::throwError("Settings $key not found");

			$settings = self::$arrSettings[$key];

			return($settings);

		}

		


		protected static function addActionAjax($ajaxAction,$eventFunction){

			self::addAction('wp_ajax_'.self::$dir_plugin."_".$ajaxAction, $eventFunction);

			self::addAction('wp_ajax_nopriv_'.self::$dir_plugin."_".$ajaxAction, $eventFunction);

		}


		private static function ajaxResponse($success,$message,$arrData = null){

			

			$response = array();			

			$response["success"] = $success;				

			$response["message"] = $message;

			

			if(!empty($arrData)){

				

				if(gettype($arrData) == "string")

					$arrData = array("data"=>$arrData);				

				

				$response = array_merge($response,$arrData);

			}

				

			$json = json_encode($response);

			

			echo $json;

                        return ;

			//exit();

		}



		protected static function ajaxResponseData($arrData){

			if(gettype($arrData) == "string")

				$arrData = array("data"=>$arrData);

			

			self::ajaxResponse(true,"",$arrData);

		}


		protected static function ajaxResponseError($message,$arrData = null){

			

			self::ajaxResponse(false,$message,$arrData,true);

		}



		protected static function ajaxResponseSuccess($message,$arrData = null){

			

			self::ajaxResponse(true,$message,$arrData,true);

			

		}


		protected static function ajaxResponseSuccessRedirect($message,$url){

			$arrData = array("is_redirect"=>true,"redirect_url"=>$url);

			

			self::ajaxResponse(true,$message,$arrData,true);

		}

		



		protected static function updatePlugin($viewBack = false){

			$linkBack = self::getViewUrl($viewBack);

			$htmlLinkBack = UniteFunctionsRev::getHtmlLink($linkBack, "Go Back");			 

			if(UniteFunctionsWPRev::isDBTableExists(GlobalsRevSlider::TABLE_CSS_NAME)){

				$captions = RevOperations::getCaptionsCssContentArray();                                

				if($captions === false){

					$message = "CSS parse error! Please make sure your captions.css is valid CSS before updating the plugin!";

					echo "<div style='color:#B80A0A;font-size:18px;'><b>Update Error: </b> $message</div><br>";

					echo $htmlLinkBack;

					exit();

				}

			}

			

			$zip = new UniteZipRev();

						

			try{

				

				if(function_exists("unzip_file") == false){					

					if( UniteZipRev::isZipExists() == false)

						UniteFunctionsRev::throwError("The ZipArchive php extension not exists, can't extract the update file. Please turn it on in php ini.");

				}

				

				dmp("Update in progress...");

				

				$arrFiles = UniteFunctionsRev::getVal($_FILES, "update_file");

				if(empty($arrFiles))

					UniteFunctionsRev::throwError("Update file don't found.");

					

				$filename = UniteFunctionsRev::getVal($arrFiles, "name");

				

				if(empty($filename))

					UniteFunctionsRev::throwError("Update filename not found.");

				$fileType = UniteFunctionsRev::getVal($arrFiles, "type");

				$filepathTemp = UniteFunctionsRev::getVal($arrFiles, "tmp_name");

				if(file_exists($filepathTemp) == false)

					UniteFunctionsRev::throwError("Can't find the uploaded file.");	




				UniteFunctionsRev::checkCreateDir(self::$path_temp);


				$pathUpdate = self::$path_temp."update_extract/";				

				UniteFunctionsRev::checkCreateDir($pathUpdate);


				if(is_dir($pathUpdate)){ 

					$arrNotDeleted = UniteFunctionsRev::deleteDir($pathUpdate,false);

					if(!empty($arrNotDeleted)){

						$strNotDeleted = print_r($arrNotDeleted,true);

						UniteFunctionsRev::throwError("Could not delete those files from the update folder: $strNotDeleted");

					}

				}

				

				//copy the zip file.

				$filepathZip = $pathUpdate.$filename;

				

				$success = move_uploaded_file($filepathTemp, $filepathZip);

				if($success == false)

					UniteFunctionsRev::throwError("Can't move the uploaded file here: ".$filepathZip.".");

				

				if(function_exists("unzip_file") == true){

					WP_Filesystem();

					$response = unzip_file($filepathZip, $pathUpdate);

				}

				else					

					$zip->extract($filepathZip, $pathUpdate);

				

				//get extracted folder

				$arrFolders = UniteFunctionsRev::getFoldersList($pathUpdate);

				if(empty($arrFolders))

					UniteFunctionsRev::throwError("The update folder is not extracted");

				

				if(count($arrFolders) > 1)

					UniteFunctionsRev::throwError("Extracted folders are more then 1. Please check the update file.");

					

				//get product folder

				$productFolder = $arrFolders[0];

				if(empty($productFolder))

					UniteFunctionsRev::throwError("Wrong product folder.");

					

				if($productFolder != self::$dir_plugin)

					UniteFunctionsRev::throwError("The update folder don't match the product folder, please check the update file.");

				

				$pathUpdateProduct = $pathUpdate.$productFolder."/";				

				

				//check some file in folder to validate it's the real one:

				$checkFilepath = $pathUpdateProduct.$productFolder.".php";

				if(file_exists($checkFilepath) == false)

					UniteFunctionsRev::throwError("Wrong update extracted folder. The file: ".$checkFilepath." not found.");


				$pathOriginalPlugin = self::$path_plugin;

				$arrBlackList = array();

				$arrBlackList[] = "rs-plugin/css/captions.css";

				$arrBlackList[] = "rs-plugin/css/dynamic-captions.css";

				$arrBlackList[] = "rs-plugin/css/static-captions.css";

				

				UniteFunctionsRev::copyDir($pathUpdateProduct, $pathOriginalPlugin,"",$arrBlackList);

				UniteFunctionsRev::deleteDir($pathUpdate);
				dmp("Updated Successfully, redirecting...");
				echo "<script>location.href='$linkBack'</script>";
			}catch(Exception $e){
				$message = $e->getMessage();
				$message .= " <br> Please update the plugin manually via the ftp";
				echo "<div style='color:#B80A0A;font-size:18px;'><b>Update Error: </b> $message</div><br>";
				echo $htmlLinkBack;
				exit();
			}
		}
 }
 ?>