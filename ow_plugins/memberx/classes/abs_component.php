<?php



abstract class MEMBERX_CLASS_AbsComponent extends OW_Component{
    
    const PLUGIN_KEY = 'memberx';
    const PLUGIN_KEY_CAP = 'MEMBERX';
    
    /**
     *
     * @var OW_Language 
     */
    protected $langs;
    
    /**
     *
     * @var OW_User 
     */
    protected $user;
    
    /**
     *
     * @var OW_PLUGIN
     */
    protected $plugin;
    
    /**
     *
     * @var VADMIN_BOL_SERVICE 
     */
    protected $service;
    
    /**
     *
     * @var BOL_AuthorizationService 
     */
    protected $authService;


    public function __construct() {
        parent::__construct();
        $this->langs = OW::getLanguage();
        $this->user = OW::getUser();
        $this->plugin = OW::getPluginManager()->getPlugin(self::PLUGIN_KEY);
        $this->authService =BOL_AuthorizationService::getInstance();
        $this->assign('isAuthToWrite', false);
    }
    
    
    protected function createPageCmp($total, $currentPage, $pageSize = 24){
        $pages = (int) ceil($total / $pageSize);
        $paging = new BASE_CMP_Paging($currentPage, $pages, 20);
        return $paging;
    }
    
    public function setWritePermission($permission){
        $this->assign('isAuthToWrite', $permission);
    }
}