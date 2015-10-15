<?php



	class UniteFunctionsWPRev{



		public static $urlSite;

		public static $urlAdmin;

		public static $prestaprds;

		const SORTBY_NONE = "none";

		const SORTBY_ID = "ID";

		const SORTBY_PRICE = "price";

		const SORTBY_SALES = "sales";

		const SORTBY_AUTHOR = "author";

		const SORTBY_TITLE = "title";

		const SORTBY_SLUG = "name";

		const SORTBY_DATE = "date";

		const SORTBY_LAST_MODIFIED = "modified";

		const SORTBY_RAND = "rand";

		const SORTBY_COMMENT_COUNT = "comment_count";

		const SORTBY_MENU_ORDER = "menu_order";

		

		const ORDER_DIRECTION_ASC = "ASC";

		const ORDER_DIRECTION_DESC = "DESC";

		

		const THUMB_SMALL = "thumbnail";

		const THUMB_MEDIUM = "medium";

		const THUMB_LARGE = "large";

		const THUMB_FULL = "full";

		

		const STATE_PUBLISHED = "publish";

		const STATE_DRAFT = "draft";

		

		


		public static function initStaticVars(){


			self::$urlSite = __PS_BASE_URI__;

			

			if(substr(self::$urlSite, -1) != "/")

				self::$urlSite .= "/";

			

			self::$urlAdmin = admin_url();			


				

			

		}

		

		


		public static function getArrSortBy(){
			$arr = array();
			$arr[self::SORTBY_ID] = "ID"; 

			$arr[self::SORTBY_DATE] = "Date";

			$arr[self::SORTBY_TITLE] = "Title"; 

			//$arr[self::SORTBY_SLUG] = "Slug"; 

			//$arr[self::SORTBY_AUTHOR] = "Author";

			//$arr[self::SORTBY_LAST_MODIFIED] = "Last Modified"; 

			//$arr[self::SORTBY_COMMENT_COUNT] = "Number Of Comments";

			$arr[self::SORTBY_PRICE] = "Price";

			//$arr[self::SORTBY_SALES] = "Sales";

			$arr[self::SORTBY_RAND] = "Random";
			

			//$arr[self::SORTBY_NONE] = "Unsorted";

			//$arr[self::SORTBY_MENU_ORDER] = "Custom Order";

			return($arr);

		}

		public static function GetArrImageSize(){
			$arr = array();

            $img_type = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                            SELECT * FROM `'._DB_PREFIX_.'image_type` where `products` = 1');
            
            foreach($img_type as $type){
            	$arr[$type['name']] = $type['name'];
            }
            // print '<pre>';
            // print_r( $arr);
            // print '</pre>';
			return($arr);

		}
		


		public static function getArrSortDirection(){

			$arr = array();

			$arr[self::ORDER_DIRECTION_DESC] = "Descending";

			$arr[self::ORDER_DIRECTION_ASC] = "Ascending";

			return($arr);

		}

		

		

		public static function getBlogID(){

			global $blog_id;

			return($blog_id);

		}

		

		



		public static function isMultisite(){

			//$isMultisite = is_multisite();

			return false;

		}

		

		



		public static function isDBTableExists($tableName){

			

                        if(class_exists('RevsliderPrestashop'))                        

                            $wpdb = RevsliderPrestashop::$wpdb;

                        else{

                            $wpdb = rev_db_class::rev_db_instance();                            

                        }

                    

			

			if(empty($tableName))

				UniteFunctionsRev::throwError("Empty table name!!!");

			

                        $tableName = $wpdb->prefix.$tableName;

                        

			$sql = "show tables like '$tableName'";

			

			$table = $wpdb->get_var($sql);

			

			if($table == $tableName)

				return(true);

				

			return(false);

		}

		



		public static function getPathBase(){

			return ABSPATH;

		}

	

		public static function getPathUploads(){		

			if(self::isMultisite()){

				if(!defined("BLOGUPLOADDIR")){

					$pathBase = self::getPathBase();

					$pathContent = $pathBase."wp-content/uploads/";

				}else

				  $pathContent = BLOGUPLOADDIR;

			}else{

				$pathContent = WP_CONTENT_DIR;

				if(!empty($pathContent)){

					$pathContent .= "/";

				}

				else{

					$pathBase = self::getPathBase();

					$pathContent = $pathBase."wp-content/uploads/";

				}

			}

			

			return($pathContent);

		}

		


		public static function getUrlUploads(){		

			$baseUrl = content_url()."/";			

			//$baseUrl = uploads_url();			

			return($baseUrl);			
			

		}



                public static function import_media_img($file_url, $folder, $filename){

                	// $folder = rev_media_folder();

                    $wpdb = rev_db_class::rev_db_instance();

                    

                    copy($file_url, $folder.$filename);

                    $imagearray = array('file_name'=>$filename);                                    

                    $mysqli = $wpdb->insert($wpdb->prefix.GlobalsRevSlider::TABLE_ATTACHMENT_IMAGES,$imagearray);

                    $imgid = $wpdb->Insert_ID();
                    
                    if(!empty($mysqli) && is_numeric($imgid)){ //new fixing 
                    
               

                        $sizes = array(

                             GlobalsRevSlider::IMAGE_SIZE_THUMBNAIL,

                             GlobalsRevSlider::IMAGE_SIZE_MEDIUM,

                             GlobalsRevSlider::IMAGE_SIZE_LARGE

                        );



                        $filerealname = substr($filename,0,strrpos($filename,'.'));

                        $fileext = substr($filename,strrpos($filename,'.'),strlen($filename)-strlen($filerealname));

                        list($width,$height) = getimagesize($folder.$filename);

                    

                        $count = 0;

                        foreach($sizes as $size){

                            

                            $nsize = self::get_img_aspect_ratio(array($width,$height,$size));

                            $newfilename = "{$filerealname}-{$size}x{$size}{$fileext}";

                            if(++$count > 1)

                                ImageManager::resize($folder.$filename, $folder.$newfilename, $nsize[0], $nsize[1]);

                            else{

                                $posx = ($width / 2) - ($size / 2); 

                                $posy = ($height / 2) - ($size / 2); 

                                ImageManager::cut($folder.$filename, $folder.$newfilename, $size, $size, substr($fileext,1), $posx, $posy);

                            }

                        }

                        return array("id" => $imgid, "path" => 'uploads/'.$filename);                        
                        // return array("id" => $imgid, "path" => $filename);                        
                    }

                }

                

                public static function import_media($file_url){

  
                    $folder = ABSPATH.'/uploads/';

                    $file_urls = explode('#',$file_url);                    

                    $randnum = rand(0000000,9999999);

                    $filename = basename($file_urls[1]);                    

                    $filename = "{$randnum}_{$filename}";

                    

                    if($fp = fopen($file_url, "r")){

                        fclose($fp);                        

                        return self::import_media_img($file_url,$folder,$filename);                                           

                    }                    

                    return false;

                    

                }

                

                protected static function get_img_aspect_ratio($params){

                    $newdim = array();

                    

                    if($params[2] == $params[0] or $params[2] == $params[1])

                        return $params;

                    

                    elseif($params[0] > $params[1]){

                        

                        $r = $params[2] / $params[1];

                        $nw = round($params[0] * $r);

                        $nh = round($params[1] * $r);

                        if($nw >= $params[0] or $nh >= $params[1]){

                            $newdim[] = $params[0];

                            $newdim[] = $params[1];

                        }

                        else{

                            $newdim[] = $nw;

                            $newdim[] = $nh;

                        }

                        

                    }

                    elseif($params[0] < $params[1]){

                        

                        $r = $params[2] / $params[0];

                        $nw = round($params[0] * $r);

                        $nh = round($params[1] * $r);

                        if($nw >= $params[0] or $nh >= $params[1]){

                            $newdim[] = $params[0];

                            $newdim[] = $params[1];

                        }

                        else{

                            $newdim[] = $nw;

                            $newdim[] = $nh;

                        }                       

                    }

                    return $newdim;

                    

                }

                

		public static function _import_media($file_url, $folder_name) { //old 



                        require_once(ABSPATH . 'wp-admin/includes/image.php');

			

			$ul_dir = wp_upload_dir();

			$artDir = 'revslider/';

			

			if(!file_exists($ul_dir['basedir'].'/'.$artDir)) mkdir($ul_dir['basedir'].'/'.$artDir);

			if(!file_exists($ul_dir['basedir'].'/'.$artDir.$folder_name)) mkdir($ul_dir['basedir'].'/'.$artDir.$folder_name);

			

			

			$filename = basename($file_url);

			

			if(@fclose(@fopen($file_url, "r"))){ //make sure the file actually exists

				

				$saveDir = $ul_dir['basedir'].'/'.$artDir.$folder_name.$filename;

				

				copy($file_url, $saveDir);

				

				$file_info = getimagesize($saveDir);



				//create an array of attachment data to insert into wp_posts table

				$artdata = array(

					'post_author' => 1, 

					'post_date' => current_time('mysql'),

					'post_date_gmt' => current_time('mysql'),

					'post_title' => $filename, 

					'post_status' => 'inherit',

					'comment_status' => 'closed',

					'ping_status' => 'closed',

					'post_name' => sanitize_title_with_dashes(str_replace("_", "-", $filename)),

					'post_modified' => current_time('mysql'),

					'post_modified_gmt' => current_time('mysql'),

					'post_parent' => '',

					'post_type' => 'attachment',

					'guid' => $ul_dir['baseurl'].'/'.$artDir.$folder_name.$filename,

					'post_mime_type' => $file_info['mime'],

					'post_excerpt' => '',

					'post_content' => ''

				);


				$attach_id = wp_insert_attachment($artdata, $artDir.$folder_name.$filename);




				if($attach_data = wp_generate_attachment_metadata($attach_id, $saveDir)) wp_update_attachment_metadata($attach_id, $attach_data);

				if(!self::isMultisite()) $artDir = 'uploads/'.$artDir;

				return array("id" => $attach_id, "path" => $artDir.$folder_name.$filename);

			}else{

				return false;

			}

		}



		public static function registerWidget($widgetName){

			add_action('widgets_init', create_function('', 'return register_widget("'.$widgetName.'");'));

		}




		public static function getImagePathFromURL($urlImage){

			

			$baseUrl = self::getUrlUploads();

			$pathImage = str_replace($baseUrl, "", $urlImage);

			

			return($pathImage);

		}

		

		public static function getImageRealPathFromUrl($urlImage){

			$filepath = self::getImagePathFromURL($urlImage);

			$realPath = UniteFunctionsWPRev::getPathUploads().$filepath;

			return($realPath);

		}

		

		


		public static function getImageUrlFromPath($pathImage){

			//protect from absolute url

			$pathLower = strtolower($pathImage);

			if(strpos($pathLower, "http://") !== false || strpos($pathLower, "www.") === 0)

				return($pathImage);

			

			$urlImage = self::getUrlUploads().$pathImage;

			return($urlImage); 

		}

		

		

		public static function getCategoriesAssoc($taxonomy = "category"){

			

			if(strpos($taxonomy,",") !== false){

				$arrTax = explode(",", $taxonomy);

				$arrCats = array();

				foreach($arrTax as $tax){

					$cats = self::getCategoriesAssoc($tax);

					$arrCats = array_merge($arrCats,$cats);

				}

				

				return($arrCats);

			}	

			

			

			$args = array("taxonomy"=>$taxonomy);

			$cats = get_categories($args);

			

			$arrCats = array();

			foreach($cats as $cat){

				$numItems = $cat->count;

				$itemsName = "items";

				if($numItems == 1)

					$itemsName = "item";

					

				$title = $cat->name . " ($numItems $itemsName)";

				

				$id = $cat->cat_ID;

				$arrCats[$id] = $title;

			}

			return($arrCats);

		}

		public static function getPostTypeTitle($postType){
			//$objType = get_post_type_object($postType);
			$objType = $postType;
			if(empty($objType))
				return($postType);
			$title = $objType->labels->singular_name;
			if(!empty($title))
				return($title);
			else
				return($postType);
		}

		public static function getPostTypeTaxomonies($postType){
// this comments is only apply for prestashop
			// $arrTaxonomies = get_object_taxonomies(array( 'post_type' => $postType ), 'objects');

			

			$arrNames = array();

			// foreach($arrTaxonomies as $key=>$objTax){			

			// 	$arrNames[$objTax->name] = $objTax->labels->name;

			// }

			

			return($arrNames);

		}

		


		public static function getPostTypeTaxonomiesString($postType){

			$arrTax = self::getPostTypeTaxomonies($postType);

			$strTax = "";

			foreach($arrTax as $name=>$title){

				if(!empty($strTax))

					$strTax .= ",";

				$strTax .= $name;

			}

			

			return($strTax);

		}

		
                
                public static function getCMSPages(){
                    
                    $pages = CMS::listCms($id_lang = null, $id_block = false, $active = true);
                    $npages = array();
                    if(!empty($pages)){
                        foreach($pages as $page){
                            $npages['CMS_'.$page['id_cms']] = $page['meta_title'];
                        }
                    }
                    
                    return $npages;
                }
                
               
                public static function getNestedCategories($root_category = null, $id_lang = false, $active = true, $groups = null,$use_shop_restriction = true, $sql_filter = '', $sql_sort = '', $sql_limit = '')
                {
                        if (isset($root_category) && !Validate::isInt($root_category))
                                die(Tools::displayError());

                        if (!Validate::isBool($active))
                                die(Tools::displayError());

                        if (isset($groups) && Group::isFeatureActive() && !is_array($groups))
                                $groups = (array)$groups;

                        $cache_id = 'Category::getNestedCategories_'.md5((int)$root_category.(int)$id_lang.(int)$active.(int)$active
                                .(isset($groups) && Group::isFeatureActive() ? implode('', $groups) : ''));

                        if (!Cache::isStored($cache_id))
                        {
                                $result = Db::getInstance()->executeS('
                                        SELECT c.*, cl.*
                                        FROM `'._DB_PREFIX_.'category` c
                                        '.($use_shop_restriction ? Shop::addSqlAssociation('category', 'c') : '').'
                                        LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON c.`id_category` = cl.`id_category`'.Shop::addSqlRestrictionOnLang('cl').'
                                        '.(isset($groups) && Group::isFeatureActive() ? 'LEFT JOIN `'._DB_PREFIX_.'category_group` cg ON c.`id_category` = cg.`id_category`' : '').'
                                        '.(isset($root_category) ? 'RIGHT JOIN `'._DB_PREFIX_.'category` c2 ON c2.`id_category` = '.(int)$root_category.' AND c.`nleft` >= c2.`nleft` AND c.`nright` <= c2.`nright`' : '').'
                                        WHERE 1 '.$sql_filter.' '.($id_lang ? 'AND `id_lang` = '.(int)$id_lang : '').'
                                        '.($active ? ' AND c.`active` = 1' : '').'
                                        '.(isset($groups) && Group::isFeatureActive() ? ' AND cg.`id_group` IN ('.implode(',', $groups).')' : '').'
                                        '.(!$id_lang || (isset($groups) && Group::isFeatureActive()) ? ' GROUP BY c.`id_category`' : '').'
                                        '.($sql_sort != '' ? $sql_sort : ' ORDER BY c.`level_depth` ASC').'
                                        '.($sql_sort == '' && $use_shop_restriction ? ', category_shop.`position` ASC' : '').'
                                        '.($sql_limit != '' ? $sql_limit : '')
                                );

                                $categories = array();
                                $buff = array();

                                if (!isset($root_category))
                                        $root_category = 1;

                                foreach ($result as $row)
                                {
                                        $current = &$buff[$row['id_category']];
                                        $current = $row;

                                        if ($row['id_category'] == $root_category)
                                                $categories[$row['id_category']] = &$current;
                                        else
                                                $buff[$row['id_parent']]['children'][$row['id_category']] = &$current;
                                }

                                Cache::store($cache_id, $categories);
                        }

                        return Cache::retrieve($cache_id);
                }
                
                public static function getPrestaProdChildCat($cats){

                        foreach($cats as $cat){
                            if($cat['id_shop'] == Context::getContext()->shop->id)
                                self::$prestaprds['category_'.$cat['id_category']] = $cat['name'];
                            
                            if(isset($cat['children']) && !empty($cat['children'])){
                                self::getPrestaProdChildCat($cat['children']);
                            }
                        }
                    
                }
                public static function getPrestaProdCat(){
                    
                    
                    if(method_exists('Category', 'getNestedCategories')){                    
                        $cats = Category::getNestedCategories(null, (int)Context::getContext()->language->id, true);
                    }else{
                        $cats = self::getNestedCategories(null, (int)Context::getContext()->language->id, true);
                    }
                    
                    self::$prestaprds = array();
                    
                    if(!empty($cats)){
                        foreach($cats as $cat){
                            if($cat['id_shop'] == Context::getContext()->shop->id)
                                self::$prestaprds['category_'.$cat['id_category']] = $cat['name'];

                            if(isset($cat['children']) && !empty($cat['children'])){
                                self::getPrestaProdChildCat($cat['children']);
                            }
                            
                        }
                    }
                    
                    return self::$prestaprds;
                    
                }
		public static function getPrestaProdduct(){
                    $prds= array();
                    $prducts= array();
                    if(method_exists('Product', 'getSimpleProducts')){     
                    $id_lang = (int)Context::getContext()->language->id;
                        $prds = Product::getSimpleProducts($id_lang);
                    }else{
                        $prds = self::getSimpleProducts();
                    }
                    if(isset($prds) && !empty($prds)){
                    	$i = 0;
                    	foreach($prds as $prd){
                    		$prducts[$i]['id_product'] = 'product_'.$prd['id_product'];
                    		$prducts[$i]['name'] = $prd['name'];
                    		$i++;
                    	}
                    	// print '<pre>';
                    	// print_r($prducts);
                    	// print '</pre>';
                    	
                    	return $prducts;
                    }else{
                    	return false;
                    }  
        }


		public static function getPostTypesAssoc($arrPutToTop = array()){

			 // $arrBuiltIn = array(

			 // 	"post"=>"post",

			 // 	"page"=>"page",

			 // );

			 

			 //$arrCustomTypes = get_post_types(array('_builtin' => false));
			 // $arrCustomTypes = array();

			 


			 // $arrPutToTopUpdated = array();

			 // foreach($arrPutToTop as $topItem){

			 // 	if(in_array($topItem, $arrCustomTypes) == true){

			 // 		$arrPutToTopUpdated[$topItem] = $topItem;

			 // 		unset($arrCustomTypes[$topItem]);

			 // 	}

			 // }

			 

			 // $arrPostTypes = array_merge($arrPutToTopUpdated,$arrBuiltIn,$arrCustomTypes);

			 



			 // foreach($arrPostTypes as $key=>$type){

				// $arrPostTypes[$key] = self::getPostTypeTitle($type);			 		

			 // }
			$arrPostTypes['product'] = 'product';


			// if((bool)Module::isEnabled('smartblog'))
   //          {
			// 	$arrPostTypes['smartblog'] = 'smartblog';
			// }
			
			return($arrPostTypes);

		}



		public static function getCategoryData($catID){

			$catData = get_category($catID);

			if(empty($catData))

				return($catData);

				

			$catData = (array)$catData;			

			return($catData);

		}

		

		

	
		public static function getPostsByIDs($strIDs){
			$arrPosts = array();
			$arrPrd = array();
			$id_lang = Context::getContext()->language->id;
			$id_shop = Context::getContext()->shop->id;
			if(is_string($strIDs)){
				$arr = explode(",",$strIDs);
			}
			$i = 0;
			foreach($arr as $ar){
				$product = new Product($ar,true,$id_lang,$id_shop);
				$lnk = new Link();
				$prd_link = $lnk->getProductLink($product);
				foreach ($product as $key => $value) {
					$arrPrd['id_product'] = $ar;
					$arrPrd['link'] = $prd_link;
					$arrPrd[$key] = $value;
					if($key == 'id_category_default'){
						$arrPrd['category_default'] = self::GetCategoryNameById($value);
					}
				}
				$arrPosts[$i] = $arrPrd;
				$i++;
			}
			return($arrPosts);
		}

		



		public static function getPostsByCategory($catID,$sortBy = self::SORTBY_ID,$direction = self::ORDER_DIRECTION_DESC,$numPosts=-1,$postTypes="any",$taxonomies="category",$arrAddition = array()){

			

			if(strpos($postTypes,",") !== false){

				$postTypes = explode(",", $postTypes);

				if(array_search("any", $postTypes) !== false)

					$postTypes = "any";		

			}

			

			if(empty($postTypes))

				$postTypes = "any";

			

			if(strpos($catID,",") !== false)

				$catID = explode(",",$catID);

			else

				$catID = array($catID);

			

			$query = array(

				'order'=>$direction,

				'posts_per_page'=>$numPosts,

				'showposts'=>$numPosts,

				'post_type'=>$postTypes

			);		




			if(strpos($sortBy, "meta_num_") === 0){

				$metaKey = str_replace("meta_num_", "", $sortBy);

				$query["orderby"] = "meta_value_num";

				$query["meta_key"] = $metaKey;

			}else

			if(strpos($sortBy, "meta_") === 0){

				$metaKey = str_replace("meta_", "", $sortBy);

				$query["orderby"] = "meta_value";

				$query["meta_key"] = $metaKey;

			}else

				$query["orderby"] = $sortBy;

				


			$arrTax = array();

			if(!empty($taxonomies)){

				$arrTax = explode(",", $taxonomies);

			}

				

			if(!empty($taxonomies)){

			

				$taxQuery = array();

			


				if(strpos($taxonomies,",") !== false){	

					$taxonomies = explode(",",$taxonomies);

					foreach($taxonomies as $taxomony){

						$taxArray = array(

							'taxonomy' => $taxomony,

							'field' => 'id',

							'terms' => $catID

						);			

						$taxQuery[] = $taxArray;

					}

				}else{		

					$taxArray = array(

						'taxonomy' => $taxonomies,

						'field' => 'id',

						'terms' => $catID

					);			

					$taxQuery[] = $taxArray;				

				}

							

				$taxQuery['relation'] = 'OR';

				

				$query['tax_query'] = $taxQuery;

			} 

			

			

			if(!empty($arrAddition))

				$query = array_merge($query, $arrAddition);

			


			

			$objQuery = new WP_Query($query);

			

			$arrPosts = $objQuery->posts;

			

			

			

			foreach($arrPosts as $key=>$post){

				

				if(method_exists($post, "to_array"))

					$arrPost = $post->to_array();				

				else

					$arrPost = (array)$post;

				

				$arrPostCats = self::getPostCategories($post, $arrTax);

				$arrPost["categories"] = $arrPostCats;

				

				$arrPosts[$key] = $arrPost;

			}

			

			return($arrPosts);

		}

		

		public static function getPostCategories($postID,$arrTax){

			

			if(!is_numeric($postID)){

				$postID = (array)$postID;

				$postID = $postID["ID"];

			}

				

			$arrCats = wp_get_post_terms( $postID, $arrTax);

			$arrCats = UniteFunctionsRev::convertStdClassToArray($arrCats);

			return($arrCats);

		}

		

		

		
		public static function getPost($postID){
			$products = new Product($postID,true,Context::getcontext()->language->id,Context::getcontext()->shop->id);

			$post = $products;

			if(empty($post))

				UniteFunctionsRev::throwError("Post with id: $postID not found");

			

			$arrPost = (array)$post;

			return($arrPost);

		}


		public static function updatePostState($postID,$state){

			$arrUpdate = array();

			$arrUpdate["ID"] = $postID;

			$arrUpdate["post_status"] = $state;

			

			wp_update_post($arrUpdate);

		}

		

	

		public static function updatePostOrder($postID,$order){

			$arrUpdate = array();

			$arrUpdate["ID"] = $postID;

			$arrUpdate["menu_order"] = $order;

			

			wp_update_post($arrUpdate);

		}

		

	

		public static function getUrlPostImage($postID,$size = self::THUMB_FULL){

			

			$post_thumbnail_id = get_post_thumbnail_id( $postID );

			if(empty($post_thumbnail_id))

				return("");

			

			$arrImage = wp_get_attachment_image_src($post_thumbnail_id,$size);

			if(empty($arrImage))

				return("");

			

			$urlImage = $arrImage[0];

			return($urlImage);

		}

		

	

		public static function getPostThumbID($postID){

			$thumbID = get_post_thumbnail_id( $postID );

			return($thumbID);

		}

		

		


		public static function getAttachmentImage($thumbID,$size = self::THUMB_FULL){

			

			$arrImage = wp_get_attachment_image_src($thumbID,$size);

			if(empty($arrImage))

				return(false);

			

			$output = array();

			$output["url"] = UniteFunctionsRev::getVal($arrImage, 0);

			$output["width"] = UniteFunctionsRev::getVal($arrImage, 1);

			$output["height"] = UniteFunctionsRev::getVal($arrImage, 2);

			

			return($output);

		}

		

		

	

		public static function getUrlAttachmentImage($thumbID,$size = self::THUMB_FULL){

			$arrImage = wp_get_attachment_image_src($thumbID,$size);

			if(empty($arrImage))

				return(false);

			$url = UniteFunctionsRev::getVal($arrImage, 0);

			return($url);

		}

		

		

		public static function getUrlSlidesEditByCatID($catID){

			

			$url = self::$urlAdmin;

			$url .= "edit.php?s&post_status=all&post_type=post&action=-1&m=0&cat=".$catID."&paged=1&mode=list&action2=-1";

			

			return($url);

		}

		

	

		public static function getUrlEditPost($postID){

			$url = self::$urlAdmin;

			$url .= "post.php?post=".$postID."&action=edit";

			

			return($url);

		}

		

		

		public static function getUrlNewPost(){

			$url = self::$urlAdmin;

			$url .= "post-new.php";

			return($url);

		}

		

		

		

		public static function deletePost($postID){

			$success = wp_delete_post($postID,false);

			if($success == false)

				UniteFunctionsRev::throwError("Could not delete post: $postID");

		}


		public static function updatePostThumbnail($postID,$thumbID){

			set_post_thumbnail($postID, $thumbID);

		}

		


		public static function getIntroFromContent($text){

			$intro = "";

			if(!empty($text)){

				$arrExtended = get_extended($text);

				$intro = UniteFunctionsRev::getVal($arrExtended, "main");

				

				

			}

			

			return($intro);

		}



		

		

		public static function getExcerptById($postID, $limit=55){

			

			// $post = get_post($postID);	

			 

			//  $excerpt = $post->post_excerpt;

			//  $excerpt = trim($excerpt);

			 

			//  $excerpt = trim($excerpt);

			//  if(empty($excerpt))

			// 	$excerpt = $post->post_content;			 

			 

			//  $excerpt = strip_tags($excerpt,"<b><br><br/><i><strong><small>");

			 

			//  $excerpt = UniteFunctionsRev::getTextIntro($excerpt, $limit);
			 $excerpt = '';

			 

			 return $excerpt;

		}		

		

	
		public static function getUserDisplayName($userID){

			

			//$displayName =  get_the_author_meta('display_name', $userID);
			$displayName =  'pro_or_blog_author_name';

			

			return($displayName);

		}

		


		public static function getCategoriesByIDs($arrIDs,$strTax = null){			

			

			if(empty($arrIDs))

				return(array());

				

			if(is_string($arrIDs))

				$strIDs = $arrIDs;

			else

				$strIDs = implode(",", $arrIDs);

			

			$args = array();

			$args["include"] = $strIDs;

							

			if(!empty($strTax)){

				if(is_string($strTax))

					$strTax = explode(",",$strTax);

				

				$args["taxonomy"] = $strTax;

			}

						

			//$arrCats = get_categories( $args );
			$arrCats = 'All_Pro_or_blog_categories';

			

			if(!empty($arrCats))

				$arrCats = UniteFunctionsRev::convertStdClassToArray($arrCats);			

			

			return($arrCats);

		}

		

	

		public static function getCategoriesByIDsShort($arrIDs,$strTax = null){

			$arrCats = self::getCategoriesByIDs($arrIDs,$strTax);

			$arrNew = array();

			foreach($arrCats as $cat){

				$catID = $cat["term_id"];

				$catName = $cat["name"];

				$arrNew[$catID] =  $catName;

			}

			

			return($arrNew);

		}

		

	

		public static function getCategoriesHtmlList($catIDs,$strTax = null){

			global $wp_rewrite;


			$categories = self::getCategoriesByIDs($catIDs,$strTax);

			

			$arrErrors = UniteFunctionsRev::getVal($categories, "errors");

			

			if(!empty($arrErrors)){

				foreach($arrErrors as $key=>$arr){

					$strErrors = implode($arr,",");				

				}

				

				UniteFunctionsRev::throwError("getCategoriesHtmlList error: ".$strErrors);

			}

			

			$rel = ( is_object( $wp_rewrite ) && $wp_rewrite->using_permalinks() ) ? 'rel="category tag"' : 'rel="category"';

			

			$separator = ',';

			

			$thelist = '';

						

			$i = 0;

			foreach ( $categories as $category ) {



				if(is_object($category))

					$category = (array)$category;

				

				if ( 0 < $i )

					$thelist .= $separator;

					

				//$catID = $category["term_id"];
				$catID = "product_term_id";

				//$link = get_category_link($catID);
				$link = 'http://categorylink.com';

				//$catName = $category["name"];
				$catName ="product_category_name";

				

				if(!empty($link))

					$thelist .= '<a href="' . $link . '" title="' .$catName . '" ' . $rel . '>' . $catName.'</a>';

				else

					$thelist .= $catName;

				

				++$i;

			}

			

			

			return $thelist;

		}

		


		public static function getTagsHtmlList($postID){

			//$tagList = get_the_tag_list("",",","",$postID);
			$tagList = 'Set All Tag List';

			return($tagList);

		}

	

		public static function convertPostDate($date){

			if(empty($date))

				return($date);

			$date = date_i18n(get_option('date_format'), strtotime($date));

			return($date);

		}

	

		public static function getTaxonomiesAssoc(){

			$arr = get_taxonomies();

			unset($arr["post_tag"]);

			unset($arr["nav_menu"]);

			unset($arr["link_category"]);

			unset($arr["post_format"]);

			

			return($arr);

		}

		

		

		public static function getPostTypesWithTaxomonies(){

			$arrPostTypes = self::getPostTypesAssoc();

			

			foreach($arrPostTypes as $postType=>$title){

				$arrTaxomonies = self::getPostTypeTaxomonies($postType);

				$arrPostTypes[$postType] = $arrTaxomonies;

			}

			

			return($arrPostTypes);

		}

		

		

		

		public static function getPostTypesWithCats(){

			$arrPostTypes = self::getPostTypesWithTaxomonies();

			

			$arrPostTypesOutput = array();

			foreach($arrPostTypes as $name=>$arrTax){



				$arrTaxOutput = array();

				foreach($arrTax as $taxName=>$taxTitle){

					$cats = self::getCategoriesAssoc($taxName);

					if(!empty($cats))

						$arrTaxOutput[] = array(

								 "name"=>$taxName,

								 "title"=>$taxTitle,

								 "cats"=>$cats);

				}

								

				$arrPostTypesOutput[$name] = $arrTaxOutput;

				

			}

			

			return($arrPostTypesOutput);

		}



		public static function getTaxonomiesWithCats(){

						

			$arrTax = self::getTaxonomiesAssoc();

			$arrTaxNew = array();

			foreach($arrTax as $key=>$value){

				$arrItem = array();

				$arrItem["name"] = $key;

				$arrItem["title"] = $value;

				$arrItem["cats"] = self::getCategoriesAssoc($key);

				$arrTaxNew[$key] = $arrItem;

			}

			

			return($arrTaxNew);

		}



	
		public static function getUrlContent(){

		

			if(self::isMultisite() == false){	//without multisite

				$baseUrl = content_url()."/";

			}

			else{	

				$arrUploadData = wp_upload_dir();

				$baseUrl = $arrUploadData["baseurl"]."/";

			}

			

			if(is_ssl()){

				$baseUrl = str_replace("http://", "https://", $baseUrl);

			}

			

			return($baseUrl);

		}



	

		public static function getPathContent(){		

			if(self::isMultisite()){

				if(!defined("BLOGUPLOADDIR")){

					$pathBase = self::getPathBase();

					$pathContent = $pathBase."wp-content/";

				}else

				  $pathContent = BLOGUPLOADDIR;

			}else{

				$pathContent = WP_CONTENT_DIR;

				if(!empty($pathContent)){

					$pathContent .= "/";

				}

				else{

					$pathBase = self::getPathBase();

					$pathContent = $pathBase."wp-content/";

				}

			}

			

			return($pathContent);

		}



		public static function getCatAndTaxData($catIDs){

			
			if(is_string($catIDs)){

				$catIDs = trim($catIDs);

				if(empty($catIDs))

					return(array("tax"=>"","cats"=>""));

				

				$catIDs = explode(",", $catIDs);

			}

			

			$strCats = "";

			$arrTax = array();

			foreach($catIDs as $cat){

				if(strpos($cat,"option_disabled") === 0)

					continue;

				

				$pos = strrpos($cat,"_");

				if($pos === false)

					UniteFunctionsRev::throwError("The category is in wrong format");

				

				$taxName = substr($cat,0,$pos);

				$catID = substr($cat,$pos+1,strlen($cat)-$pos-1);

				

				$arrTax[$taxName] = $taxName;

				if(!empty($strCats))

					$strCats .= ",";

					

				$strCats .= $catID;				

			}

			

			$strTax = "";

			foreach($arrTax as $taxName){
				if(!empty($strTax))
					$strTax .= ",";
				$strTax .= $taxName;
			}

			

			$output = array("tax"=>$strTax,"cats"=>$strCats);

			// print_r($output);
			// die();


			return($output);

		}

		

		

	

		public static function getCurrentLangCode(){

			

                        $language = Context::getContext()->language;
                        
                        $langTag = $language->iso_code;

			return($langTag);

		}

		


		public static function writeSettingLanguageFile($filepath){

			$info = pathinfo($filepath);

			$path = UniteFunctionsRev::getVal($info, "dirname")."/";

			$filename = UniteFunctionsRev::getVal($info, "filename");

			$ext =  UniteFunctionsRev::getVal($info, "extension");

			$filenameOutput = "{$filename}_{$ext}_lang.php";

			$filepathOutput = $path.$filenameOutput;

			

			

			$settings = new UniteSettingsAdvancedRev();	

			$settings->loadXMLFile($filepath);

			$arrText = $settings->getArrTextFromAllSettings();

			

			$str = "";

			$str .= "<?php \n";

			foreach($arrText as $text){

				$text = str_replace('"', '\\"', $text);

				$str .= "_e(\"$text\",\"".REVSLIDER_TEXTDOMAIN."\"); \n";				

			}

			$str .= "?>";

			

			UniteFunctionsRev::writeFile($str, $filepathOutput);

		}



		


		public static function hasShortcode($shortcode = '') {  

		      

		    $post = get_post(get_the_ID());  

		      

		    if (empty($shortcode))   

		        return $found;

		        		        

		    $found = false; 

		        

		    if (stripos($post->post_content, '[' . $shortcode) !== false )    

		        $found = true;  

		       

		    return $found;  

		}  
		public static function getSimpleProducts()
		    {
		        // if (!$context)
		            $context = Context::getContext();
		        $id_lang = (int)Context::getContext()->language->id;

		        $front = true;
		        if (!in_array($context->controller->controller_type, array('front', 'modulefront')))
		            $front = false;

		        $sql = 'SELECT p.`id_product`, pl.`name`
		                FROM `'._DB_PREFIX_.'product` p
		                '.Shop::addSqlAssociation('product', 'p').'
		                LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` '.Shop::addSqlRestrictionOnLang('pl').')
		                WHERE pl.`id_lang` = '.(int)$id_lang.'
		                '.($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').'
		                ORDER BY pl.`name`';
		        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		    }
		public static function GetRevPostDataArray($catIDs,$sortBy = self::SORTBY_ID,$direction = self::ORDER_DIRECTION_DESC,$numPosts=-1,$postTypes="any",$taxonomies="category",$arrAddition = array())
		    {
		    	if($numPosts == -1)
		    		$numPosts = null;
		    	$results = array();
		    	$categoriesid = array();
		    	$blogids = array();
		    	$ids = explode(',',$catIDs);
		    	if(isset($ids) && !empty($ids)){
		    		foreach($ids as $id){
		    			$dta_len = strlen($id);
		    			$dta_pos = strrpos($id,'_');
		    			$cat_type = substr($id,0,$dta_pos);
		    			if($cat_type == 'category'){
		    				$categoriesid[] = str_replace('category_','',$id);
		    			}elseif($cat_type == 'smartblog'){
		    				$blogids[] = str_replace('smartblog_','',$id);
		    			}
		    		}
		    	}
		    	$i = 0;
		    	if(isset($categoriesid) && !empty($categoriesid)){
		    		foreach($categoriesid as $catid){
			    		$results_temp = self::GetAllProducts($catid,$sortBy,$direction,$numPosts);
			    		foreach($results_temp as $temp){
			    			$results[$i] = $temp;
			    			$i++;
			    		}
		    		}
		    	}
		    	if(isset($blogids) && !empty($blogids)){
		    		foreach($blogids as $blgid){
		    			$results[$i] =  $blgid;
		    			$i++;
		    		}
		    	}
		    	return $results;
		    }
		public static function GetAllBlogPost()
			{
				$id_lang = (int)Context::getContext()->language->id;
				$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
				SELECT * FROM `'._DB_PREFIX_.'smart_blog_category` sbc INNER JOIN `'._DB_PREFIX_.'smart_blog_category_lang` sbcl ON(sbc.`id_smart_blog_category` = sbcl.`id_smart_blog_category` AND sbcl.`id_lang` = '.(int)($id_lang).')
				INNER JOIN `'._DB_PREFIX_.'smart_blog_category_shop` sbs ON sbs.id_smart_blog_category = sbc.id_smart_blog_category and sbs.id_shop = '.(int)Context::getContext()->shop->id.' WHERE sbc.`active`= 1');
				return $result;
   		 	}
   		 public static function GetCategoryNameById($id_category = '')
			{
				if(isset($id_category) && !empty($id_category) && $id_category != 0){
					$id_lang = (int)Context::getContext()->language->id;
					$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
					SELECT sbcl.`name` FROM `'._DB_PREFIX_.'category` sbc INNER JOIN `'._DB_PREFIX_.'category_lang` sbcl ON(sbc.`id_category` = sbcl.`id_category` AND sbcl.`id_lang` = '.(int)($id_lang).')
					INNER JOIN `'._DB_PREFIX_.'category_shop` sbs ON sbs.id_category = sbc.id_category and sbs.id_shop = '.(int)Context::getContext()->shop->id.' WHERE sbc.`active`= 1 and sbc.`id_category` = '.$id_category);
					return $result[0]['name'];
				}else{
					return false;
				}	
   		 	}
   		public static function GetAllProducts($id_category,$order_by = null, $order_way = null,$limit = null)
   		 	{
   		 		// start set prestashop value
   		 		$random = false;

   		 		if($order_by == 'ID'){
		    		$order_by = 'id_product';
		    	}
		    	elseif($order_by == 'date'){
		    		$order_by = 'date_add';
		    	}
		    	elseif($order_by == 'title'){
		    		$order_by = 'name';
		    	}
		    	elseif($order_by == 'price'){
		    		$order_by = 'price';
		    	}
		    	else{
		    		$order_by = 'position';
		    	}
		    	
		    	if($order_by == 'rand'){
		    		$random = true;
		    	}
   		 		// end set prestashop value
   		 		$random_number_products = 1;

   		 		$check_access = true;

   		 		
   		 		$id_lang = Context::getcontext()->language->id;

   		 		$context = Context::getContext();

   		 		// if ($check_access && !$this->checkAccess($context->customer->id))
   		 		// 	return false;

   		 		$active = true;

   		 		$front = true;
   		 		if (!in_array($context->controller->controller_type, array('front', 'modulefront')))
   		 			$front = false;
   		 			
   		 		if (empty($order_by))
   		 			$order_by = 'position';
   		 		else
   		 			$order_by = strtolower($order_by);

   		 		if (empty($order_way))
   		 			$order_way = 'ASC';
   		 		
   		 		$order_by_prefix = false;
   		 		if ($order_by == 'id_product' || $order_by == 'date_add' || $order_by == 'date_upd')
   		 			$order_by_prefix = 'p';
   		 		elseif ($order_by == 'name')
   		 			$order_by_prefix = 'pl';
   		 		elseif ($order_by == 'manufacturer')
   		 		{
   		 			$order_by_prefix = 'm';
   		 			$order_by = 'name';
   		 		}
   		 		elseif($order_by == 'position')
   		 			$order_by_prefix = 'cp';

   		 		if ($order_by == 'price')
   		 			$order_by = 'orderprice';

   		 		if (!Validate::isBool($active) || !Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way))
   		 			die (Tools::displayError());

   		 		$sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, MAX(product_attribute_shop.id_product_attribute) id_product_attribute, product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity, pl.`description`, pl.`description_short`, pl.`available_now`,
   		 					pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, MAX(image_shop.`id_image`) id_image,
   		 					il.`legend`, m.`name` AS manufacturer_name, cl.`name` AS category_default,
   		 					DATEDIFF(product_shop.`date_add`, DATE_SUB(NOW(),
   		 					INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).'
   		 						DAY)) > 0 AS new, product_shop.price AS orderprice
   		 				FROM `'._DB_PREFIX_.'category_product` cp
   		 				LEFT JOIN `'._DB_PREFIX_.'product` p
   		 					ON p.`id_product` = cp.`id_product`
   		 				'.Shop::addSqlAssociation('product', 'p').'
   		 				LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa
   		 				ON (p.`id_product` = pa.`id_product`)
   		 				'.Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.`default_on` = 1').'
   		 				'.Product::sqlStock('p', 'product_attribute_shop', false, $context->shop).'
   		 				LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
   		 					ON (product_shop.`id_category_default` = cl.`id_category`
   		 					AND cl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').')
   		 				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
   		 					ON (p.`id_product` = pl.`id_product`
   		 					AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').')
   		 				LEFT JOIN `'._DB_PREFIX_.'image` i
   		 					ON (i.`id_product` = p.`id_product`)'.
   		 				Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
   		 				LEFT JOIN `'._DB_PREFIX_.'image_lang` il
   		 					ON (image_shop.`id_image` = il.`id_image`
   		 					AND il.`id_lang` = '.(int)$id_lang.')
   		 				LEFT JOIN `'._DB_PREFIX_.'manufacturer` m
   		 					ON m.`id_manufacturer` = p.`id_manufacturer`
   		 				WHERE product_shop.`id_shop` = '.(int)$context->shop->id.'
   		 					AND cp.`id_category` = '.(int)$id_category
   		 					.($active ? ' AND product_shop.`active` = 1' : '')
   		 					.($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '')
   		 					
   		 					.' GROUP BY product_shop.id_product';

   		 		if ($random === true){
   		 			$sql .= ' ORDER BY RAND() LIMIT '.(int)$random_number_products;
   		 		}
   		 		else{
   		 			$sql .= ' ORDER BY '.(!empty($order_by_prefix) ? $order_by_prefix.'.' : '').'`'.bqSQL($order_by).'` '.pSQL($order_way);
   		 		}
   		 		if(isset($limit)){
   		 			$sql .= ' LIMIT '.$limit;
   		 		}
   		 		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
   		 		if ($order_by == 'orderprice')
   		 			Tools::orderbyPrice($result, $order_way);

   		 		if (!$result)
   		 			return array();

   		 		return Product::getProductsProperties($id_lang, $result);
   		 	}
	}
	UniteFunctionsWPRev::initStaticVars();
?>