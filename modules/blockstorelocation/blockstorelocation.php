<?php

if ( !defined('_PS_VERSION_'))
	exit;

class BlockStoreLocation extends Module {

	private $tableName = 'store_location';
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
		if ( !parent::install() || !$this->registerHook('displayHome') || !$this->registerHook('header') || !$this->registerHook('displayBackOfficeHeader') )
			return false;	

		if (false === $this->installDB())
			return false;

		// Create default values
		$title       = 'Notre magasin';
		$description = "Lorem ipsum ...";
		$address     = "Paris";

		Db::getInstance()->insert($this->tableName, array(
			'title'       => $title,
			'description' => $description,
			'address'     => $address,
		));

		return true;
	}

	public function installDB()
	{
		return Db::getInstance()->execute('
		CREATE TABLE `'._DB_PREFIX_.$this->tableName.'` (
			`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`title` VARCHAR(255) NOT NULL,
			`description` TEXT,
			`address` VARCHAR(255) NOT NULL,
			PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;');
	}

	public function uninstall()
	{
		if ( !parent::uninstall() )
			return false;

		if (false === $this->uninstallDB())
			return false;

		return true;
	}

	/**
	 * Drop table
	 * @return bool
	 */
	public function uninstallDB()
	{
		return Db::getInstance()->execute('
		DROP TABLE `'._DB_PREFIX_.$this->tableName.'`;');
	}

	public function getContent()
	{
		$output = null;

		if (Tools::isSubmit('submit'.$this->name)) {
			$title       = strval(Tools::getValue('BLOCK_STORE_LOCATION_TITLE'));
			$description = strval(Tools::getValue('BLOCK_STORE_LOCATION_DESCRIPTION'));
			$address     = strval(Tools::getValue('BLOCK_STORE_LOCATION_ADDRESS'));

			if (!$title || empty($title) || !Validate::isGenericName($title) ||
				!Validate::isGenericName($description) ||
				!$address || empty($address) || !Validate::isGenericName($address)
			) {
				$output .= $this->displayError($this->l('Configuration invalide !.'));
			} else {
				$response = $this->update($title, $description, $address);

				if (false === $response)
					$output .= $this->displayError($this->l('Une erreur s\'est produite lors de la mise à jour.'));

				$output .= $this->displayConfirmation($this->l('Configuration valide.'));
			}
		}

		return $output.$this->displayForm();
	}

	public function hookDisplayHome($params)
	{
		$data = $this->getData();
		$this->context->smarty->assign([
			'title'       => $data['title'],
			'description' => $data['description'],
			'address'     => $data['address'],
		]);

		return $this->display(__FILE__, 'blockstorelocation.tpl');
	}

	public function hookDisplayHeader($params)
	{
		$data = $this->getData();
		$content = '<script>var title = "'.$data["title"].'";
		 var description = "'.$data["description"].'";
		 var address = "'.$data["address"].'";</script>';

		// Obliger de mettre les liens comme ça, sinon variables non définies ...
		$content .= '<script src="https://maps.googleapis.com/maps/api/js"></script>';
		$content .= '<script src="'.$this->_path.'views/js/blockstorelocation.js"></script>';
//		$this->context->controller->addJS('https://maps.googleapis.com/maps/api/js', false);
//		$this->context->controller->addJS($this->_path.'views/js/blockstorelocation.js', true);

		return $content;
	}

	public function hookDisplayBackOfficeHeader()//note the case of hook name
	{

		$this->context->controller->addJS('http://maps.googleapis.com/maps/api/js?libraries=places"
        async defer');
		$this->context->controller->addJS($this->_path.'views/js/adminbosearch.js');
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
					'label'    => $this->l('Titre pour le marqueur'),
					'name'     => 'BLOCK_STORE_LOCATION_TITLE',
					'size'     => 20,
					'required' => true,
				],
				[
					'type'     => 'textarea',
					'label'    => $this->l('Description pour le marqueur'),
					'name'     => 'BLOCK_STORE_LOCATION_DESCRIPTION',
					'size'     => 20,
					'required' => false,
				],
				[
					'type'     => 'text',
					'label'    => $this->l('Adresse du magasin'),
					'name'     => 'BLOCK_STORE_LOCATION_ADDRESS',
					'size'     => 20,
					'required' => true,
				],
			],
			'submit' => [
				'title' => $this->l('Envoyer'),
				'class' => 'btn btn-default pull-right',
			]
		];

		$default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
		$helper       = new HelperForm();
 
		// Module, Token and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		 
		// Language
		$helper->default_form_language    = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;
		 
		// title and Toolbar
		$helper->title          = $this->displayName;
		$helper->show_toolbar   = true;        // false -> remove toolbar
		$helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
		$helper->submit_action  = 'submit'.$this->name;
		$helper->toolbar_btn    = array(
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
		$data = $this->getData();
		$helper->fields_value['BLOCK_STORE_LOCATION_TITLE']       = $data['title'];
		$helper->fields_value['BLOCK_STORE_LOCATION_DESCRIPTION'] = $data['description'];
		$helper->fields_value['BLOCK_STORE_LOCATION_ADDRESS']     = $data['address'];

		return $helper->generateForm($fields_form);
	}

	public function getData()
	{
		$sql = 'SELECT * FROM '._DB_PREFIX_.$this->tableName;
		if ($results = Db::getInstance()->getRow($sql))
			return $results;

		return false;
	}

	public function update($title, $description, $address)
	{
		$sql = 'UPDATE '._DB_PREFIX_.$this->tableName.' SET title="'.$title.'",
			description="'.$description.'",
			address="'.$address.'"
			where id = "'.$this->getData()['id'].'"';

		if (!Db::getInstance()->execute($sql))
			return false;

		return true;
	}
}