<?php
require_once _PS_MODULE_DIR_.'al_box_grawer/models/Settings.php';

class al_box_grawerBuygrawerModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $action = Tools::getValue('action');


        if ($action === 'buyGrawer') {
            $url = $this->saveScreenshot();

            $grawer_product_id = Configuration::get('AL_BOX_GRAWER_GRAWER_PRODUCT_ID');
            $box_grawer_customization_id = Configuration::get('box_grawer_customization_id');

            $this->context->cart->addTextFieldToProduct($grawer_product_id, $box_grawer_customization_id, Product::CUSTOMIZE_TEXTFIELD, $url);
            $customization = $this->context->cart->getProductCustomization($grawer_product_id, Product::CUSTOMIZE_TEXTFIELD, true);

            header('Content-Type: application/json');
            die(json_encode([
                'grawer_id' => $grawer_product_id,
                'customization_id' => $customization[0]['id_customization']
            ]));
        }

        if ($action === 'getSettings') {
            $id_product = Tools::getValue('id_product');
            $settings = Settings::getSettings($id_product);

            if (!$settings['enabled']) {
                return;
            }

            $availableBoards = $this->getAvailableBoards();
            $availableIcons = $this->getAvailableIcons();
            $availableFonts = $this->getAvailableFonts();

            $settings['images'] = array_diff($availableBoards, $settings['images']);
            $settings['icons'] = array_diff($availableIcons, $settings['icons']);
            $settings['fonts'] = array_diff($availableFonts, $settings['fonts']);

            header('Content-Type: application/json');
            die(json_encode([
                'settings' => $settings,
            ]));
        }

    }

    public function saveScreenshot()
    {
        $board = Tools::getValue('board');
        $screenshot = Tools::getValue('screenshot');
        $fonts = Tools::getValue('fonts');

        $fileName = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . $board . date("Y-m-d-H:i:s") . '.html';
        $dir = _PS_MODULE_DIR_ . 'al_box_grawer/screenshots/' . $fileName;

        $meta = '<meta charset="UTF-8">';
        $style = $this->getStyle();
        $fontScript = $this->getFontScript($fonts);
        file_put_contents($dir, $meta . $style . $screenshot . $fontScript);

        $url = _PS_BASE_URL_ . _MODULE_DIR_ . 'al_box_grawer/screenshots/' . $fileName;

        return $url;
    }

    public function getAvailableBoards()
    {
        $path = _PS_MODULE_DIR_ . 'al_box_grawer/boards';
        $files = array_diff(scandir($path), array('.', '..'));
        return $files;
    }

    public function getAvailableIcons()
    {
        $path = _PS_MODULE_DIR_ . 'al_box_grawer/icons';
        $files = array_diff(scandir($path), array('.', '..'));
        return $files;

    }

    public function getAvailableFonts()
    {
        $path = _PS_MODULE_DIR_ . 'al_box_grawer/fonts';
        $files = array_diff(scandir($path), array('.', '..'));
        return $files;

    }

    public function getFontScript($fonts){
        $fonts = json_encode($fonts);
        $base_url = _PS_BASE_URL_;
        return
            "<script>
                function getFontUrl(font){
                  let domain_url = '{$base_url}';
                  let url = domain_url + '/modules/al_box_grawer/fonts/' + font;
                  return 'url(' + url + ')';
                }
                function fontWithoutExtension(font) {
                  return font.replace(/\.[^/.]+$/, '');
                }
                (function(){
                    let fonts = {$fonts};
                    fonts.forEach((font)=>{
                        let fontFace = new FontFace(this.fontWithoutExtension(font), getFontUrl(font));
                        fontFace.load().then(function(loaded_face) {
                            document.fonts.add(loaded_face);
                        })
                    })
                })();
         </script>";
    }

    public function getStyle(){
        return
            "<style>
                .added-icon_control{
                    position: absolute;
                }
            </style>";
    }
}