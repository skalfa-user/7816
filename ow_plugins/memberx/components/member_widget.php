<?php

/* 
 * Copyright 2015 Daniel Shum 
 * Contact: denny.shum@gmail.com
 * 
 * Licensed under the OSCL (the License); you may not 
 * use this file except in compliance with the License.
 * 
 * You may obtain a copy of the License at 
 * 
 * 	https://developers.oxwall.com/store/oscl
 * 
 * 
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 */



class MEMBERX_CMP_MemberWidget extends OW_Component{
    
    public $listType = 'latest';
    public $numberOfProfile;
    public $showButtons;
    public $numberOfItems = 0;
    
    public function __construct($lstType = 'activity') {
        parent::__construct();
        
        $this->listType = $lstType;
        $this->numberOfProfile = MEMBERX_CMP_SearchResultSetting::getInt(MEMBERX_CMP_SearchResultSetting::NUMBER_OF_AVATARS_ON_INDEX_WIDGET);
        $this->showButtons = MEMBERX_CMP_SearchResultSetting::getBoolean(MEMBERX_CMP_SearchResultSetting::SHOW_BUTTONS_ON_WIDGET);
        
        $idList = array();
        $userDtoList = array();
        
        switch($lstType){
            case 'activity':
                $data = array();
                $data['with_photo'] = 1;
                $idList =  $this->getLatestActivityList($data);
                break;
            case 'newest':
                $data = array();
                $data['with_photo'] = 1;
                $idList =  $this->getLatestActivityList($data, '`user`.`id` DESC');
                break;
            case 'distance':
                $data = array();
                $data['with_photo'] = 1;
                $userDtoList =  $this->getDistanceList($data);
                break;
                
                
        }
        
        
        if (empty($idList) && empty($userDtoList)){
            return;
        }

        if (empty($userDtoList)){
            $userDtoList = BOL_UserService::getInstance()->findUserListByIdList($idList);
            $this->numberOfItems = count($idList);
        }else{
            $this->numberOfItems = count($userDtoList);
        }
        
        
        $cmp = new MEMBERX_CMP_SearchResultList($userDtoList, 1, null, false, null, MEMBERX_CMP_SearchResultSetting::SEARCH_RESULT_LAYOUT_UD, $this->showButtons);
        $url = OW::getRouter()->urlForRoute('memberx.members', array('orderType' => 'latest_activity'));
        $avatarSize = MEMBERX_CMP_SearchResultSetting::getInt(MEMBERX_CMP_SearchResultSetting::AVATAR_SIZE_ON_INDEX_WIDGET);
        
        $this->addComponent('cmp', $cmp);
        $this->assign('url', $url);
        
        if ($avatarSize && isset($cmp->assignedVars['config'])){
            $cmp->assignedVars['config'][MEMBERX_CMP_SearchResultSetting::AVATAR_SIZE] = $avatarSize;
        }
        
        $jsParams = array(
            'excludeList' => array(),
            'respunderUrl' => OW::getRouter()->urlForRoute('memberx.load_list_action'),
            'orderType' => 0,
            'page' => 1,
            'listId' => 0,
            'count' => 0,
            'userId' => OW::getUser()->getId()
            
        );
        
        $jsParams['noScroll'] = true;
        
        $script = ' MEMBERX_ResultList.init('.  json_encode($jsParams).', $(".ow_search_results_photo_gallery_container")); ';
        OW::getDocument()->addOnloadScript($script);
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('memberx')->getStaticJsUrl().'result_list.js');
        
    }
    
    public function getLatestActivityList($data, $order = null){
        
        if (!$this->numberOfProfile){
            return array();
        }
        
        $addParams = array('join' => '', 'where' => '', 'order' => '`user`.`activityStamp` DESC');

        if (!empty($data['online'])) {
            $addParams['join'] .= " INNER JOIN `" . BOL_UserOnlineDao::getInstance()->getTableName() . "` `online` ON (`online`.`userId` = `user`.`id`) ";
        }

        if (!empty($data['with_photo'])) {
            $addParams['join'] .= " INNER JOIN `" . OW_DB_PREFIX . "base_avatar` avatar ON (`avatar`.`userId` = `user`.`id`) ";
        }

        if (!empty($order)){
            $addParams['order'] = $order;
        }

        $searchSettings = MEMBERX_CMP_SearchResultSetting::getSavedConfig();
        $restrictToOtherAccountType = isset($searchSettings[MEMBERX_CMP_SearchResultSetting::ACCOUNT_TYPE_RESTRICT]) ?
                $searchSettings[MEMBERX_CMP_SearchResultSetting::ACCOUNT_TYPE_RESTRICT] : 'no';

        if ($restrictToOtherAccountType === 'yes' && OW::getUser()->isAuthenticated() && !isset($data['accountType'])) {

            $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();
            if (count($accountTypes) > 1) {
                $myAccountType = OW::getUser()->getUserObject()->accountType;

                foreach ($accountTypes as $accountType) {
                    if ($accountType->name != $myAccountType) {
                        $data['accountType'] = $accountType->name;
                        break;
                    }
                }
            }
        }

        $userIdList = MEMBERX_BOL_Service::getInstance()->findUserIdListByQuestionValues($data, 0, $this->numberOfProfile, false, $addParams);

        if (OW::getUser()->isAuthenticated()) {
            foreach ($userIdList as $key => $id) {
                if (OW::getUser()->getId() == $id) {
                    unset($userIdList[$key]);
                }
            }
        }

        return $userIdList;
    }
    
    public function getDistanceList($data){
        
        if (!$this->numberOfProfile){
            return array();
        }
        
        if (!OW::getUser()->isAuthenticated() || !OW::getPluginManager()->isPluginActive('googlelocation')){
            return array();
        }
       



        $addParams = array('join' => '', 'where' => '', 'order' => '`user`.`activityStamp` DESC');

        if (!empty($data['online'])) {
            $addParams['join'] .= " INNER JOIN `" . BOL_UserOnlineDao::getInstance()->getTableName() . "` `online` ON (`online`.`userId` = `user`.`id`) ";
        }

        if (!empty($data['with_photo'])) {
            $addParams['join'] .= " INNER JOIN `" . OW_DB_PREFIX . "base_avatar` avatar ON (`avatar`.`userId` = `user`.`id`) ";
        }


        $searchSettings = MEMBERX_CMP_SearchResultSetting::getSavedConfig();
        $restrictToOtherAccountType = isset($searchSettings[MEMBERX_CMP_SearchResultSetting::ACCOUNT_TYPE_RESTRICT]) ?
                $searchSettings[MEMBERX_CMP_SearchResultSetting::ACCOUNT_TYPE_RESTRICT] : 'no';

        if ($restrictToOtherAccountType === 'yes' && OW::getUser()->isAuthenticated() && !isset($data['accountType'])) {

            $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();
            if (count($accountTypes) > 1) {
                $myAccountType = OW::getUser()->getUserObject()->accountType;

                foreach ($accountTypes as $accountType) {
                    if ($accountType->name != $myAccountType) {
                        $data['accountType'] = $accountType->name;
                        break;
                    }
                }
            }
        }

        $userIdList = MEMBERX_BOL_Service::getInstance()->findUserIdListByQuestionValues($data, 0, 500, false, $addParams);
        
        $result = BOL_QuestionService::getInstance()->getQuestionData(array(OW::getUser()->getId()), array('googlemap_location'));

        if (!empty($result[OW::getUser()->getId()]['googlemap_location']['json'])) {
            $location = $result[OW::getUser()->getId()]['googlemap_location'];

            $userDtoList = GOOGLELOCATION_BOL_LocationService::getInstance()->getListOrderedByDistance($userIdList, 0, $this->numberOfProfile, $location['latitude'], $location['longitude']);
        }else{
            $userDtoList = array();
        }
        
        
        

        foreach ($userDtoList as $key => $userDto) {

            if (OW::getUser()->isAuthenticated() && OW::getUser()->getId() == $userDto->id) {
                unset($userDtoList[$key]);
            }

        }

        return $userDtoList;
    }
    
    
}