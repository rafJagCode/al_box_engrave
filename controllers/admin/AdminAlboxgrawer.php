<?php
require_once _PS_MODULE_DIR_.'al_box_grawer/models/Settings.php';

class AdminAlboxgrawerController extends ModuleAdminController
{
    public function __construct(){
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initContent(){
        parent::initContent();

        // enable these lines if you're stuck with damn stupid blank page with just 500 error
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);


        if(Tools::getValue('action') === 'updateSettings'){
            $this->updateSettings();
            return;
        }

        $this->displaySettings();

    }

    protected function ajaxRenderJson($content)
    {
        header('Content-Type: application/json');
        $this->ajaxRender(json_encode($content));
    }

    public function displaySettings(){

        $id_product = $_GET['id_product'];
        $settings = Settings::getSettings($id_product);

        $availableSettings = [
            'fonts' => $this->getAvailableFonts(),
            'images' => $this->getAvailableBoards(),
            'icons' => $this->getAvailableIcons()
        ];

        $this->context->smarty->assign(
            array(
                'settings' => $settings,
                'availableSettings' => $availableSettings
            ));


        $this->setTemplate('settings.tpl');
    }

    public function updateSettings(){
        $settings = Tools::getValue('settings');
        Settings::updateSettings($settings);
        $this->clearCache();

        $this->ajaxRenderJson('updated');
    }

    public function clearCache(){
        Tools::clearSmartyCache();
        Tools::clearXMLCache();
        Media::clearCache();
        Tools::generateIndex();
    }

    public function getAvailableBoards(){
        $path    = _PS_MODULE_DIR_ . 'al_box_grawer/boards';
        $files = array_diff(scandir($path), array('.', '..'));
        return $files;
    }

    public function getAvailableIcons(){
        $path    = _PS_MODULE_DIR_ . 'al_box_grawer/icons';
        $files = array_diff(scandir($path), array('.', '..'));
        return $files;
    }

    public function getAvailableFonts(){
        $path    = _PS_MODULE_DIR_ . 'al_box_grawer/fonts';
        $files = array_diff(scandir($path), array('.', '..'));
        return $files;
    }
}