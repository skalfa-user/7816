<?php


class MEMBERX_MCLASS_EventHandler
{
    /**
     * @var MEMBERX_CLASS_EventHandler
     */
    private static $classInstance;

    const EVENT_COLLECT_USER_ACTIONS = 'memberx.collect_user_actions';

    /**
     * @return MEMBERX_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct() { 
        
    }
    
    public function init() {
        OW::getEventManager()->bind('class.get_instance', array($this, 'getClassInstance'));
    }
    
    public function getClassInstance(OW_Event $event) {
        $params = $event->getParams();
        
        $className = $params['className'];
        $arguments = $params['arguments'];
        
        if ( $className == 'MEMBERX_CMP_SearchResultList' )
        {
            $event->setData(new MEMBERX_MCMP_SearchResultList($arguments[0], $arguments[1], (!empty($arguments[2]) ? $arguments[2] : null), (!empty($arguments[3]) ? $arguments[3] : false)));
        }
    }
}