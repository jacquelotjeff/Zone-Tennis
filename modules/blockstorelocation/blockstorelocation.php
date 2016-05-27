<?php

if ( !defined('_PS_VERSION_'))
	exit;

class BlockStoreLocation extends Module {

	public function __construct()
	{
		$this->name             = 'blockstorelocation';
		$this->displayName      = $this->l('Localisation de votre magasin');
		$this->description      = $this->l('Affiche un marqueur sur une Google map en prenant en compte l\'adresse du magasin.');
		$this->confirmUninstall = $this->l('Êtes-vous sûre de vouloir supprimer le module de localisation?');

		$this->tab     				  = 'front_office_features';
		$this->version                = '1.0.0';
		$this->author                 = 'moi';
		$this->need_instance          = 0;
		$this->ps_versions_compliancy = [
			'min' => '1.6',
			'max' => _PS_VERSION_
		];
		$this->bootstrap = true;

		parent::__construct();
	}

	public function install()
	{
		if ( !parent::install() || !Configuration::updateValue('BLOCK_STORE_LOCATION_ADDRESS', 'Paris') ||
			!$this->registerHook('leftColumn'))
			return false;

		return true;
	}

	public function uninstall()
	{
		if ( !parent::uninstall() || !configuration::deleteByName('BLOCK_STORE_LOCATION_ADDRESS'))
			return false;

		return true;
	}

	public function getContent()
	{
		$output = null;

		if (Tools::isSubmit('submit'.$this->name)) {
			$myoption_txt = strval(Tools::getValue('BLOCK_STORE_LOCATION_ADDRESS'));

			if (!$myoption_txt || empty($myoption_txt) || !Validate::isGenericName($myoption_txt)) {
				$output .= $this->displayError($this->l('Configuration invalide.'));
			} else {
				Configuration::updateValue('MYOPTION', $myoption_txt);
				$output .= $this->displayConfirmation($this->l('Configuration invalide.'));
			}
		}

		return $output.$this->displayForm();
	}

	public function hookDisplayLeftColumn($params)
	{
		$this->context->smarty->assign([
			'adress' => Configuration::get('BLOCK_STORE_LOCATION_ADDRESS'),
		]);

		return $this->display(__FILE__, 'blockstorelocation.tpl');
	}

	public function displayForm()
	{
		$fields_form[0]['form'] = [
			'legend' => [
				'title' => $this->l('Configuration du module de localisation du magasin'),
			],
			'input' => [
				[
					'type'     => 'text',
					'label'    => $this->l('Adresse à marquer'),
					'name'     => 'BLOCK_STORE_LOCATION_ADDRESS',
					'size'     => 20,
					'required' => true,
				]
			],
			'submit' => [
				'title' => $this->l('Envoyer'),
				'class' => 'btn btn-default pull-right',
			]
		];

		$default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
		$helper = new HelperForm();
 
		// Module, Token and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		 
		// Language
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;
		 
		// title and Toolbar
		$helper->title = $this->displayName;
		$helper->show_toolbar = true;        // false -> remove toolbar
		$helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array(
		    'save' =>
		    array(
		        'desc' => $this->l('Save'),
		        'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
		        '&token='.Tools::getAdminTokenLite('AdminModules'),
		    ),
		    'back' => array(
		        'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
		        'desc' => $this->l('Retour à la liste')
		   )
		);
		 
		// Load current value
		$helper->fields_value['BLOCK_STORE_LOCATION_ADDRESS'] = Configuration::get('BLOCK_STORE_LOCATION_ADDRESS');
		 
		return $helper->generateForm($fields_form);

	}
}