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

class MEMBERX_CMP_EventSelector extends OW_Component{
    
    const INVITE_FORM_NAME = 'invite-to-event-form';
    const INVITE_FORM_USER_ID = 'invite-to-event-user-id';
    const INVITE_FORM_EVENT_ID = 'invite-to-event-event-id';
    const INVITE_FORM_PROCESS_URL = 'invite-to-event-process-url';
    
    public $userId;
    public function __construct($userIdToInvite) {
        parent::__construct();
        $this->userId = OW::getUser()->getId();
        
        
        $eventAuth = BOL_AuthorizationService::getInstance()->isActionAuthorized('event', 'add_event');
        $this->assign('isAuthorized', $eventAuth);
        
        
        if (!$eventAuth){
            $upgradeUrl = OW::getRouter()->urlForRoute('membership_subscribe');
            $upgradeMessage = OW::getLanguage()->text('memberx', 'no_authorized_to_create_event', array('upgradUrl' => $upgradeUrl));
            $this->assign('upgradeMessage', $upgradeMessage);
            return;
        }
    
        $userToInvite = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userIdToInvite));

        if (empty($userToInvite) || !isset($userToInvite[$userIdToInvite])){
            return;
        }
        
        $userToInvite = $userToInvite[$userIdToInvite];
        
        $myEventList = MEMBERX_BOL_EventDao::getInstance()->findLatestUserCreatedEvents($this->userId, 0, 50);
        
        foreach($myEventList as $event){
            if ($event->image){
                $event->imageUrl = EVENT_BOL_EventService::getInstance()->generateImageUrl($event->image, true);
            }else{
                $event->imageUrl = EVENT_BOL_EventService::getInstance()->generateDefaultImageUrl();
            }
            
            $event->viewUrl = OW::getRouter()->urlForRoute('event.view', array('eventId' => $event->id));
            
        }
        
        $invitedEventListTemp = MEMBERX_BOL_EventDao::getInstance()->findLatestUserInvitedEvents($userIdToInvite, 50);
        $joinedEventListTemp = MEMBERX_BOL_EventDao::getInstance()->findLatestUserEventsWithStatus($userIdToInvite, 0, 50);

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
        
        $inviteTableTitle = OW::getLanguage()->text('memberx', 'invite_someone_to_event', array('name' => $userToInvite['title']));

        $eventAddUrl = OW::getRouter()->urlForRoute('event.add'); 
        $processUrl = OW::getRouter()->urlForRoute('memberx.invitetoevent');
        
        $this->assign('eventAddUrl', $eventAddUrl);
        $this->assign('processUrl', $processUrl);
        $this->assign('userIdToInvite', $userIdToInvite);
        $this->assign('userToInvite', $userToInvite);
        $this->assign('inviteTableTitle', $inviteTableTitle);
        
        $this->assign('eventList', $myEventList);
        $this->assign('formName', self::INVITE_FORM_NAME);
        $this->assign('invitedEventList', $invitedEventList);
        $this->assign('joinedEventList', $joinedEventList);
        
        $this->addForm(self::createForm());
        
    }
    
    public  static function createForm(){
    	
    	$processUrl = OW::getRouter()->urlForRoute('memberx.invitetoevent');
    
        $form = new Form(self::INVITE_FORM_NAME);
        $userId = new HiddenField(self::INVITE_FORM_USER_ID);
        $eventId = new HiddenField(self::INVITE_FORM_EVENT_ID);
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

        $userIdToInvite = (int)$values['invite-to-event-user-id'];
        $eventId = (int)$values['invite-to-event-event-id'];
        
        if (!$userIdToInvite || ! $eventId){
            return false;
        }
        
        $eventService = EVENT_BOL_EventService::getInstance();
        $inviteDao = EVENT_BOL_EventInviteDao::getInstance();
        
        
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userIdToInvite);
        $example->andFieldEqual('eventId', $eventId);
        
        $invite = $inviteDao->findObjectByExample($example);
        
        if ($invite){
        	return true;
        }
        
        $event = $eventService->findEvent($eventId);
        if (empty($event)){
            return false;
        }
       
        if ($event->userId != OW::getUser()->getId()){
            return false;
        }
        
        $invitation = EVENT_BOL_EventService::getInstance()->inviteUser($eventId, $userIdToInvite, OW::getUser()->getId());
        return $invitation;
    }
}