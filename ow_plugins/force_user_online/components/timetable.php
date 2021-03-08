<?php

/**
 * Timetable component
 *
 * @author Pryadkin Sergey <GiperProger@gmail.com>
 * @package ow.ow_plugins.force_user_online.components
 * @since 1.8.4
 */
class FORCE_CMP_Timetable extends OW_Component
{
    private $service;
    private $pluginKey;
    private $config;
    private $lang;

    public function __construct()
    {
        parent::__construct();
        $this->service = FORCE_BOL_Service::getInstance();
        $this->pluginKey = $this->service->getPluginKey();
        $this->lang = OW::getLanguage();
        $this->config = OW::getConfig();
        $this->process();
    }


    protected function process()
    {
        $actionsList = $this->service->getAllActions();
        $this->assign('actionsList', $actionsList);

    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        $this->process();
    }
}