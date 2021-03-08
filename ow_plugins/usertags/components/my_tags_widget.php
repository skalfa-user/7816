<?php
class USERTAGS_CMP_MyTagsWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $service = USERTAGS_BOL_Service::getInstance();
        $user = BOL_UserService::getInstance()->findUserById($params->additionalParamList['entityId']);

        if( $user === null )
        {
            $this->setVisible(false);
            return;
        }

        $eventParams =  array(
                'action' => 'usertags_view_my_tags',
                'ownerId' => $user->getId(),
                'viewerId' => OW::getUser()->getId()
            );
        
        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch( RedirectException $e )
        {

            $this->setVisible(false);
            return;
        }
        $modPermissions = OW::getUser()->isAuthorized('usertags');
        $this->assign('moderatorMode', $modPermissions);
	$canView = BOL_AuthorizationService::getInstance()->isActionAuthorizedForUser($user->getId(), 'usertags', 'add_tags');

        if ( !$canView )
        {
            $this->setVisible(false);
            return;
        }

        if ($user->getId() == OW::getUser()->getId() &&
		OW::getUser()->isAuthorized('usertags', 'add_tags')) {
            $this->setSettingValue(
               self::SETTING_TOOLBAR, array(
               array(
                  'label' => OW::getLanguage()->text('usertags', 'edit'),
                  'href' => OW::getRouter()->urlForRoute('usertags-edit'))
               )
            );
        }

        $configs = OW::getConfig()->getValues('usertags');
	$tagsLimit = isset($configs['tags_in_cloud']) ? $configs['tags_in_cloud'] : 20;

        $tagList = BOL_TagService::getInstance()->findEntityTagsWithPopularity($user->getId(), 'usertags', (int)$tagsLimit);

        // get font sizes from configs
        $minFontSize = BOL_TagService::getInstance()->getConfig(BOL_TagService::CONFIG_MIN_FONT_SIZE);
        $maxFontSize = BOL_TagService::getInstance()->getConfig(BOL_TagService::CONFIG_MAX_FONT_SIZE);

        // get min and max tag's items count
        $minCount = null;
        $maxCount = null;

        foreach ( $tagList as $tag )
        {
            if ( $minCount === null )
            {
                $minCount = (int) $tag['count'];
                $maxCount = (int) $tag['count'];
            }

            if ( (int) $tag['count'] < $minCount )
            {
                $minCount = (int) $tag['count'];
            }

            if ( (int) $tag['count'] > $maxCount )
            {
                $maxCount = (int) $tag['count'];
            }
        }

        $tags = array();

        // prepare array to assign
        $list = empty($tagList) ? array() : $tagList;

        foreach ( $list as $key => $value )
        {
            if ( $value['label'] === null )
            {
                continue;
            }

            $tags[$key]['url'] = OW::getRouter()->urlForRoute('users-by-tags_list', array('tag' => $value['label'])) ;

            $fontSize = ($maxCount === $minCount ? ($maxFontSize / 2) : floor(((int) $value['count'] - $minCount) / ($maxCount - $minCount) * ($maxFontSize - $minFontSize) + $minFontSize));

            $tags[$key]['size'] = $fontSize;
            $tags[$key]['lineHeight'] = $fontSize + 4;
            $tags[$key]['label'] = $value['label'];
        }

        $this->assign('tags', $tags);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('usertags', 'tags'),
            self::SETTING_ICON => self::ICON_USER,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_FREEZE => true
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}