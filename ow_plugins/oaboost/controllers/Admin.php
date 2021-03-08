<?php

/**
 * Copyright (c) 2013 Sardar Madumarov
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */
use \oacompress\bol\Service as Service;

/**
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package ow_plugins.oaboost.controllers
 * @since 1.0
 */
class OACOMPRESS_CTRL_Admin extends \ADMIN_CTRL_Abstract
{
    /**
     * @var Service
     */
    private $service;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = Service::getInstance();

        if ( !\OW::getRequest()->isAjax() )
        {
            $router = \OW::getRouter();
            $infoArr = array("index" => "ow_ic_gear_wheel", "storages" => "ow_ic_server", "cache_control" => "ow_ic_trash");
            $itemsArr = array();
            $count = 1;

            foreach ( $infoArr as $key => $iconClass )
            {
                $menuItem = new \BASE_MenuItem();
                $menuItem->setKey($key);
                $menuItem->setLabel($this->service->text("admin_tab_" . $key . "_label"));
                $menuItem->setUrl($router->urlForRoute("oacompress.admin_" . $key));
                $menuItem->setIconClass($iconClass);
                $menuItem->setOrder($count++);
                $itemsArr[] = $menuItem;
            }

            $this->menu = new \BASE_CMP_ContentMenu($itemsArr);
            $this->addComponent("contentMenu", $this->menu);

            $this->setPageHeading($this->service->text("admin_index_new_heading"));
            $this->setPageHeadingIconClass("ow_ic_gear_wheel");
            \OW::getNavigation()->activateMenuItem("admin_plugins", "admin", "sidebar_menu_plugins_installed");
            $this->addComponent("adminLogo", new oacompress\components\AdminLogo());
        }
    }

    public function index()
    {
        $form = new Form("config_form");

        $compressCss = new CheckboxField("compress_css");
        $compressCss->setLabel($this->service->text("compress_css_label"));
        $compressCss->setDescription($this->service->text("compress_css_desc"));
        $compressCss->setValue((int) $this->service->getConfig(Service::CNFG_COMPRESS_CSS));
        $form->addElement($compressCss);

        $compressJs = new CheckboxField("compress_js");
        $compressJs->setLabel($this->service->text("compress_js_label"));
        $compressJs->setDescription($this->service->text("compress_js_desc"));
        $compressJs->setValue((int) $this->service->getConfig(Service::CNFG_COMPRESS_JS));
        $form->addElement($compressJs);

        $imageSize = new CheckboxField("encode_css_image");
        $imageSize->setLabel($this->service->text("encode_css_image_label"));
        $imageSize->setDescription($this->service->text("encode_css_image_desc"));
        $imageSize->setValue((int) $this->service->getConfig(Service::CNFG_ENCODE_CSS_IMAGE));
        $form->addElement($imageSize);

//        $compressHtml = new CheckboxField("compress_html");
//        $compressHtml->setLabel($this->service->text("compress_html_label"));
//        $compressHtml->setDescription($this->service->text("compress_html_desc"));
//        $compressHtml->setValue((int) $this->service->getConfig(Service::CNFG_COMPRESS_HTML));
//        $form->addElement($compressHtml);

        $cacheQueries = new Selectbox("cache_db_queries");
        $optionsArr = array(0 => $this->service->text("no_storage"));
        $vals = array(1, 2, 3, 5, 12, 24);

        foreach ( $vals as $val )
        {
            $optionsArr[$val] = $val . " " . $this->service->text("hours_label");
        }

        $cacheQueries->setOptions($optionsArr);
        $cacheQueries->setHasInvitation(false);
        $cacheQueries->setLabel($this->service->text("cache_db_queries_hours_label"));
        $cacheQueries->setDescription($this->service->text("cache_db_queries_hours_desc"));
        $cacheQueries->setValue($this->service->getConfig(Service::CNFG_CACHE_DB_QUERIES));
        $form->addElement($cacheQueries);

        $cachePages = new Selectbox("cache_pages_for_guests");
        $optionsArr = array(0 => $this->service->text("no_storage"));
        $vals = array(1, 2, 5, 10, 30, 60);

        foreach ( $vals as $val )
        {
            $optionsArr[$val] = $val . " " . $this->service->text("minutes_label");
        }

        $cachePages->setOptions($optionsArr);
        $cachePages->setHasInvitation(false);
        $cachePages->setLabel($this->service->text("cache_pages_for_guests_label"));
        $cachePages->setDescription($this->service->text("cache_pages_for_guests_desc"));
        $cachePages->setValue($this->service->getConfig(Service::CNFG_CACHE_PAGES_FOR_GUESTS));
        $form->addElement($cachePages);

        $activeStorages = array();
        $storages = $this->service->getStorages();

        foreach ( $storages as $key => $val )
        {
            if ( call_user_func(array($val["class"], "checkAvailability")) && call_user_func(array($val["class"], "checkIfConfigured")) )
            {
                $activeStorages[$key] = $val["label"];
            }
        }

        $this->assign("storageAvail", !empty($activeStorages));

        $queryCache = new Selectbox("cache_storage");
        $queryCache->addOptions($activeStorages);
        $queryCache->setLabel($this->service->text("cache_storage_label"));
        $queryCache->setDescription($this->service->text("cache_storage_desc"));
        $queryCache->setValue($this->service->getConfig(Service::CNFG_CACHE_STORAGE));
        $form->addElement($queryCache);

        $tableOptimize = new Selectbox("optimize");
        $tableOptimize->setHasInvitation(false);
        $tableOptimize->setOptions(
            array(
                0 => $this->service->text("never"),
                3600 * 24 => $this->service->text("daily"),
                3600 * 24 * 7 => $this->service->text("weekly"),
                3600 * 24 * 30 => $this->service->text("monthly")
            )
        );
        $tableOptimize->setLabel($this->service->text("optimize_label"));
        $tableOptimize->setDescription($this->service->text("optimize_desc"));
        $tableOptimize->setValue((int) $this->service->getConfig(Service::CNFG_T_OPTIMIZE_PERIOD));
        $form->addElement($tableOptimize);

        $submit = new Submit("submit");
        $submit->setValue(\OW::getLanguage()->text("admin", "save_btn_label"));
        $form->addElement($submit);

        if ( \OW::getRequest()->isPost() && $form->isValid($_POST) && !empty($_POST["form_name"]) && $_POST["form_name"] == "config_form" )
        {
            $data = $form->getValues();

            $this->service->saveConfig(Service::CNFG_COMPRESS_CSS, !empty($data["compress_css"]));
            $this->service->saveConfig(Service::CNFG_COMPRESS_JS, !empty($data["compress_js"]));
            $this->service->saveConfig(Service::CNFG_ENCODE_CSS_IMAGE, !empty($data["encode_css_image"]));
            $this->service->saveConfig(Service::CNFG_COMPRESS_HTML, !empty($data["compress_html"]));
            
            $dropStorageCache = false;

            if ( (int) $this->service->getConfig(Service::CNFG_CACHE_PAGES_FOR_GUESTS) != (int) $data["cache_pages_for_guests"] )
            {
                $this->service->saveConfig(Service::CNFG_CACHE_PAGES_FOR_GUESTS, (int) $data["cache_pages_for_guests"]);
                $dropStorageCache = true;
            }

            if ( (int) $this->service->getConfig(Service::CNFG_CACHE_DB_QUERIES) != (int) $data["cache_db_queries"] )
            {
                $this->service->saveConfig(Service::CNFG_CACHE_DB_QUERIES, (int) $data["cache_db_queries"]);
                $dropStorageCache = true;
            }

            $pStorage = trim($data["cache_storage"]);

            if ( array_key_exists($pStorage, $storages) && $pStorage != $this->service->getConfig(Service::CNFG_CACHE_STORAGE) )
            {
                $this->service->saveConfig(Service::CNFG_CACHE_STORAGE, $pStorage);
                $dropStorageCache = true;
            }

            if ( $dropStorageCache )
            {
                $this->service->dropAllStoragesCache();
            }

            $this->service->saveConfig(Service::CNFG_T_OPTIMIZE_PERIOD, (int) $data["optimize"]);
            \OW::getFeedback()->info($this->service->text("settings_saved"));
            $this->redirect();
        }

        $this->addForm($form);
    }

    public function cacheControl()
    {
        if ( \OW::getRequest()->isPost() && (!empty($_POST["mode"]) || !empty($_POST["oacompress"]) ) )
        {
            if ( !empty($_POST["oacompress"]) )
            {
                $this->service->markAllExpired();
            }

            if ( in_array(1 << 4, $_POST["mode"]) )
            {
                $storages = $this->service->getStorages();

                foreach ( $storages as $storage )
                {
                    if ( call_user_func(array($storage["class"], "checkAvailability")) && call_user_func(array($storage["class"],
                            "checkIfConfigured")) )
                    {
                        $stObject = new $storage["class"]();
                        $stObject->clean(array(), \OW_CacheManager::CLEAN_ALL);
                    }
                }
            }

            if ( !empty($_POST["mode"]) )
            {
                $devVal = 0;

                foreach ( $_POST["mode"] as $item )
                {
                    $devVal |= (int) $item;
                }

                \OW::getConfig()->saveConfig("base", "dev_mode", $devVal);
            }

            \OW::getFeedback()->info($this->service->text("cache_clear_msg"));
            $this->redirect(\OW::getRouter()->urlForRoute("oacompress.admin_cache_control"));
        }

        $confirmMessage = $this->service->text("cache_confirm_message");

        $checkboxData = array(
            "oa_compress" => 1,
            "oa_template" => 1 << 1,
            "oa_theme" => 1 << 2,
            "oa_dbcache" => 1 << 4,
            "oa_plugin" => 1 << 5,
            "oa_lang" => 1 << 3
        );

        $this->assign("checkboxData", $checkboxData);
        \OW::getDocument()->addOnloadScript(
            "$('#oa_all').click(function(){ $('table.\OACOMPRESS_form input[type=checkbox]').prop('checked', this.checked); });"
        );
    }

    public function storages()
    {
        $storageClasses = $this->service->getStorages();
        $arrToAssign = array();
        $activeStorage = $this->service->getConfig(Service::CNFG_CACHE_STORAGE);

        foreach ( $storageClasses as $key => $storage )
        {
            $arrToAssign[$key] = array("label" => $storage["label"], "status" => "avail");

            if ( !call_user_func(array($storage["class"], "checkAvailability")) )
            {
                $arrToAssign[$key]["status"] = "na";
                $arrToAssign[$key]["requirements"] = call_user_func(array($storage["class"], "getRequirements"));
                $arrToAssign[$key]["id"] = $key;
                \OW::getDocument()->addOnloadScript("$('#" . $key . "_rq').click(function(){ "
                    . "new OW_FloatBox({\$title:'" . $this->service->text("{$key}_rq_floatbox_cap_label") . "', \$contents: $('#{$key}-rq-content'), width: '550px'}); });");
            }
            else if ( !call_user_func(array($storage["class"], "checkIfConfigured")) )
            {
                $arrToAssign[$key]["status"] = "nc";
                $arrToAssign[$key]["id"] = $key;
            }
            else
            {
                $data = call_user_func(array($storage["class"], "getStats"));

                $arrToAssign[$key] = array_merge($arrToAssign[$key], $data);
            }

            if ( $activeStorage == $key )
            {
                $arrToAssign[$key]["status"] = "active";
            }

            if ( $key == "sql" )
            {
                $arrToAssign[$key]["speed"] = 2;
            }
            else
            {
                $arrToAssign[$key]["speed"] = 1;
            }
        }


        // memcached form
        $this->addForm($this->getMemcachedForm());
        $floatboxCapLabel = $this->service->text("mcd_floatbox_cap_label");
        \OW::getDocument()->addOnloadScript("$('#mcd').click(function(){ window.mcdFB = new OW_FloatBox({\$title:'" . $floatboxCapLabel . "', \$contents: $('#mcd-content'), width: '550px'}); });");


        $this->assign("storages", $arrToAssign);
    }

    public function checkMemcached()
    {
        $form = $this->getMemcachedForm();

        if ( \OW::getRequest()->isAjax() && $_POST["form_name"] == "memcached" && $form->isValid($_POST) )
        {
            $data = $form->getValues();

            $storages = $this->service->getStorages();
            $mcdClass = $storages[\oacompress\classes\MemcachedCacheBackend::getNamespace()]["class"];

            if ( !call_user_func(array($mcdClass, "checkAvailability")) )
            {
                exit(json_encode(array("result" => false, "message" => $this->service->text("mcd_err_msg_extension_not_available"))));
            }

            if ( !call_user_func(array($mcdClass, "checkIfConfigured"), $data) )
            {
                exit(json_encode(array("result" => false, "message" => $this->service->text("mcd_err_msg_cant_connect_to_server"))));
            }

            $this->service->saveConfig(Service::CNFG_MEMCACHED_ATTRS, json_encode($data));

            exit(json_encode(array("result" => true, "message" => $this->service->text("form_submit_success_msg"))));
        }
        else
        {
            exit(json_encode(array("result" => false, "message" => "Data submit error!")));
        }
    }

    private function getMemcachedForm()
    {
        $attrs = $this->service->getConfig(Service::CNFG_MEMCACHED_ATTRS);

        $attrArr = json_decode($attrs, true);

        $host = empty($attrArr["host"]) ? "" : $attrArr["host"];
        $port = empty($attrArr["port"]) ? "" : $attrArr["port"];

        $form = new Form("memcached");
        $element = new TextField("host");
        $element->setRequired();
        $element->setValue($host);
        $element->setLabel($this->service->text("label_host"));
        $form->addElement($element);

        $element = new TextField("port");
        $element->setRequired();
        $element->setValue($port);
        $element->setLabel($this->service->text("label_port"));
        $form->addElement($element);

        $submit = new Submit("submit");
        $submit->setValue(\OW::getLanguage()->text("admin", "save_btn_label"));
        $form->addElement($submit);
        $form->setAjax(true);
        $form->setAction(\OW::getRouter()->urlFor(__CLASS__, "checkMemcached"));
        $form->bindJsFunction(Form::BIND_SUCCESS, "function(data){if( !data.result ){ OW.error(data.message); }else{ OW.info(data.message);location.reload(); } }");
        $form->setAjaxResetOnSuccess(false);

        return $form;
    }
}
