<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ucarousel.components
 * @since 1.0
 */
class UCAROUSEL_CMP_Users extends OW_Component
{
    private $uniqId;
    private $userCount = 0;

    public function __construct( $idList, $size, $layout )
    {
        parent::__construct();

        $this->userCount = count($idList);
        
        $questionService = BOL_QuestionService::getInstance();
        $userService = BOL_UserService::getInstance();

        $this->uniqId = uniqid('ucl_');

        $qList = $questionService->getQuestionData($idList, array(
            'sex', 'birthdate'
        ));

        $onlineStatuses = $userService->findOnlineStatusForUserList($idList);
        $displayNames = $userService->getDisplayNamesForList($idList);
        $urls = $userService->getUserUrlsForList($idList);

        $tplData = array();

        $js = UTIL_JsGenerator::newInstance();
        $js->addScript("window.UCAROUSEL_PhotoData = {};");
        foreach ( $idList as $userId )
        {
            $tplData[$userId] = array();
            $tplData[$userId]['userId'] = $userId;
            $tplData[$userId]['displayName'] = empty($displayNames[$userId]) ? null : $displayNames[$userId];
            $tplData[$userId]["online"] = !empty($onlineStatuses[$userId]);

            $tplData[$userId]['url'] = empty($urls[$userId]) ? null : $urls[$userId];
            $tplData[$userId]['sex'] = empty($qList[$userId]['sex']) || in_array($layout, array(3, 4))
                ? null
                : strtolower(BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $qList[$userId]['sex']));

            $tplData[$userId]['birthdate'] = null;

            if ( !empty($qList[$userId]['birthdate']) && in_array($layout, array(1, 3)) )
            {
                $date = UTIL_DateTime::parseDate($qList[$userId]['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);
                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
                $tplData[$userId]['birthdate'] = $age;
            }


            $avatar = BOL_AvatarService::getInstance()->getAvatarUrl($userId, 2);
            $tplData[$userId]['thumb'] =  $avatar ? $avatar : BOL_AvatarService::getInstance()->getDefaultAvatarUrl(2);
            
            $uAvatar = OW::getEventManager()->trigger(new OW_Event("uavatars.get_avatar", array(
                "userId" => $userId
            )))->getData();
            
            if ( !empty($uAvatar) && !empty($uAvatar["photo"]) )
            {
                $js->addScript('window.UCAROUSEL_PhotoData[{$pid}] = {}; window.UCAROUSEL_PhotoData[{$pid}].data={$data}; window.UCAROUSEL_PhotoData[{$pid}].image = new Image(); window.UCAROUSEL_PhotoData[{$pid}].image.src={$url};', array(
                    "data" => $uAvatar["photo"]["data"],
                    "url" => $uAvatar["photo"]["url"],
                    "pid" => $uAvatar["photoId"]
                ));
            }
            
            $tplData[$userId]["uavatar"] = $uAvatar;

            $tplData[$userId]["alt"] = OW::getLanguage()->text("ucarousel", "image_alt", array(
                "displayName" => $tplData[$userId]["displayName"],
                "gender" => $tplData[$userId]["sex"],
                "age" => $tplData[$userId]["birthdate"]
            ));
        }
        
        OW::getDocument()->addScriptDeclaration($js);

        $sizes = array(
            'small' => 100,
            'medium' => 150,
            'big' => OW::getConfig()->getValue('base', 'avatar_big_size')
        );

        $this->assign('list', $tplData);
        $avatarSize = $sizes[$size];

        $this->assign('size', $size);
        $this->assign('uniqId', $this->uniqId);

        OW::getDocument()->addStyleDeclaration('.uc-avatar-size { width: ' . $avatarSize . 'px; height: ' . $avatarSize . 'px; }');
        OW::getDocument()->addStyleDeclaration('.uc-carousel-size { height: ' . ($avatarSize + 50) . 'px; }');

        OW::getDocument()->addStyleDeclaration('.uc-shape-waterWheel .uc-carousel { width: ' . ($avatarSize + 20) . 'px; }');
    }

    public function initCarousel( $options )
    {
        $static = OW::getPluginManager()->getPlugin('ucarousel')->getStaticUrl();
        $plugin = OW::getPluginManager()->getPlugin('ucarousel');

        if ( !empty($options['dragging']) )
        {
            OW::getDocument()->addScript($static . 'jquery.event.drag.js');
            OW::getDocument()->addScript($static . 'jquery.event.drop.js');
        }

        OW::getDocument()->addScript($static . 'jquery.roundabout.min.js');
        OW::getDocument()->addScript($static . 'jquery.roundabout-shapes.min.js');
        OW::getDocument()->addStyleSheet($static . 'styles.css?' . $plugin->getDto()->build);

        $js = UTIL_JsGenerator::newInstance();

        $extraOptions = array(
            'lazySusan' => array(),
            'waterWheel' => array( 'dragAxis' => 'y' ),
            'figure8' => array(),
            'square' => array( 'minOpacity' => 0.6 ),
            'conveyorBeltLeft' => array( 'minOpacity' => 1.0 ),
            'conveyorBeltRight' => array( 'minOpacity' => 1.0 ),
            'diagonalRingLeft' => array(),
            'diagonalRingRight' => array(),
            'rollerCoaster' => array( 'minOpacity' => 0.6 ),
            'tearDrop' => array()
        );

        $params = array(
            'minZ' => 2,
            'maxZ' => $this->userCount + 2,
            'shape' => $options['shape'],
            'enableDrag' => !empty($options['dragging'])
        );

        $params = array_merge($params, $extraOptions[$options['shape']]);

        $this->assign('shape', $options['shape']);

        if ( !empty($options['autoplay']) )
        {
            $params['autoplay'] = true;
            $params['autoplayDuration'] = $options['speed'];
            $params['autoplayPauseOnHover'] = true;
        }

        $js->addScript('$("#' . $this->uniqId . '").css("visibility", "visible").roundabout({$params});', array(
            'params' => $params
        ));

        if ( OW::getPluginManager()->isPluginActive("uavatars") )
        {
            OW::getEventManager()->trigger(new OW_Event("uavatars.add_static"));
            
            $js->addScript('window.UCAROUSEL_PhotoData = window.UCAROUSEL_PhotoData || {}; $(".uc-carousel-wrap").on("click", ".roundabout-in-focus[data-pid]", function(e) { '
                    . 'if ( !$(e.target).is(".uc-info *") ) { '
                    . 'var photoId = $(this).data("pid");'
                    . 'var photoData = window.UCAROUSEL_PhotoData[photoId];'
                    . 'UAVATARS.setPhoto(photoId, photoData.data, photoData.image);'
                    . ' }});');
        }
        
        OW::getDocument()->addOnloadScript($js);
    }
}