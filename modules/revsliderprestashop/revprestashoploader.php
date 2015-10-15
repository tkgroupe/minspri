<?php
if((!defined('__DIR__')))
    define('__DIR__',dirname(__FILE__));

$dir = _PS_MODULE_DIR_.'revsliderprestashop';
if(!defined('ABSPATH'))
    define('ABSPATH', $dir);
define('WP_CONTENT_DIR', $dir);

define('ARRAY_A', true);

define('OBJECT', false);


$currentFolder = $dir;

$folderIncludes = "{$currentFolder}/inc_php/framework/";



//include frameword files

require_once $folderIncludes . 'include_framework.php';

require_once $currentFolder . '/inc_php/revslider_db.class.php'; // added by rakib on 2nd Jan, 2013

////include bases

require_once $folderIncludes . 'base.class.php';

require_once $folderIncludes . 'elements_base.class.php';

require_once $folderIncludes . 'base_admin.class.php';

require_once $folderIncludes . 'base_front.class.php';


////include product files

require_once $currentFolder . '/inc_php/revslider_settings_product.class.php';

require_once $currentFolder . '/inc_php/revslider_globals.class.php';

require_once $currentFolder . '/inc_php/revslider_operations.class.php';

require_once $currentFolder . '/inc_php/revslider_slider.class.php';

require_once $currentFolder . '/inc_php/revslider_output.class.php';

require_once $currentFolder . '/inc_php/revslider_slide.class.php';

//require_once $currentFolder . '/inc_php/revslider_widget.class.php';

require_once $currentFolder . '/inc_php/revslider_params.class.php';



require_once $currentFolder . '/inc_php/revslider_tinybox.class.php';

require_once $currentFolder . '/inc_php/fonts.class.php'; //punchfonts

require_once $currentFolder . '/inc_php/hooks.class.php'; //prestashop hooks

require_once $currentFolder . '/inc_php/extension.class.php';



function bloginfo($prop) {

    switch ($prop):

        case 'charset':

            echo "UTF-8";

            break;

        default : break;

    endswitch;

}

function rev_get_token(){
    $token = Context::getcontext()->controller->token;
    if(isset($token))
        return $token;
    return false;
}

function is_multisite(){
    if(Shop::isFeatureActive()){
        return true;
    }else{
        return false;
    }
}

function is_ssl(){

    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')

        return true;

    return false;

}

function is_admin() {

    $cookie = new Cookie('psAdmin');    

    if ($cookie->id_employee){

        return true;

    }

    return false;

}



function rev_title() {

    if (is_admin()) {

        echo "Revolution Slider";

        return;

    }

    echo "Homepage";

}



function load_additional_scripts($deps = array(), $parent) {

    if (empty($deps) || !is_array($deps))

        return false;



    $load = array();



    foreach ($deps as $dep) {



//        if(!empty($parent)){

//            foreach($parent as $p):                

//                if(!isset($p->deps)) continue 2;                

//                if(!is_array($p->deps) || array_key_exists($dep, $p->deps)) continue 2;                

//            endforeach;

//        }





        switch ($dep) {

            case 'jquery':

                $load[$dep] = 'js/jquery-1.9.1.min.js';

                break;

            // case 'thickbox':

            //     $load[$dep] = 'js/thickbox.js';

            //     break;

            default:

                break;

        }

    }

    return $load;

}



function get_url($link = '') {

    $url = _MODULE_DIR_ ."revsliderprestashop";            

    return $url;

}
function plugin_dir_path($link = '') {

    $url = context::getcontext()->shop->getBaseURL() ."modules/revsliderprestashop/";            

    return $url;

}



function uploads_url($src = ''){

    return get_url().'/uploads/'.$src;

}



function script_url() {

    return get_url() . '/';

}
function controller_upload_url($link = '') {
    $hash = Tools::encrypt(GlobalsRevSlider::MODULE_NAME);
    $cntrl =  Context::getContext()->link->getAdminLink('Revolutionslider_upload').'&security_key='.$hash;
    $url = $cntrl.$link;
    return $url;
}


function admin_url($link = '') {

    $url = $_SERVER['PHP_SELF'];

    preg_match('/\?(.*)$/', $link, $found);
  
    $arr = $_GET;

    if (isset($found[1]) && !empty($found[1])) {

        if(!preg_match('/\&id\=/',$found[1])){            

            unset($arr['id']);

        }

        if(isset($arr['conf']))
            unset($arr['conf']);

        $level1 = explode('&',$found[1]);

        foreach($level1 as $level2){

            $lv2 = explode('=',$level2);            

            $arr[$lv2[0]] = $lv2[1];                       

        }    

    }

    $url .= '?'.http_build_query($arr);

    return $url;

}







function plugins_url($file = '') {

    if (!empty($file)) {

        return get_url(dirname($file));

    }

    return __DIR__;

}



function content_url($link = '') {
    return get_url($link);

}

function rev_media_folder(){
        $folder = _PS_ROOT_DIR_.'/img/cms/revolution/';
        if(!file_exists($folder)){
           if(!mkdir($folder, 0755, true)) {
                $folder = _PS_ROOT_DIR_.'img/cms/';
            }else{
                $folder = _PS_ROOT_DIR_.'img/cms/revolution/';
            }
        }
        return $folder;
}
function rev_media_folderuri(){
        $folder = _PS_ROOT_DIR_.'/img/cms/revolution/';
        if(!file_exists($folder)){
                $folder = __PS_BASE_URI__.'img/cms/';
        }else{
            $folder = __PS_BASE_URI__.'img/cms/revolution/';
        }
        return $folder;
}
function rev_media_url($link = ''){
    // return get_url($link);
   $folder = rev_media_folderuri().$link;
   return $folder;
}


function get_template_directory_uri() {

    return get_url();

}



function get_image_real_size($image){
    $filepath = ABSPATH.'/uploads/'.$image;
    // $filepath = rev_media_folder().$image;    
    if(file_exists($filepath))
        return list($width,$height) = getimagesize ($filepath);
    return false;    
}



function get_image_id_by_url($image){    

    $wpdb = rev_db_class::rev_db_instance();

    $tablename = $wpdb->prefix.GlobalsRevSlider::TABLE_ATTACHMENT_IMAGES;

    $image = basename($image);

    $id = $wpdb->get_var("SELECT ID FROM {$tablename} WHERE file_name='{$image}'");

    return $id;

}





function wp_get_attachment_image_src($attach_id, $size = 'thumbnail', $args = array()){

    $wpdb = rev_db_class::rev_db_instance();

    $tablename = $wpdb->prefix.GlobalsRevSlider::TABLE_ATTACHMENT_IMAGES;

    $filename = $wpdb->get_var("SELECT file_name FROM {$tablename} WHERE ID={$attach_id}");

    

    if(!empty($filename)){

        $filerealname = substr($filename,0,strrpos($filename,'.'));

        $fileext = substr($filename,strrpos($filename,'.'),strlen($filename)-strlen($filerealname));

        $newfilename = $filerealname;

        if(gettype($size) == 'string'){

            switch($size){

                case "thumbnail":

                    $px = GlobalsRevSlider::IMAGE_SIZE_THUMBNAIL;

                    $newfilename .= "-{$px}x{$px}";                

                    break;

                case "medium":

                    $px = GlobalsRevSlider::IMAGE_SIZE_MEDIUM;

                    $newfilename .= "-{$px}x{$px}";                

                    break;

                case "large":

                    $px = GlobalsRevSlider::IMAGE_SIZE_LARGE;

                    $newfilename .= "-{$px}x{$px}";                

                    break;

                default: break;

            }

            $newfilename .= $fileext;        

            $imagesize = get_image_real_size($newfilename);

            return array(uploads_url($newfilename),$imagesize[0],$imagesize[1]);
            // return array(rev_media_url($newfilename),$imagesize[0],$imagesize[1]);
        }
    }
    return false;
}

function GetLinkobj()
{
    $ret = array();
  if(Tools::usingSecureMode())
   $useSSL = true;
  else
   $useSSL = false;
  $protocol_link = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) ? 'https://' : 'http://';
  $protocol_content = (isset($useSSL) AND $useSSL AND Configuration::get('PS_SSL_ENABLED') AND Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) ? 'https://' : 'http://';
  $link = new Link($protocol_link, $protocol_content);
  $ret['protocol_link'] = $protocol_link;
  $ret['protocol_content'] = $protocol_content;
  $ret['obj'] = $link;
  return $ret;
}
function modify_image_url($img_src = ''){
    $lnk = GetLinkobj();
    $img_pathinfo = pathinfo($img_src);
    $mainstr = $img_pathinfo['basename'];
    $static_url = __PS_BASE_URI__.'modules/revsliderprestashop/uploads/'.$mainstr;
    return $lnk['protocol_content'].Tools::getMediaServer($static_url).$static_url;
}
function modify_layer_image($img_src = ''){
    $lnk = GetLinkobj();
    $img_pathinfo = pathinfo($img_src);
    $mainstr = $img_pathinfo['basename'];
    $static_url = __PS_BASE_URI__.'modules/revsliderprestashop/uploads/'.$mainstr;
    return $lnk['protocol_content'].Tools::getMediaServer($static_url).$static_url;
}
function wp_enqueue_script($scriptName, $src = '', $deps = array(), $ver = '1.0', $in_footer = false) {
    UniteBaseClassRev::wp_enqueue_script($scriptName, $src, $deps, $ver, $in_footer);
}
function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = '', $media = 'all', $noscript = false) {

    UniteBaseClassRev::wp_enqueue_style($handle, $src, $deps, $ver, $media, $noscript);

}



function rev_head() {

    UniteBaseClassRev::rev_head();

}

function rev_footer() {

    UniteBaseClassRev::rev_footer();

}

if(!function_exists('__')){
    function __($text, $textdomain = '') {
        $mod = RevsliderPrestashop::getInstance();
        return $mod->l($text);
    }
}

if(!function_exists('_e')){
    function _e($text, $textdomain = '') {
        $mod = new RevsliderPrestashop();
        echo $mod->l($text);
    }
}

function esc_sql($data) {
    $wpdb = rev_db_class::rev_db_instance();    
    return $wpdb->_escape($data);
}

function add_shortcode($tag, $func) {

    UniteBaseClassRev::add_shortcode($tag, $func);

}



function do_shortcode($str = "") {
    // return UniteBaseClassRev::parse($str);
    if(isset($str) && !empty($str) && (bool)Module::isEnabled('smartshortcode') && (bool)Module::isInstalled('smartshortcode'))
    {
        $smartshortcode = Module::getInstanceByName('smartshortcode');
        $str =  $smartshortcode->parse($str);
    }
    return $str;
}


function get_option(){
    return true;
}

function putRevSlider($data, $putIn = "") {

   $operations = new RevOperations();

   $arrValues = $operations->getGeneralSettingsValues();

   $includesGlobally = UniteFunctionsRev::getVal($arrValues, "includes_globally", "on");

   $strPutIn = UniteFunctionsRev::getVal($arrValues, "pages_for_includes");

   $isPutIn = RevSliderOutput::isPutIn($strPutIn, true);



   if ($isPutIn == false && $includesGlobally == "off") {

       $output = new RevSliderOutput();

       $option1Name = "Include RevSlider libraries globally (all pages/posts)";

       $option2Name = "Pages to include RevSlider libraries";

       $output->putErrorMessage(__("If you want to use the PHP function \"putRevSlider\" in your code please make sure to check \" ", REVSLIDER_TEXTDOMAIN) . $option1Name . __(" \" in the backend's \"General Settings\" (top right panel). <br> <br> Or add the current page to the \"", REVSLIDER_TEXTDOMAIN) . $option2Name . __("\" option box."));

       return(false);

   }



   RevSliderOutput::putSlider($data, $putIn);

}




class sdsconfig{
    public $ocdb;
    public static function getval($key,$store_id = 0,$group = 'config')
    {
        $value = Configuration::get($key);
        if(isset($value))
            return $value;
        else
            return false;
    }
    public static function setval($key,$value='',$group = 'config',$store_id = 0,$serialized = 0)
    {
        $value = serialize($value);
        if(Configuration::updateValue($key,$value))
            return true;
        else
            return false;
    }

    public static function getcaptioncss($tabl){
        $wpdb = rev_db_class::rev_db_instance();
        $sql = "SELECT * FROM "._DB_PREFIX_.$tabl;
        $value = $wpdb->get_results($sql);
        if(isset($value))
            return $value;
        else
            return false;
    }
    public static function getgeneratecss(){
        $getcss = self::getcaptioncss(GlobalsRevSlider::TABLE_CSS_NAME);
        
        $value = UniteCssParserRev::parseDbArrayToCss($getcss, "\n");
        if(isset($value))
            return $value;
        else
            return false;
    }
    public static function getgeneratecssfile(){
        $csscontent = sdsconfig::getgeneratecss();
        $cache_filename = RevSliderAdmin::$path_plugin.'rs-plugin/css/captions.css';
        file_put_contents($cache_filename, $csscontent);
        chmod($cache_filename, 0777);
    }
    public static function getLayouts(){
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'meta` m INNER JOIN `'._DB_PREFIX_.'meta_lang` ml ON(m.`id_meta` = ml.`id_meta` AND ml.`id_lang` = '.(int)Context::getContext()->language->id.' AND ml.`id_shop` = '.(int)Context::getContext()->shop->id.')';
        $meta = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        return $meta;
    }
    public static function getrevslide(){
        $result = array();
        $wpdb = rev_db_class::rev_db_instance();
        $sql = "SELECT * FROM " .$wpdb->prefix.GlobalsRevSlider::TABLE_SLIDERS_NAME;
        $data = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        //$data = $wpdb->get_results($sql);
        if(!empty($data)){
            $i = 0;
            foreach ($data as $val) {
               $result[$i]['id'] = $val['id'];
               $result[$i]['title'] = $val['title'];
               $i = $i+1;
            }
        }
        if(!empty($result))
            return $result;
        else
            return false;
    }
     public static function get_current_store() {
        $store_id = (int)Context::getContext()->shop->id;
        if(!isset($store_id)){
                $store_id = 1;  
            }
        return $store_id;
    }
    public static function getNameById($id)
    {
        $sql = 'SELECT name
                FROM '._DB_PREFIX_.'shop_group
                WHERE id_shop_group = '.$id;
        return Db::getInstance()->getValue($sql);
    }
}



