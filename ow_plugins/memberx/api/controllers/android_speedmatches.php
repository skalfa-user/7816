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


class MEMBERX_ACTRL_AndroidSpeedmatches extends SKANDROID_ACTRL_Speedmatches{
 
    public function getList($params) {
        parent::getList($params);
        
        $accountTypeRestric = MEMBERX_CMP_SearchResultSetting::getString(MEMBERX_CMP_SearchResultSetting::ACCOUNT_TYPE_RESTRICT);
        if ($accountTypeRestric !== 'yes'){
            return;
        }
        
        $matchSexData = $this->assignedVars['filter']['match_sex_data'];
        
        $myAccountType = ow::getUser()->getUserObject()->accountType;
        $myAccountTypeLabel = BOL_QuestionService::getInstance()->getAccountTypeLang($myAccountType);

        $newMatchSexData = array();
        foreach($matchSexData as $option){
            if ($option['label'] !== $myAccountTypeLabel){
                $newMatchSexData[] = $option;
            }
        }
        
        $this->assignedVars['filter']['match_sex_data'] = $newMatchSexData;
        
        
    }
}
