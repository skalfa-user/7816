<?php

/**
 * Copyright (c) 2017,  Sergey Pryadkin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * Force User Online admin controller
 *
 * @author Pryadkin Sergey <GiperProger@gmail.com>
 * @package ow.ow_plugins.force_online.controllers
 * @since 1.8.4
 */
class FORCE_CTRL_Admin extends ADMIN_CTRL_Abstract
{
     /**
     * @var FORCE_BOL_Service
     */

    protected $service;
    protected $language;
    protected $pluginKey;
    
    public function __construct()
    {
        parent::__construct();

        $this->service = FORCE_BOL_Service::getInstance();
        $this->language = OW::getLanguage();
        $this->pluginKey = $this->service->getPluginKey();

    } 

    public function settings()
    {
        $form = new FORCE_CLASS_SettingsForm();
        $this->addForm($form);

        if( isset($_GET['fixForShaun']) )
        {
            $this->service->deleteOldOnlineUsers();
        }

        if( OW::getRequest()->isPost() &&  $form->isValid($_POST) )
        {
            $values = $form->getValues();
            $task = new FORCE_BOL_Task();

            $amountOfUsers = $values['amountOnlineUsers_txt'];
            $task->amount_of_users = $amountOfUsers;
            $task->command = isset($_POST['addToOnline_btn']) ? 'adding':  'deleting';
            $task->total_amount = $amountOfUsers;
            $this->service->makeOnline();


            if( isset($_POST['addToOnline_btn']) )
            {
                $task->command = 'adding';
            }
            else
            {
                $task->command = 'deleting';
            }

            $this->service->setTask($task);

            OW::getFeedback()->info($this->language->text($this->pluginKey, 'task_successfully_created'));

            $this->redirect();

        }

        $this->setPageHeading(OW::getLanguage()->text($this->pluginKey, 'config_page_heading'));

        $js =
            "
                $('#amountOnlineUsers_div').find('*').prop('disabled',true);
                $('#action_buttons_div').find('*').prop('disabled',true);

            ";

        OW::getDocument()->addOnloadScript($js);

        $jsParams = array(

        );

        $script = ' var ping = new ForceUsers_Ping(); ping.init('.  json_encode($jsParams).');';

        OW::getDocument()->addOnloadScript($script);
        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin($this->pluginKey)->getStaticJsUrl(). 'ping.js');

        $this->addComponent('menu', $this->getMenu());

    }


    public function settingsAuto()
    {
        $autoSettingsForm = new FORCE_CLASS_AutoSettingsForm();
        $this->addForm($autoSettingsForm);
        $timeTableComponent = new FORCE_CMP_Timetable();

        if( OW::getRequest()->isPost() && $autoSettingsForm->isValid($_POST))
        {
            $values = $autoSettingsForm->getValues();
            $action = new FORCE_BOL_Actions();
            $action->hours = $values['hours_sb'];
            $action->minutes = $values['minutes_sb'];
            $action->amount = $values['amountOnlineUsers_txt'];
            $action->action = $values['actions_sb'] == 1 ? 'add' : 'delete';
            $this->service->addAction($action);
        }

        $this->addComponent('menu', $this->getMenu());
        $this->addComponent('timeTableComponent', $timeTableComponent);

    }

    private function getMenu()
    {
        $language = OW::getLanguage();
        $menuItems = array();


        $item = new BASE_MenuItem();
        $item->setLabel($language->text($this->pluginKey, 'manual_manage'));
        $item->setUrl(OW::getRouter()->urlForRoute('force_admin_settings'));
        $item->setKey('manual_manage');
        $item->setIconClass('ow_ic_friends');
        $item->setOrder(0);

        array_push($menuItems, $item);

        $item = new BASE_MenuItem();
        $item->setLabel($language->text($this->pluginKey, 'auto_manage'));
        $item->setUrl(OW::getRouter()->urlForRoute('force_admin_settings_auto') );
        $item->setKey('auto_manage');
        $item->setIconClass('ow_ic_chat');
        $item->setOrder(1);

        array_push($menuItems, $item);

        $item = new BASE_MenuItem();
        $item->setLabel($language->text($this->pluginKey, 'help'));
        $item->setUrl(OW::getRouter()->urlForRoute('force_admin_settings_help') );
        $item->setKey('help');
        $item->setIconClass('ow_ic_help');
        $item->setOrder(2);

        array_push($menuItems, $item);

        $menu = new BASE_CMP_ContentMenu($menuItems);

        return $menu;
    }

    public function deleteAction()
    {
        $actionId = $_GET['actionId'];
        $this->service->deleteAction($actionId);

        $this->redirect($_SERVER['HTTP_REFERER']);
    }

    public function settingsHelp()
    {
        $this->addComponent('menu', $this->getMenu());
    }
    
}



