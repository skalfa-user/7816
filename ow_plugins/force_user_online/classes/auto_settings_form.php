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
 * @package ow.ow_plugins.force_user_online.bol
 * @since 1.8.4
 */

class FORCE_CLASS_AutoSettingsForm extends Form
{
    /**
     *
     * @var FORCE_BOL_Service
     */
    protected $service;
    protected $pluginKey;
    protected $lang;


    public function __construct()
    {
        parent::__construct('settings-form-auto');

        $this->service = FORCE_BOL_Service::getInstance();
        $this->pluginKey = $this->service->getPluginKey();
        $this->lang = OW::getLanguage();
        $this->generateControls();
    }

    protected function generateControls()
    {
        $amountOnlineUsers_txt = new TextField('amountOnlineUsers_txt');
        $amountOnlineUsers_txt->setId('amountOnlineUsers_txt');
        $amountOnlineUsers_txt->setLabel(OW::getLanguage()->text($this->pluginKey, 'amountOnlineUsers_txt'));
        $amountOnlineUsers_txt->setRequired();
        $this->addElement($amountOnlineUsers_txt);

        $hours_sb = new Selectbox('hours_sb');
        $hours_sb->setRequired();
        $hours_sb->setLabel($this->lang->text($this->pluginKey, 'hours_sb'));

        for( $i = 1; $i <= 12; $i++)
        {
            $hours_sb->addOption($i, $i);
        }

        $this->addElement($hours_sb);

        $minutes_sb = new Selectbox('minutes_sb');
        $minutes_sb->setRequired();
        $minutes_sb->setId('minutes_sb_id');
        $minutes_sb->setLabel($this->lang->text($this->pluginKey, 'minutes_sb'));

        for( $i = 1; $i <= 59; $i++)
        {
            $minutes_sb->addOption($i, $i);
        }

        $this->addElement($minutes_sb);

        $actions_sb = new Selectbox('actions_sb');
        $actions_sb->setRequired();
        $actions_sb->setId('actions_sb_id');
        $actions_sb->setLabel($this->lang->text($this->pluginKey, 'actions'));
        $actions_sb->addOption(1, 'Add');
        $actions_sb->addOption(2, 'Delete');

        $this->addElement($actions_sb);



        $addToOnline_btn = new Submit('createAction_btn');
        $addToOnline_btn->setValue($this->lang->text($this->pluginKey, 'createaction_btn'));
        $addToOnline_btn->setId('createAction_btn');
        $this->addElement($addToOnline_btn);
    }



}