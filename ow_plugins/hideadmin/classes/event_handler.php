<?php
/**
* Copyright (c) 2015, Pryadkin Sergey <GiperProger@gmail.com>
* All rights reserved.

* ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
* and is licensed under Oxwall Store Commercial License.
* Full text of this license can be found at http://www.oxwall.org/store/oscl
*/

/**
* @author Pryadkin Sergey <GiperProger@gmail.com>
*/

class HIDEADMIN_CLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var HIDEADMIN_CLASS_EventHandler
     */
    private static $classInstance;


    /**
     * @var HIDEADMIN_BOL_Service
     */
    protected $service;

    /**
     * Filtered methods
     *
     * @var array
     */
    protected $filteredMethods = [
        'MATCHMAKING_BOL_QuestionMatchDao::prepareQuerySelectCount',
        'MATCHMAKING_BOL_QuestionMatchDao::prepareQuerySelect',
        'BOL_UserDao::findUserIdListByQuestionValues'
    ];
    

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return HIDEADMIN_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->service = HIDEADMIN_BOL_Service::getInstance();
    }

    public function init()
    {
        $this->genericInit();
    }

    public function genericInit()
    {
        $em = OW::getEventManager();
        $em->bind(OW_EventManager::ON_AFTER_ROUTE, array($this, "onAfterRout"));
        $em->bind('base.query.user_filter', array($this, 'userFilter'));
        $em->bind('mailbox.before_send_message', array($this, 'onBeforeSendMessage'));
        $em->bind('mailbox.before_create_conversation', array($this, 'onBeforeSendMessage'));
    }

    public function onBeforeSendMessage( OW_Event $event )
    {
        $params = $event->getParams();
        $adminIds = $this->service->getAdminsIds();

        $profileUserId = $params['recipientId'];


        if( in_array($profileUserId, $adminIds) )
        {
            $message = OW::getLanguage()->text('hideadmin', 'can_not_send_message_feedback');
            $event->setData( array('result' => false, 'error' => $message ));
        }

    }

    public function onAfterRout()
    {
        $handlerAtr = OW::getRequestHandler()->getHandlerAttributes();

        if( ($handlerAtr['controller'] == 'BASE_CTRL_ComponentPanel' ||  $handlerAtr['controller'] == 'BASE_MCTRL_User') && $handlerAtr['action'] == 'profile' )
        {
            $profileUserName = empty($handlerAtr['params']['username']) ? false : $handlerAtr['params']['username'];
            $profileUserId = BOL_UserService::getInstance()->findByUsername($profileUserName)->id;
            $currentUserId = Ow::getUser()->getId();

            if( OW::getAuthorization()->isUserAuthorized($profileUserId, 'admin') && !OW::getAuthorization()->isUserAuthorized($currentUserId, 'admin') )
            {
                OW::getApplication()->redirect((OW::getRouter()->urlForRoute('upgrade-to-view', array('type' => $_POST['type']))));
            }

        }
    }



    /**
     * Matchmaking filter
     *
     * @param BASE_CLASS_QueryBuilderEvent $event
     */
    public function userFilter( BASE_CLASS_QueryBuilderEvent $event )
    {
        $params = $event->getParams();

        if (in_array($params['method'], $this->filteredMethods))
        {
            $userIds = HIDEADMIN_BOL_Service::getInstance()->getAdminsIds();

            if ( $userIds )
            {
                $event->addWhere('`base_user_table_alias`.`id` NOT IN(' . implode(',', $userIds) . ') ');
            }
        }
    }

}