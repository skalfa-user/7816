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

class MEMBERX_CMP_GroupSelector extends OW_Component{
    
    const INVITE_FORM_NAME = 'invite-to-group-form';
    const INVITE_FORM_USER_ID = 'invite-to-group-user-id';
    const INVITE_FORM_GROUP_ID = 'invite-to-group-group-id';
    const INVITE_FORM_PROCESS_URL = 'invite-to-group-process-url';
    
    public $userId;
    public $groupService;
    public function __construct($userIdToInvite) {
        parent::__construct();
        $this->userId = OW::getUser()->getId();
        $this->groupService = GROUPS_BOL_Service::getInstance();
        
        $userToInvite = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userIdToInvite));

        if (empty($userToInvite) || !isset($userToInvite[$userIdToInvite])){
            return;
        }
        
        $userToInvite = $userToInvite[$userIdToInvite];
        
        //$myEventList = MEMBERX_BOL_EventDao::getInstance()->findLatestUserCreatedEvents($this->userId, 0, 50);
        $myGroupList = MEMBERX_BOL_GroupDao::getInstance()->findInvitableGroups($this->userId, 0, 50);
        
        foreach($myGroupList as $group){
            $group->imageUrl = $this->groupService->getGroupImageUrl($group, GROUPS_BOL_Service::IMAGE_SIZE_SMALL);
            $group->viewUrl = $this->groupService->getGroupUrl($group);
            if (strlen($group->description) > 60){
                $group->bref = substr($group->description, 0, 60) . '...';
            }else{
                $group->bref = $group->description;
            }
        }
        
        $invitedEventListTemp = MEMBERX_BOL_GroupDao::getInstance()->findUserRecentlyInvitedGroups($userIdToInvite, 50);
        $joinedEventListTemp = MEMBERX_BOL_GroupDao::getInstance()->findUserRecentlyJoinedGroups($userIdToInvite, 50);

        $invitedEventList = array();
        foreach($invitedEventListTemp as $key => $invitedEvent){
            $invitedEventList[$invitedEvent->id] = $invitedEvent;
            unset($invitedEventListTemp[$key]);
        }
        
        $joinedEventList = array();
        foreach($joinedEventListTemp as $key => $joinedEvent){
            $joinedEventList[$joinedEvent->id] = $joinedEvent;
            unset($joinedEventListTemp[$key]);
        }
        
        $inviteTableTitle = OW::getLanguage()->text('memberx', 'invite_someone_to_groups', array('name' => $userToInvite['title']));

        $eventAddUrl = OW::getRouter()->urlForRoute('groups-create'); 
        $processUrl = OW::getRouter()->urlForRoute('memberx.invitetogroups');
        
        $this->assign('eventAddUrl', $eventAddUrl);
        $this->assign('processUrl', $processUrl);
        $this->assign('userIdToInvite', $userIdToInvite);
        $this->assign('userToInvite', $userToInvite);
        $this->assign('inviteTableTitle', $inviteTableTitle);
        
        $this->assign('eventList', $myGroupList);
        $this->assign('formName', self::INVITE_FORM_NAME);
        $this->assign('invitedEventList', $invitedEventList);
        $this->assign('joinedEventList', $joinedEventList);
        
        $this->addForm(self::createForm());
        
        
    }
    
    public  static function createForm(){
    	
    	$processUrl = OW::getRouter()->urlForRoute('memberx.invitetogroups');
    
        $form = new Form(self::INVITE_FORM_NAME);
        $userId = new HiddenField(self::INVITE_FORM_USER_ID);
        $eventId = new HiddenField(self::INVITE_FORM_GROUP_ID);
        $url = new HiddenField(self::INVITE_FORM_PROCESS_URL);
        $url->setValue($processUrl);
        $form->addElement($url);
        $form->addElement($eventId);
        $form->addElement($userId);
        
        return $form;
    }
    
    
    public static function processInvite(){
        
        if (!OW::getUser()->getId()){
        	return false;
        }
        
        $form = self::createForm();
        if (!$form->isValid($_POST)){
            
            return false;
        }
        
        $values = $form->getValues();

        $userIdToInvite = (int)$values[self::INVITE_FORM_USER_ID];
        $groupId = (int)$values[self::INVITE_FORM_GROUP_ID];
        
        if (!$userIdToInvite || ! $groupId){
            return false;
        }

        $group = GROUPS_BOL_GroupDao::getInstance()->findById($groupId);
        
        if (empty($group)){
            return false;
        }
        
        if ($group->status !== 'active'){
            return false;
        }
        
        if (!MEMBERX_BOL_GroupDao::getInstance()->isGroupCanInvite(OW::getUser()->getId(), $groupId)){
            return false;
        }
        
        GROUPS_BOL_Service::getInstance()->inviteUser($groupId, $userIdToInvite, OW::getUser()->getId());
        return true;
        
    }
}