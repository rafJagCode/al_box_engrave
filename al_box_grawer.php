<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_.'al_box_grawer/models/Settings.php';

class Al_box_grawer extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'al_box_grawer';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Rafał Jagielski';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Box Grawer');
        $this->description = $this->l('Moduł dodający fukcje wyboru graweru na pudełkach');

        $this->confirmUninstall = $this->l('Are you sure you wan\'t to uninstall?');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('AL_BOX_GRAWER_LIVE_MODE', false);

        return
            $this->registerAdminModule() &&
            parent::install() &&
            $this->createDB() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayAdminProductsMainStepLeftColumnBottom') &&
            $this->registerHook('displayProductAdditionalInfo') &&
            $this->createGrawerCustomization();

    }

    public function uninstall()
    {
        Configuration::deleteByName('AL_BOX_GRAWER_LIVE_MODE');

        return
            $this->unregisterAdminModule() &&
            parent::uninstall() &&
            $this->removeDB();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitAl_box_grawerModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();

    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAl_box_grawerModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'AL_BOX_GRAWER_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'AL_BOX_GRAWER_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'AL_BOX_GRAWER_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'AL_BOX_GRAWER_GRAWER_PRODUCT_ID',
                        'label' => $this->l('GrawerId'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'AL_BOX_GRAWER_LIVE_MODE' => Configuration::get('AL_BOX_GRAWER_LIVE_MODE', true),
            'AL_BOX_GRAWER_ACCOUNT_EMAIL' => Configuration::get('AL_BOX_GRAWER_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'AL_BOX_GRAWER_ACCOUNT_PASSWORD' => Configuration::get('AL_BOX_GRAWER_ACCOUNT_PASSWORD', null),
            'AL_BOX_GRAWER_GRAWER_PRODUCT_ID' => Configuration::get('AL_BOX_GRAWER_GRAWER_PRODUCT_ID', 2113),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    public function hookDisplayAdminProductsMainStepLeftColumnBottom($params)
    {

        if (!isset($params['id_product']) || !(int)$params['id_product']) {
            return;
        }

        $id_product = $params['id_product'];

        $link = new Link();
        $settingsLink = $link->getAdminLink('AdminAlboxgrawer') . "&id_product={$id_product}";

        $this->context->smarty->assign([
            'settingsLink' => $settingsLink
        ]);


        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/goToSettings.tpl');
    }

    public function createDB()
    {
        return Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'alboxgrawer
			(
				`id_box_grawer` int(11) NOT NULL AUTO_INCREMENT,
				`id_product` int(11) UNSIGNED NOT NUll,
				`enabled` boolean DEFAULT "0",
				`reminder` boolean DEFAULT "0",
				`fonts` json,
				`images` json,
				`icons` json,
				PRIMARY KEY (`id_box_grawer`)
			)');
    }

    public function removeDB()
    {
        return Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'alboxgrawer`');
    }

    public function registerAdminModule()
    {
        $tab = new Tab();
        $tab->id_parent = -1;
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Amelen Box Grawer';
        }
        $tab->class_name = 'AdminAlboxgrawer';
        $tab->module = $this->name;
        $tab->active = 1;
        $tab->add();
        return $tab->save();
    }

    public function unregisterAdminModule()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminAlboxgrawer');
        $tab = new Tab($id_tab);
        return $tab->delete();
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        $product = new Product(Tools::getValue('id_product'));
        $id_product = $product->id_product;

        $settings = Settings::getSettings($id_product);

        if (!$settings['enabled']) {
            return;
        }

        $controller_url = $this->context->link->getModuleLink('al_box_grawer', 'buygrawer');
        $info['controller_url'] = $controller_url;
        $info['domain_url'] = _PS_BASE_URL_;
        $info['id_product'] = $id_product;

        $this->context->smarty->assign([
            'pathApp' => $this->getPathUri() . 'views/js/app.js',
            'chunkVendor' => $this->getPathUri() . 'views/js/chunk-vendors.js',
            'info' => json_encode($info)
        ]);


        return $this->context->smarty->fetch($this->local_path . 'views/templates/front/app.tpl');
    }

    public function createGrawerCustomization()
    {
        $idOfExistingCustomizationField = $this->getIdOfExistingCustomizationField();

        if (empty($idOfExistingCustomizationField)) {
            return $this->addCustomizationFieldToDb();
        }

        Configuration::updateValue('box_grawer_customization_id', $idOfExistingCustomizationField);
        return true;

    }

    public function getIdOfExistingCustomizationField()
    {
        $sql = 'SELECT id_customization_field FROM ' . _DB_PREFIX_ . 'customization_field_lang' . " WHERE name = 'Grawer Box Preview: '";
        return Db::getInstance()->getValue($sql);
    }

    public function addCustomizationFieldToDb()
    {
        $grawer_product_id = Configuration::get('AL_BOX_GRAWER_GRAWER_PRODUCT_ID');
        $sql = "
            INSERT INTO " . _DB_PREFIX_ . "customization_field
            (id_product, type, required, is_module, is_deleted)
            VALUES ({$grawer_product_id}, 1, 0, 0, 0) 
        ";

        $addedCustomizationField = Db::getInstance()->execute($sql);

        if (!$addedCustomizationField) return false;

        $sql = 'SELECT id_customization_field FROM ' . _DB_PREFIX_ . 'customization_field' . ' ORDER BY id_customization_field DESC';
        $lastCustomizationFieldId = Db::getInstance()->getValue($sql);
        $sql = "
            INSERT INTO " . _DB_PREFIX_ . "customization_field_lang
            (id_customization_field, id_lang, id_shop, name)
            VALUES ({$lastCustomizationFieldId}, 1, 1, 'Grawer Box Preview: ')
        ";

        Configuration::updateValue('box_grawer_customization_id', $lastCustomizationFieldId);
        return Db::getInstance()->execute($sql);
    }
}