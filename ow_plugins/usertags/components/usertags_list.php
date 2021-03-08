<?php
class USERTAGS_CMP_UserList extends OW_Component
{
    /**
     * @var PHOTO_BOL_PhotoService 
     */
    private $photoService;

    /**
     * Class constructor
     *
     * @param string $listType
     * @param int $count
     * @param string $tag
     */
    public function __construct( array $params )
    {
        parent::__construct();

        $listType = $params['type'];
        $count = isset($params['count']) ? $params['count'] : 5;

        $this->photoService = PHOTO_BOL_PhotoService::getInstance();

        $page = !empty($_GET['page']) && (int) $_GET['page'] ? abs((int) $_GET['page']) : 1;

        $config = OW::getConfig();

        $photosPerPage = $config->getValue('photo', 'photos_per_page');

        if ( isset($params['tag']) && strlen($tag = $params['tag']) )
        {
            $photos = $this->photoService->findTaggedPhotos($tag, $page, $photosPerPage);
            $records = $this->photoService->countTaggedPhotos($tag);
        }
        else
        {
            $checkPrivacy = $listType == 'latest' && !OW::getUser()->isAuthorized('photo');
            $photos = $this->photoService->findPhotoList($listType, $page, $photosPerPage, $checkPrivacy);
            $records = $this->photoService->countPhotos($listType, $checkPrivacy);
        }

        if ( $photos )
        {
            $userIds = array();
            foreach ( $photos as $photo )
            {
                if ( !in_array($photo['userId'], $userIds) )
                    array_push($userIds, $photo['userId']);
            }

            $names = BOL_UserService::getInstance()->getDisplayNamesForList($userIds);
            $this->assign('names', $names);
            $usernames = BOL_UserService::getInstance()->getUserNamesForList($userIds);
            $this->assign('usernames', $usernames);

            // Paging
            $pages = (int) ceil($records / $photosPerPage);
            $paging = new BASE_CMP_Paging($page, $pages, 10);
            $this->addComponent('paging', $paging);

            $this->assign('photos', $photos);
            $this->assign('no_content', false);
        }
        else
        {
            $this->assign('no_content', true);
        }

        $this->assign('listType', $listType);

        $this->assign('widthConfig', $config->getValue('photo', 'preview_image_width'));
        $this->assign('heightConfig', $config->getValue('photo', 'preview_image_height'));

        $this->assign('count', $count);
        
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl().'jquery.bbq.min.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('photo')->getStaticJsUrl() . 'photo.js');

        OW::getLanguage()->addKeyForJs('photo', 'tb_edit_photo');
        OW::getLanguage()->addKeyForJs('photo', 'confirm_delete');
        OW::getLanguage()->addKeyForJs('photo', 'mark_featured');
        OW::getLanguage()->addKeyForJs('photo', 'remove_from_featured');
        OW::getLanguage()->addKeyForJs('photo', 'add_to_favorites');
        OW::getLanguage()->addKeyForJs('photo', 'remove_from_favorites');
        
        $objParams = array(
            'ajaxResponder' => OW::getRouter()->urlFor('PHOTO_CTRL_Photo', 'ajaxResponder'),
            'fbResponder' => OW::getRouter()->urlForRoute('photo.floatbox'),
            'listType' => $listType
        );

        $script = '$("div.ow_photo_list_item_thumb a").on("click", function(e){
            e.preventDefault();
            var photo_id = $(this).attr("rel");

            if ( !window.photoViewObj ) {
                window.photoViewObj = new photoView('.json_encode($objParams).');
            }
            
            window.photoViewObj.setId(photo_id);
        });
        
        $(window).bind( "hashchange", function(e) {
            var photo_id = $.bbq.getState("view-photo");
            if ( photo_id != undefined )
            {
                if ( window.photoFBLoading ) { return; }
                window.photoViewObj.showPhotoCmp(photo_id);
            }
        });';
        
        OW::getDocument()->addOnloadScript($script);
    }
}