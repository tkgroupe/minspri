<?php
// if (!defined('_PS_VERSION_'))
//     exit;
  include_once('../../config/config.inc.php');
  include_once('../../init.php');
  include_once('inc_php/revslider_globals.class.php');
  include_once('inc_php/revslider_db.class.php');
// defined('_PS_VERSION_') OR die('No Direct Script Access Allowed');
$action = Tools::getValue('action');
$mod_url = context::getcontext()->shop->getBaseURL()."modules/revsliderprestashop/";  
switch($action){
    case 'revsliderprestashop_show_image':
        $imgsrc = Tools::getValue('img');
        if($imgsrc){
            if(is_numeric($imgsrc)){
                $table = _DB_PREFIX_.GlobalsRevSlider::TABLE_ATTACHMENT_IMAGES;
                $result = rev_db_class::rev_db_instance()->get_var("SELECT file_name FROM {$table} WHERE ID={$imgsrc}");
                if(empty($result)){
                    die();
                }
                $imgsrc = "uploads/$result";
            }else{
                $imgsrc = str_replace('../','',  urldecode($imgsrc));
            }
            
            if(strpos($imgsrc,'uploads') !== FALSE){
                $file = @getimagesize($imgsrc);

                if(!empty($file) && isset($file['mime'])){
                    $size = GlobalsRevSlider::IMAGE_SIZE_MEDIUM;
                    $filename = basename($imgsrc);
                    $filetitle = substr($filename,0,strrpos($filename,'.'));
                    $fileext = substr($filename,strrpos($filename,'.'));
                    
                    $newfile = "uploads/{$filetitle}-{$size}x{$size}{$fileext}";
                    
                    if($newfilesize = @getimagesize($newfile)){
                        $file = $newfilesize;
                        $imgsrc = $newfile;
                    }
                    header('Content-Type:'.$file['mime']);
                    echo file_get_contents($mod_url.$imgsrc);
                } 
            }
        }
        break;
}
die();
