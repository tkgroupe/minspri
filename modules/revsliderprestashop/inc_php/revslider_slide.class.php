<?php



	class RevSlide extends UniteElementsBaseRev{

		

		private $id;
		private $sliderID;
		private $slideOrder;		
		
		private $imageUrl;
		private $imageID;		
		private $imageThumb;		
		private $imageFilepath;
		private $imageFilename;
		
		private $params;
		private $arrLayers;
		private $arrChildren = null;
		private $slider;
		
		private $static_slide = false;
		
		private $postData;
		private $templateID;
		
		public function __construct(){
			parent::__construct();
		}

		

		
		public function initByData($record){
			if(isset($record["id"])){
				$this->id = $record["id"];
			}
			if(isset($record["slider_id"])){
				$this->sliderID = $record["slider_id"];
			}
			if(isset($record["slide_order"])){
				$this->slideOrder = $record["slide_order"];
			}
			$params = $record["params"];
                        if (get_magic_quotes_gpc()) { //changes made 1st Apr 2014
                            $params = stripslashes($params);
                        }
			$params = (array)json_decode($params);
			$layers = $record["layers"];
                        if (get_magic_quotes_gpc()) { //changes made 1st Apr 2014
                            $layers = stripslashes($layers);
                        }
			$layers = (array)json_decode($layers);
			$layers = UniteFunctionsRev::convertStdClassToArray($layers);
			$imageID = UniteFunctionsRev::getVal($params, "image_id");
			//get image url and thumb url
			if(!empty($imageID)){
				$this->imageID = $imageID;
				$imageUrl = UniteFunctionsWPRev::getUrlAttachmentImage($imageID);
				if(empty($imageUrl))
					$imageUrl = UniteFunctionsRev::getVal($params, "image");
				$this->imageThumb = UniteFunctionsWPRev::getUrlAttachmentImage($imageID,UniteFunctionsWPRev::THUMB_MEDIUM);
			}else{
				$imageUrl = UniteFunctionsRev::getVal($params, "image");
			}
			if(is_ssl()){
				$imageUrl = str_replace("http://", "https://", $imageUrl);
			}
			//dmp($imageUrl);exit();
			//set image path, file and url
			$this->imageUrl = $imageUrl;
			$this->imageFilepath = UniteFunctionsWPRev::getImagePathFromURL($this->imageUrl);
		    $realPath = UniteFunctionsWPRev::getPathContent().$this->imageFilepath;
		    if(file_exists($realPath) == false || is_file($realPath) == false)
		    	$this->imageFilepath = "";
			$this->imageFilename = basename($this->imageUrl);
			$this->params = $params;
			$ijk = 0;
			foreach ($layers as $layer) {
				if(isset($layer['image_url']) && !empty($layer['image_url'])){
					$layers[$ijk]['image_url'] = modify_image_url($layers[$ijk]['image_url']);
				}
 			
				$ijk++;
			}
			$this->arrLayers = $layers;
		}

		private function initBySlide(RevSlide $slide){


			$this->id = "template";

			$this->templateID = $slide->getID();

			$this->sliderID = $slide->getSliderID();

			$this->slideOrder = $slide->getOrder();

			$this->imageUrl = $slide->getImageUrl();

			$this->imageID = $slide->getImageID();

			$this->imageThumb = $slide->getThumbUrl();		

			$this->imageFilepath = $slide->getImageFilepath();

			$this->imageFilename = $slide->getImageFilename();

			$this->params = $slide->getParams();

			$this->arrLayers = $slide->getLayers();

			$this->arrChildren = $slide->getArrChildrenPure();

		}
		// public function getRevSliderid(){

		// }
		public function initByPostData($postData, RevSlide $slideTemplate, $sliderID){

			$this->postData = $this->postData;
			
			$postID = $postData['id_product'];

			$arrWildcardsValues = RevOperations::getPostWilcardValues($postID);

			$slideTemplateID = UniteFunctionsRev::getVal($arrWildcardsValues, "slide_template");

			if(!empty($slideTemplateID) && is_numeric($slideTemplateID)){

					//init by local template, if fail, init by global (slider) template

				try{

					$slideTemplateLocal = new RevSlide();

					$slideTemplateLocal->initByID($slideTemplateID);

					$this->initBySlide($slideTemplateLocal);

				}

				catch(Exception $e){

					$this->initBySlide($slideTemplate);

				}

			}else{

				$this->initBySlide($slideTemplate);

			}

			$this->id = $postID;

			$this->params["title"] = UniteFunctionsRev::getVal($postData, "post_title");

			// if($this->params['enable_link'] == "true" && $this->params['link_type'] == "regular"){

			// $link = get_permalink($postID);

			// $this->params["link"] = str_replace("%link%", $link, $this->params["link"]);

			// $this->params["link"] = str_replace('-', '_REVSLIDER_', $this->params["link"]);

			// $arrMatches = array();

			// preg_match('/%product:\w+%/', $this->params["link"], $arrMatches);

			// foreach($arrMatches as $match){

			// 	$meta = str_replace("%product:", "", $match);

			// 	$meta = str_replace("%","",$meta);

			// 	$meta = str_replace('_REVSLIDER_', '-', $meta);

			// 	if(isset($postData[$meta]) && !empty($postData[$meta])){
			// 		$metaValue = $postData[$meta];
			// 		$this->params["link"] = str_replace($match,$metaValue,$this->params["link"]);
			// 	}
			// }

			// $this->params["link"] = str_replace('_REVSLIDER_','-',$this->params["link"]);

			// }

			$status = $postData["active"];

			if($status == 1)

				$this->params["state"] = "published";

			else

				$this->params["state"] = "unpublished";


			//set image

			// $thumbID = UniteFunctionsWPRev::getPostThumbID($postID);

			$RevSlider = new RevSlider();
			$GetSliderImgSettings = $RevSlider->GetSliderImgSettings($sliderID);
			
			if(!empty($postID))

				$this->setImageByImageID($postID,$GetSliderImgSettings);

			//replace placeholders in layers:
			
			$this->setLayersByPostData($postData, $sliderID);

		}
		private function SetImageSrc($postData = array())
		{
			$link = new Link();
			$lnk = $link->getImageLink($postData['link_rewrite'], $postData['id_image']); //Give here extra argument in Image type
			if(isset($lnk) && !empty($lnk))
				return 'http://'.htmlspecialchars_decode($lnk);
			else
				return false;
		}
		private function SetCountDown($postData = array()){
			$html = '';
			if(isset($postData) && isset($postData['specific_prices']) && !empty($postData['specific_prices'])){
				$id_product = $postData['id_product'];
				$specific_prices = $postData['specific_prices'];
				$to_time = $specific_prices['to'];
				$to_time_str = strtotime($to_time);
				$to_time_y = date("Y",$to_time_str);
				$to_time_m = date("m",$to_time_str);
				$to_time_d = date("d",$to_time_str);
				$to_time_h = date("H",$to_time_str);
				$to_time_i = date("i",$to_time_str);
				$to_time_s = date("s",$to_time_str);
				$from_time = $specific_prices['from'];
				$now_time = date("Y-m-d H:i:s");
				if($now_time <= $to_time && $now_time >= $from_time){
					$html .= '<div class="product_count_down">
						<span class="turning_clock"></span>
						<div class="count_holder_small">
						<div class="count_info">
						</div>
						<div id="sds_rev_countdown_'.$id_product.'" class="count_content clearfix">
						</div>
						<div class="clear"></div>
						</div>
						</div>';
					$html .= "<script type='text/javascript'>
						$(function() {
                                      $('#sds_rev_countdown_".$id_product."').countdown({
                                          until: new Date(".$to_time_y.",".$to_time_m." - 1,".$to_time_d.",".$to_time_h.",".$to_time_i.",".$to_time_s."), compact: false});
                                  });
		</script>";
//start add countdown CSS & JS Files
$countdown_js = __PS_BASE_URI__.'modules/revsliderprestashop/js/countdown/jquery.countdown.js';
$countdown_css = __PS_BASE_URI__.'modules/revsliderprestashop/css/countdown/countdown.css';
Context::getcontext()->controller->addJs($countdown_js);
Context::getcontext()->controller->addCSS($countdown_css);
// end start countdown JS
				}
			}
			return $html;
		}

		private function setLayersByPostData($postData,$sliderID){

			$priceDisplay = Product::getTaxCalculationMethod((int)Context::getcontext()->cookie->id_customer);
			if(!$priceDisplay){
				$productprice = Tools::displayPrice($postData["price"],Context::getContext()->currency);
			}else{
				$productprice = Tools::displayPrice($postData["price_tax_exc"],Context::getContext()->currency);
			}

			$postID = $postData["id_product"];



			$countdown = $this->SetCountDown($postData);

			// $imgsrc = $this->SetImageSrc($postData);

			$title = UniteFunctionsRev::getVal($postData, "name");

			$excerpt_limit = $this->getSliderParam($sliderID,"excerpt_limit",55,RevSlider::VALIDATE_NUMERIC);

			$excerpt_limit = (int)$excerpt_limit;

			$description = substr($postData["description"], $excerpt_limit);

			$description_short = $postData["description_short"];

			// $alias = UniteFunctionsRev::getVal($postData, "post_name");
			// $content = UniteFunctionsRev::getVal($postData, "post_content");
			//$link = get_permalink($postID);
			$link = $postData["link"];

			$date_add = $postData["date_add"];

			//$date_add = UniteFunctionsWPRev::convertPostDate($date_add);

			$date_upd = $postData["date_upd"];

			//$date_upd = UniteFunctionsWPRev::convertPostDate($date_upd);

			$category_default = $postData["category_default"];

			$linkobj = new Link();

			$addtocart = $linkobj->getPageLink('cart',false, NULL, "add=1&amp;id_product=".$postID, false);

			foreach($this->arrLayers as $key=>$layer){
			$text = UniteFunctionsRev::getVal($layer, "text");
			$text = str_replace("%title%", $title, $text);
			$text = str_replace("%description_short%",$description_short, $text);
			$text = str_replace("%description%", $description, $text);
			$text = str_replace("%link%", $link, $text);
			$text = str_replace("%addtocart%", $addtocart, $text);
			$text = str_replace("%countdown%", $countdown, $text);
			// $text = str_replace("%imgsrc%", $imgsrc, $text);
			$text = str_replace("%date%", $date_add , $text);
			$text = str_replace("%date_modified%", $date_upd , $text);
			$text = str_replace("%product_price%", $productprice , $text);
			$text = str_replace("%category_default%", $category_default , $text);

				$arrMatches = array();
				$text = str_replace('-', '_REVSLIDER_', $text);
				
				preg_match_all('/%product:\w+%/', $text, $arrMatches);

				foreach($arrMatches as $matched){
					
					foreach($matched as $match) {
					
						$meta = str_replace("%product:", "", $match);
						$meta = str_replace("%","",$meta);
						$meta = str_replace('_REVSLIDER_', '-', $meta);
						if(isset($postData[$meta]) && !empty($postData[$meta])){
							$metaValue = $postData[$meta];
							$text = str_replace($match,$metaValue,$text);
						}	
					}
				}
				$text = str_replace('_REVSLIDER_','-',$text);

// start hook exec
$extra_hook_meta_exec = array();
Hook::exec('actionsdsrevinsertmetaexec',array(
'extra_hook_meta_exec' => &$extra_hook_meta_exec,
'id_product' => &$postID,
));
if(isset($extra_hook_meta_exec) && !empty($extra_hook_meta_exec)){
	foreach ($extra_hook_meta_exec as $svalue){
		$hook_title = "%".$svalue['title']."%";
		$hook_exec = $svalue['exec'];
		$text = str_replace($hook_title, $hook_exec , $text);
	}
}
// end hook exec
				$layer["text"] = $text;
				$this->arrLayers[$key] = $layer;
			}
		}

		
		public function initByID($slideid){
			if(strpos($slideid, 'static_') !== false){
				$this->static_slide = true;
				$sliderID = str_replace('static_', '', $slideid);
				
				UniteFunctionsRev::validateNumeric($sliderID,"Slider ID");
				
				$sliderID = $this->db->escape($sliderID);
				$record = $this->db->fetch(GlobalsRevSlider::$table_static_slides,"slider_id=$sliderID");
				
				if(empty($record)){
					//create a new static slide for the Slider and then use it
					$slide_id = $this->createSlide($sliderID,"",true);
					
					$record = $this->db->fetch(GlobalsRevSlider::$table_static_slides,"slider_id=$sliderID");
					
					$this->initByData($record[0]);
				}else{
					$this->initByData($record[0]);
				}
			}else{
				UniteFunctionsRev::validateNumeric($slideid,"Slide ID");
				$slideid = $this->db->escape($slideid);
				$record = $this->db->fetchSingle(GlobalsRevSlider::$table_slides,"id=$slideid");
				
				$this->initByData($record);
			}
		}

		 

		

		public function initByStaticID($slideid){
		
			UniteFunctionsRev::validateNumeric($slideid,"Slide ID");
			$slideid = $this->db->escape($slideid);
			$record = $this->db->fetchSingle(GlobalsRevSlider::$table_static_slides,"id=$slideid");
			
			$this->initByData($record);
		}
		
		public function getStaticSlideID($sliderID){
			
			UniteFunctionsRev::validateNumeric($sliderID,"Slider ID");
			
			$sliderID = $this->db->escape($sliderID);
			$record = $this->db->fetch(GlobalsRevSlider::$table_static_slides,"slider_id=$sliderID");
			if(empty($record)){
				return false;
			}else{
				return $record[0]['id'];
			}
		}
		private function setImageByImageID($postID,$img_type = 'large_default'){

			$prdid_image = Product::getCover($postID);

            if (sizeof($prdid_image) > 0)
            {
            $prdimage = new Image($prdid_image['id_image']);
            $prdimage_url = _PS_BASE_URL_._THEME_PROD_DIR_.$prdimage->getExistingImgPath()."-".$img_type.".jpg";
            }

			//$this->imageID = $imageID;
			$this->imageID = 0;

			//$this->imageUrl = UniteFunctionsWPRev::getUrlAttachmentImage($imageID);
			$this->imageUrl = $prdimage_url;

			// $this->imageThumb = UniteFunctionsWPRev::getUrlAttachmentImage($imageID,UniteFunctionsWPRev::THUMB_MEDIUM);
			$this->imageThumb = $prdimage_url;

			if(empty($this->imageUrl))

				return(false);

			$this->params["background_type"] = "image";

			if(is_ssl()){

			$this->imageUrl = str_replace("http://", "https://", $this->imageUrl);

			}			

			// $this->imageFilepath = UniteFunctionsWPRev::getImagePathFromURL($this->imageUrl);
			$this->imageFilepath = $prdimage_url;

		    //$realPath = UniteFunctionsWPRev::getPathContent().$this->imageFilepath;
		    $realPath = $prdimage_url;

		    if(file_exists($realPath) == false || is_file($realPath) == false)

		    	$this->imageFilepath = "";

			$this->imageFilename = basename($this->imageUrl);

		}


		public function setArrChildren($arrChildren){

			$this->arrChildren = $arrChildren;

		}

		

		

		public function getArrChildren(){
			$this->validateInited();  
			if($this->arrChildren === null){
				$slider = new RevSlider();
				$slider->initByID($this->sliderID);       
				$this->arrChildren = $slider->getArrSlideChildren($this->id);
			}
			return($this->arrChildren);				
		}

		public function isFromPost(){

			return !empty($this->postData);

		}
		public function getPostData(){
			return($this->postData);
		}

		public function getArrChildrenPure(){

			return($this->arrChildren);

		}

		public function isParent(){

			$parentID = $this->getParam("parentid","");

			return(!empty($parentID));

		}
		
		public function getLang(){

			$lang = $this->getParam("lang","all");

			return($lang);

		}

		public function getParentSlide(){

			$parentID = $this->getParam("parentid","");

			if(empty($parentID))

				return($this);

			$parentSlide = new RevSlide();

			$parentSlide->initByID($parentID);

			return($parentSlide);

		}

		

		

		public function getArrChildrenIDs(){

			$arrChildren = $this->getArrChildren();

			$arrChildrenIDs = array();

			foreach($arrChildren as $child){

				$childID = $child->getID();

				$arrChildrenIDs[] = $childID;

			}

			

			return($arrChildrenIDs);

		}

		

	

		public function getArrChildrenLangs($includeParent = true){			

			$this->validateInited();

			$slideID = $this->id;

			if($includeParent == true){

				$lang = $this->getParam("lang","all");

				$arrOutput = array();

				$arrOutput[] = array("slideid"=>$slideID,"lang"=>$lang,"isparent"=>true);

			}

			

			$arrChildren = $this->getArrChildren();

			

			foreach($arrChildren as $child){

				$childID = $child->getID();

				$childLang = $child->getParam("lang","all");

				$arrOutput[] = array("slideid"=>$childID,"lang"=>$childLang,"isparent"=>false);

			}

			

			return($arrOutput);

		}

		

		public function getArrChildLangCodes($includeParent = true){

			$arrLangsWithSlideID = $this->getArrChildrenLangs($includeParent);

			$arrLangCodes = array();

			foreach($arrLangsWithSlideID as $item){

				$lang = $item["lang"];

				$arrLangCodes[$lang] = $lang;

			}

			

			return($arrLangCodes);

		}

		

	

		public function getID(){

			return($this->id);

		}
		public function tem_post_types(){

			return($this->slider->arrParams['post_types']);

		}
		

		


		public function getOrder(){

			$this->validateInited();

			return($this->slideOrder);

		}

	

		public function getLayers(){

			$this->validateInited();

			return($this->arrLayers);

		}

		


		public function getLayersForExport($useDummy = false){

			$this->validateInited();

			$arrLayersNew = array();

			foreach($this->arrLayers as $key=>$layer){

				$imageUrl = UniteFunctionsRev::getVal($layer, "image_url");

				if(!empty($imageUrl))

					$layer["image_url"] = UniteFunctionsWPRev::getImagePathFromURL($layer["image_url"]);

				

				$arrLayersNew[] = $layer;

			}

			

			return($arrLayersNew);

		}

	

		public function getParamsForExport(){

			$arrParams = $this->getParams();

			$urlImage = UniteFunctionsRev::getVal($arrParams, "image");

			if(!empty($urlImage))

				$arrParams["image"] = UniteFunctionsWPRev::getImagePathFromURL($urlImage);

			

			return($arrParams);

		}


		public function getLayersNormalizeText(){

			$arrLayersNew = array();

			foreach ($this->arrLayers as $key=>$layer){

				$text = $layer["text"];

				$text = addslashes($text);

				$layer["text"] = $text;

				$arrLayersNew[] = $layer;

			}

			

			return($arrLayersNew);

		}

		




		public function getParams(){

			$this->validateInited();

			return($this->params);

		}




		function getParam($name,$default=null){

			

			if($default === null){

				if(!array_key_exists($name, $this->params))

					UniteFunctionsRev::throwError("The param <b>$name</b> not found in slide params.");

				$default = "";

			}

				

			return UniteFunctionsRev::getVal($this->params, $name,$default);

		}

		

		

		
		public function getImageFilename(){

			return($this->imageFilename);

		}

		

		

		

		public function getImageFilepath(){

			return($this->imageFilepath);

		}

		


		public function getImageUrl(){

			

			return($this->imageUrl);

		}

		public function getImageID(){                        

			return($this->imageID);

		}


		public function getThumbUrl(){

			$thumbUrl = $this->imageUrl;

                        

                        $size = GlobalsRevSlider::IMAGE_SIZE_MEDIUM;

                        $filename = basename($thumbUrl);

                        

                        $filerealname = substr($filename,0,strrpos($filename,'.'));

                        $fileext = substr($filename,strrpos($filename,'.'),strlen($filename)-strlen($filerealname));

                        

                        $nthumbUrl = str_replace($filename,"{$filerealname}-{$size}x{$size}{$fileext}",$thumbUrl);

                        

			if(!empty($this->imageThumb))

				$nthumbUrl = $thumbUrl = $this->imageThumb;

			

                        

                        

                        

                        //$nthumbUrl = str_replace($filename,"{$filerealname}-{$size}x{$size}{$fileext}",$thumbUrl);

			return($nthumbUrl);

		}

		
		public function getSliderID(){

			return($this->sliderID);

		}

	

		private function getSliderParam($sliderID,$name,$default,$validate=null){

			

			if(empty($this->slider)){

				$this->slider = new RevSlider();

				$this->slider->initByID($sliderID);

			}

			

			$param = $this->slider->getParam($name,$default,$validate);

			

			return($param);

		}

		

		
		private function validateSliderExists($sliderID){

			$slider = new RevSlider();

			$slider->initByID($sliderID);

		}

		


		private function validateInited(){

			if(empty($this->id))

				UniteFunctionsRev::throwError("The slide is not inited!!!");

		}

		public function createSlide($sliderID,$obj="",$static = false){
			
			$imageID = null;
			
			if(is_array($obj)){
				$urlImage = UniteFunctionsRev::getVal($obj, "url");
				$imageID = UniteFunctionsRev::getVal($obj, "id");
			}else{
				$urlImage = $obj;
			}
			
			//get max order
			$slider = new RevSlider();
			$slider->initByID($sliderID);
			$maxOrder = $slider->getMaxOrder();
			$order = $maxOrder+1;
			
			$params = array();
			if(!empty($urlImage)){
				$params["background_type"] = "image";
				$params["image"] = $urlImage;
				if(!empty($imageID))
					$params["image_id"] = $imageID;
					
			}else{	//create transparent slide
				
				$params["background_type"] = "trans";
			}
				
			$jsonParams = json_encode($params);
			
			
			$arrInsert = array("params"=>$jsonParams,
			           		   "slider_id"=>$sliderID,
								"layers"=>""
						);
						
			if(!$static)
				$arrInsert["slide_order"] = $order;
			
			if(!$static)
				$slideID = $this->db->insert(GlobalsRevSlider::$table_slides, $arrInsert);
			else
				$slideID = $this->db->insert(GlobalsRevSlider::$table_static_slides, $arrInsert);
			
			return($slideID);
		}

		public function updateSlideImageFromData($data){

			

			$sliderID = UniteFunctionsRev::getVal($data, "slider_id");

			$slider = new RevSlider();

			$slider->initByID($sliderID);

			

			$slideID = UniteFunctionsRev::getVal($data, "slide_id");

			$urlImage = UniteFunctionsRev::getVal($data, "url_image");

			UniteFunctionsRev::validateNotEmpty($urlImage);

			$imageID = UniteFunctionsRev::getVal($data, "image_id");

			if($slider->isSlidesFromPosts()){

				

				if(!empty($imageID))

					UniteFunctionsWPRev::updatePostThumbnail($slideID, $imageID);

				

			}else{

				$this->initByID($slideID);

								

				$arrUpdate = array();

				$arrUpdate["image"] = $urlImage;			

				$arrUpdate["image_id"] = $imageID;

				

				$this->updateParamsInDB($arrUpdate);

			}

			

			return($urlImage);

		}

		

		

		


		private function updateParamsInDB($arrUpdate = array()){

			$this->validateInited();

			$this->params = array_merge($this->params,$arrUpdate);

			$jsonParams = json_encode($this->params);

			

			$arrDBUpdate = array("params"=>$jsonParams);

			

			$this->db->update(GlobalsRevSlider::$table_slides,$arrDBUpdate,array("id"=>$this->id));

		}

	

		private function updateLayersInDB($arrLayers = null){

			$this->validateInited();

			

			if($arrLayers === null)

				$arrLayers = $this->arrLayers;

				

			$jsonLayers = json_encode($arrLayers);

			$arrDBUpdate = array("layers"=>$jsonLayers);

			

			$this->db->update(GlobalsRevSlider::$table_slides,$arrDBUpdate,array("id"=>$this->id));

		} 

		


		public function updateParentSlideID($parentID){

			$arrUpdate = array();

			$arrUpdate["parentid"] = $parentID;

			$this->updateParamsInDB($arrUpdate);

		}

		

	
		private function sortLayersByOrder($layer1,$layer2){

			$layer1 = (array)$layer1;

			$layer2 = (array)$layer2;

			

			$order1 = UniteFunctionsRev::getVal($layer1, "order",1);

			$order2 = UniteFunctionsRev::getVal($layer2, "order",2);

			if($order1 == $order2)

				return(0);

			

			return($order1 > $order2);

		}

	
		private function normalizeLayers($arrLayers){

			

			usort($arrLayers,array($this,"sortLayersByOrder"));

			

			$arrLayersNew = array();

			foreach ($arrLayers as $key=>$layer){

				

				$layer = (array)$layer;

				

				//set type

				$type = UniteFunctionsRev::getVal($layer, "type","text");

				$layer["type"] = $type;

				

				//normalize position:

				$layer["left"] = round($layer["left"]);

				$layer["top"] = round($layer["top"]);

				

				//unset order

				unset($layer["order"]);

				

				//modify text

				$layer["text"] = stripcslashes($layer["text"]);

				

				$arrLayersNew[] = $layer;

			}

			

			return($arrLayersNew);

		}  

		private function normalizeParams($params){

			

			$urlImage = UniteFunctionsRev::getVal($params, "image_url");


			$params["image_id"] = UniteFunctionsRev::getVal($params, "image_id");

			

			$params["image"] = $urlImage;

			unset($params["image_url"]);

			

			if(isset($params["video_description"]))

				$params["video_description"] = UniteFunctionsRev::normalizeTextareaContent($params["video_description"]);

			

			return($params);

		}

		

		public function updateSlideFromData($data, $slideSettings){

			

			$slideID = UniteFunctionsRev::getVal($data, "slideid");

			$this->initByID($slideID);						

			

			//treat params

			$params = UniteFunctionsRev::getVal($data, "params");

			$params = $this->normalizeParams($params);

			

			//modify the values according the settings

			$params = $slideSettings->setStoredValues($params);

			

			//preserve old data that not included in the given data

			$params = array_merge($this->params,$params);

			

			//treat layers

			$layers = UniteFunctionsRev::getVal($data, "layers");

			

			if(gettype($layers) == "string"){

				$layersStrip = stripslashes($layers);

				$layersDecoded = json_decode($layersStrip);

				if(empty($layersDecoded))

					$layersDecoded = json_decode($layers);

				

				$layers = UniteFunctionsRev::convertStdClassToArray($layersDecoded);

			}

			

			if(empty($layers) || gettype($layers) != "array")

				$layers = array();

			

			$layers = $this->normalizeLayers($layers);

			

			$arrUpdate = array();

			$arrUpdate["layers"] = json_encode($layers);

			$arrUpdate["params"] = json_encode($params);

			$this->db->update(GlobalsRevSlider::$table_slides,$arrUpdate,array("id"=>$this->id));

			

			RevOperations::updateDynamicCaptions();

		}

		

		public function updateStaticSlideFromData($data){
			
			$slideID = UniteFunctionsRev::getVal($data, "slideid");
			$this->initByStaticID($slideID);
			
			//treat layers
			$layers = UniteFunctionsRev::getVal($data, "layers");
			
			if(gettype($layers) == "string"){
				$layersStrip = stripslashes($layers);
				$layersDecoded = json_decode($layersStrip);
				if(empty($layersDecoded))
					$layersDecoded = json_decode($layers);
				
				$layers = UniteFunctionsRev::convertStdClassToArray($layersDecoded);
			}
			
			if(empty($layers) || gettype($layers) != "array")
				$layers = array();
			
			$layers = $this->normalizeLayers($layers);
			
			$arrUpdate = array();
			$arrUpdate["layers"] = json_encode($layers);
			
			$this->db->update(GlobalsRevSlider::$table_static_slides,$arrUpdate,array("id"=>$this->id));
			
			
		}


		public function deleteSlide(){

			$this->validateInited();

			

			$this->db->delete(GlobalsRevSlider::$table_slides,"id='".$this->id."'");

		}

		public function deleteChildren(){

			$this->validateInited();

			$arrChildren = $this->getArrChildren();

			foreach($arrChildren as $child)

				$child->deleteSlide();

		}

		public function deleteSlideFromData($data){

			

			$sliderID = UniteFunctionsRev::getVal($data, "sliderID");

			$slider = new RevSlider();

			$slider->initByID($sliderID); 			



			$isPost = $slider->isSlidesFromPosts();

			

			if($isPost == true){	//delete post	

				

				$postID = UniteFunctionsRev::getVal($data, "slideID");

				UniteFunctionsWPRev::deletePost($postID);

				

			}else{		//delete slide

				

				$slideID = UniteFunctionsRev::getVal($data, "slideID");

				$this->initByID($slideID);

				$this->deleteChildren();

				$this->deleteSlide();

								

			}

			

			RevOperations::updateDynamicCaptions();

			

		}

		public function setParams($params){

			$params = $this->normalizeParams($params);

			$this->params = $params;

		}

		public function setLayers($layers){

			$layers = $this->normalizeLayers($layers);

			$this->arrLayers = $layers;

		}

		

		


		public function toggleSlideStatFromData($data){

			

			$sliderID = UniteFunctionsRev::getVal($data, "slider_id");

			$slider = new RevSlider();

			$slider->initByID($sliderID);

			

			$slideID = UniteFunctionsRev::getVal($data, "slide_id");

						

			if($slider->isSlidesFromPosts()){

				$postData = UniteFunctionsWPRev::getPost($slideID);

				

				$oldState = $postData["post_status"];

				$newState = ($oldState == UniteFunctionsWPRev::STATE_PUBLISHED)?UniteFunctionsWPRev::STATE_DRAFT:UniteFunctionsWPRev::STATE_PUBLISHED;

				

				//update the state in wp

				UniteFunctionsWPRev::updatePostState($slideID, $newState);

				

				//return state:

				$newState = ($newState == UniteFunctionsWPRev::STATE_PUBLISHED)?"published":"unpublished";

				

			}else{

				$this->initByID($slideID);

				

				$state = $this->getParam("state","published");

				$newState = ($state == "published")?"unpublished":"published";

				

				$arrUpdate = array();

				$arrUpdate["state"] = $newState;

				

				$this->updateParamsInDB($arrUpdate);

				

			}

						

			return($newState);

		}
		private function updateLangFromData($data){

						

			$slideID = UniteFunctionsRev::getVal($data, "slideid");

			$this->initByID($slideID);

			

			$lang = UniteFunctionsRev::getVal($data, "lang");

			

			$arrUpdate = array();

			$arrUpdate["lang"] = $lang;

			$this->updateParamsInDB($arrUpdate);

			

			$response = array();

			$response["url_icon"] = UniteWpmlRev::getFlagUrl($lang);

			$response["title"] = UniteWpmlRev::getLangTitle($lang);

			$response["operation"] = "update";

			

			return($response);

		}

		private function addLangFromData($data){

			$sliderID = UniteFunctionsRev::getVal($data, "sliderid");

			$slideID = UniteFunctionsRev::getVal($data, "slideid");

			$lang = UniteFunctionsRev::getVal($data, "lang");

			

			//duplicate slide

			$slider = new RevSlider();

			$slider->initByID($sliderID);

			$newSlideID = $slider->duplicateSlide($slideID);

					

			//update new slide

			$this->initByID($newSlideID);

			

			$arrUpdate = array();

			$arrUpdate["lang"] = $lang;

			$arrUpdate["parentid"] = $slideID;

			$this->updateParamsInDB($arrUpdate);

						

			$urlIcon = UniteWpmlRev::getFlagUrl($lang);

			$title = UniteWpmlRev::getLangTitle($lang);

			

			$newSlide = new RevSlide();

			$newSlide->initByID($slideID);

			$arrLangCodes = $newSlide->getArrChildLangCodes();

			$isAll = UniteWpmlRev::isAllLangsInArray($arrLangCodes);

			

			$html = "<li>

								<img id=\"icon_lang_".$newSlideID."\" class=\"icon_slide_lang\" src=\"".$urlIcon."\" title=\"".$title."\" data-slideid=\"".$newSlideID."\" data-lang=\"".$lang."\">

								<div class=\"icon_lang_loader loader_round\" style=\"display:none\"></div>								

							</li>";

			

			$response = array();

			$response["operation"] = "add";

			$response["isAll"] = $isAll;

			$response["html"] = $html;

			

			return($response);

		}

		private function deleteSlideFromLangData($data){

			

			$slideID = UniteFunctionsRev::getVal($data, "slideid");

			$this->initByID($slideID);

			$this->deleteSlide();

			

			$response = array();

			$response["operation"] = "delete";

			return($response);

		}
		public function doSlideLangOperation($data){

			

			$operation = UniteFunctionsRev::getVal($data, "operation");

			switch($operation){

				case "add":

					$response = $this->addLangFromData($data);	

				break;

				case "delete":

					$response = $this->deleteSlideFromLangData($data);

				break;

				case "update":

				default:

					$response = $this->updateLangFromData($data);

				break;

			}

			

			return($response);

		}

		


		public function getUrlImageThumb(){

			

			//get image url by thumb

			if(!empty($this->imageID)){

				$urlImage = UniteFunctionsWPRev::getUrlAttachmentImage($this->imageID, UniteFunctionsWPRev::THUMB_MEDIUM);

			}else{

				//get from cache

				if(!empty($this->imageFilepath)){

					$urlImage = UniteBaseClassRev::getImageUrl($this->imageFilepath,200,100,true);

				}

				else 

					$urlImage = $this->imageUrl;

			}

			

			if(empty($urlImage))

				$urlImage = $this->imageUrl;

			

			return($urlImage);

		}

		public function replaceImageUrls($urlFrom, $urlTo){

			

			$this->validateInited();

						

			$urlImage = UniteFunctionsRev::getVal($this->params, "image");

			

			if(strpos($urlImage, $urlFrom) !== false){

				$imageNew = str_replace($urlFrom, $urlTo, $urlImage);

				$this->params["image"] = $imageNew; 

				$this->updateParamsInDB();

			}

			

			

			// update image url in layers

			$isUpdated = false;

			foreach($this->arrLayers as $key=>$layer){

				$type =  UniteFunctionsRev::getVal($layer, "type");

				if($type == "image"){

					$urlImage = UniteFunctionsRev::getVal($layer, "image_url");

					if(strpos($urlImage, $urlFrom) !== false){

						$newUrlImage = str_replace($urlFrom, $urlTo, $urlImage);

						$this->arrLayers[$key]["image_url"] = $newUrlImage;

						$isUpdated = true;

					}

				}

			}

			

			if($isUpdated == true)

				$this->updateLayersInDB();

			

		}
		public function changeTransition($transition){

			$this->validateInited();

			

			$this->params["slide_transition"] = $transition;

			$this->updateParamsInDB();

		}
		public function changeTransitionDuration($transitionDuration){

			$this->validateInited();

			

			$this->params["transition_duration"] = $transitionDuration;

			$this->updateParamsInDB();

		}

		public function isStaticSlide(){
			return $this->static_slide;
		}

	}

	

?>