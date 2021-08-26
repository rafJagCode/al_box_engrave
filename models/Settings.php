<?php

class Settings extends ObjectModel
{
    public static function getSettings($id_product)
    {
        $defaultSettings = [
            "id_box_grawer" => null,
            "id_product" => $id_product,
            "enabled" => false,
            "reminder" => false,
        ];

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('alboxgrawer');
        $sql->where("id_product =  {$id_product}");


        $settings = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if(empty($settings)){
           return $defaultSettings;
        }
        $settings = $settings[0];
        $settings['fonts'] = json_decode($settings['fonts']);
        $settings['images'] = json_decode($settings['images']);
        $settings['icons'] = json_decode($settings['icons']);

        return $settings;
    }

    public static function updateSettings($settings){

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('alboxgrawer');
        $sql->where("id_product =  {$settings['id_product']}");

        $newRecord = empty(Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));

        $id_product = $settings['id_product'];
        $enabled = $settings['enabled'];
        $reminder = $settings['reminder'];
        $fonts = "'". $settings['fonts'] . "'";
        $images = "'". $settings['images'] . "'";
        $icons = "'". $settings['icons'] . "'";

        if($newRecord){
            $sql = "
                INSERT INTO " ._DB_PREFIX_ . "alboxgrawer
                (id_product, enabled, reminder, fonts, images, icons)
                VALUES ({$id_product}, {$enabled}, {$reminder}, {$fonts}, {$images}, {$icons})
            ";
            Db::getInstance()->execute($sql);
            return;
        }

        $sql = "
            UPDATE " ._DB_PREFIX_ . "alboxgrawer 
            SET enabled = {$enabled},
            reminder = {$reminder},
            fonts = {$fonts},
            images = {$images},
            icons = {$icons}
            WHERE id_product = {$settings['id_product']}
        ";

        Db::getInstance()->execute($sql);
    }
}
