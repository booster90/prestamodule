<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestaModule extends Module {
    
    public function __construct() {
        $this->name = 'prestamodule';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Krystian';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
        $this->bootstrap = true;
 
        parent::__construct();

        $this->displayName = $this->l('presta module');
        $this->description = $this->l('możliwość dodania zdjęcia z podpisem i linkiem + możliwość wyświetlenia go w: lewa/prawa kolumna lub strona główna');

        $this->confirmUninstall = $this->l('jestes pewien ze chcesz usunac ten modul?');

        if (!Configuration::get('MYMODULE_NAME')) {
            $this->warning = $this->l('No name provided');
        }
    }
    
    public function install() {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        return parent::install() && $this->registerHook('leftColumn') && $this->registerHook('rightColumn') && $this->registerHook('header') && 
            Configuration::updateValue('MYMODULE_NAME', 'xxx') && Configuration::updateValue('MYMODULE_URL', 'http://test.lccode.xyz/');
    }
    
    public function uninstall() {
        if (!parent::uninstall() || !Configuration::deleteByName('MYMODULE_NAME')) {
            return false;
        }

        return true;
    }
    
    /**
     * USTAWIENIA
     * 
     * @return type
     */
    public function getContent() {
        $output = null;
        $picUrl = _PS_UPLOAD_DIR_ . 'prestamodule';

        if (Tools::isSubmit('submit' . $this->name)) {
            $name = strval(Tools::getValue('MYMODULE_NAME'));
            $url = strval(Tools::getValue('MYMODULE_URL'));
            $pathForNewFile = strval(Tools::getValue('MYMODULE_PIC'));
            
            if ($pathForNewFile == 1 || $pathForNewFile == '') {
                $pathForNewFile = '/modules/prestamodule/img/logo.png';
            }
            
            if (!is_dir($picUrl)) {
                mkdir($picUrl);
                chmod($picUrl, 0777);
            }
            
            if (!$_FILES['MYMODULE_FILE']['error']) {
                $pathForNewFile =  $picUrl . "/" . $_FILES['MYMODULE_FILE']['name'];
                $pic = move_uploaded_file($_FILES['MYMODULE_FILE']['tmp_name'], $pathForNewFile);    
                $pathForNewFile = substr($pathForNewFile, strpos($pathForNewFile, '/upload'));
            }
            
           
            if (!$name || empty($name) || !Validate::isGenericName($name) || empty($url)) {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            } else {
                Configuration::updateValue('MYMODULE_NAME', $name);
                Configuration::updateValue('MYMODULE_URL', $url);
                Configuration::updateValue('MYMODULE_PIC', $pathForNewFile);
                
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        
        return $output.$this->displayForm();
    }
    
    /**
     * Settings view
     * 
     * @return type
     */
    public function displayForm() {
        // Get default language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fields_form[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('name'),
                    'name' => 'MYMODULE_NAME',
                    'size' => 20,
                    'required' => true
                ], [
                    'type' => 'text',
                    'label' => $this->l('url'),
                    'name' => 'MYMODULE_URL',
                    'size' => 20,
                    'required' => false
                ], [
                    'type' => 'file',
                    'label' => $this->l('pic'),
                    'name' => 'MYMODULE_FILE',
                    'size' => 20,
                    'required' => false
                ]
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules')
            ],
            'back' => [
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            ]
        ];

        // Load current value
        $helper->fields_value['MYMODULE_NAME'] = Configuration::get('MYMODULE_NAME');
        $helper->fields_value['MYMODULE_URL'] = Configuration::get('MYMODULE_URL');
        $helper->fields_value['MYMODULE_PIC'] = Configuration::get('MYMODULE_PIC');

        return $helper->generateForm($fields_form);
    }
    
    
    public function hookDisplayLeftColumn($params) {
        $this->context->smarty->assign(
            [
                'presta_module_name' => Configuration::get('MYMODULE_NAME'),
                'presta_module_link' => Configuration::get('MYMODULE_URL'),
                'presta_module_pic' => Configuration::get('MYMODULE_PIC')
            ]
        );
  
    return $this->display(__FILE__, 'prestamodule.tpl');
}
   
    public function hookDisplayRightColumn($params) {
        return $this->hookDisplayLeftColumn($params);
    }

    public function hookDisplayHeader() {
        $this->context->controller->addCSS($this->_path . 'css/prestamodule.css', 'all');
    }   
}