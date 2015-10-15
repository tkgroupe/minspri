<?php
if (!defined('_PS_VERSION_'))
	exit;

class Override extends Module
{
	public function __construct()
	{
		$this->name = 'override';
		$this->tab = 'front_office_features';
		$this->version = '1.0.0.20151015';
		$this->author = 'Rui HUANG';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
    	$this->bootstrap = true;
		
		parent::__construct();
		
		$this->displayName = $this->l('override');
		$this->description = $this->l('override');
		
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall override?');
		
		if (!Configuration::get('MYMODULE_NAME'))
			$this->warning = $this->l('No name provided');
	}
	public function hookHeader()
	{
		
		$this->context->controller->addCSS(($this->_path).'override.css');
		$this->context->controller->addCSS(($this->_path).'override_992_1170.css');
		$this->context->controller->addCSS(($this->_path).'override_768_991.css');
		$this->context->controller->addCSS(($this->_path).'override_481_767.css');
		$this->context->controller->addCSS(($this->_path).'override_0_480.css');
		
		$this->context->controller->addJS(($this->_path).'override.js');
			
	}
	
	public function install()
	{
		if (Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);
			
		if (!parent::install() || 
		!$this->registerHook('header')
		)
			return false;
		return true;
	}
	
	public function uninstall()
	{
		if (!parent::uninstall() ||
			!Configuration::deleteByName('MYMODULE_NAME')
		)
			return false;
			
		return true;
	}
	
}