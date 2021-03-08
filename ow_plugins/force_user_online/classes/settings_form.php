<?php
/**
 * Copyright (c) 2017,  Sergey Pryadkin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Sergey Pryadkin <GiperProger@gmail.com>
 * @package ow.ow_plugins.force_user_online.classes
 * @since 1.8.4
 */

class FORCE_CLASS_SettingsForm extends Form
{
    /**
     * 
     * @var TASU_BOL_Service
     */
    protected $service;
    protected $pluginKey;
    protected $lang;


    public function __construct()
    {
        parent::__construct('settings-form');

        $this->service = FORCE_BOL_Service::getInstance();
        $this->pluginKey = $this->service->getPluginKey();
        $this->lang = OW::getLanguage();

        $amountOnlineUsers_txt = new TextField('amountOnlineUsers_txt');
        $amountOnlineUsers_txt->setId('amountOnlineUsers_txt');
        $amountOnlineUsers_txt->setLabel(OW::getLanguage()->text($this->pluginKey, 'amountOnlineUsers_txt'));
        $amountOnlineUsers_txt->setRequired();
        $this->addElement($amountOnlineUsers_txt);

        $deleteFromOnline_btn = new Submit('deleteFromOnline_btn');
        $deleteFromOnline_btn->setValue($this->lang->text($this->pluginKey, 'deleteFromOnline_btn'));
        $deleteFromOnline_btn->setId('deleteFromOnline_btn');
        $this->addElement($deleteFromOnline_btn);

        $addToOnline_btn = new Submit('addToOnline_btn');
        $addToOnline_btn->setValue($this->lang->text($this->pluginKey, 'addToOnline_btn'));
        $addToOnline_btn->setId('addToOnline_btn');
        $this->addElement($addToOnline_btn);




    }
    

    
}