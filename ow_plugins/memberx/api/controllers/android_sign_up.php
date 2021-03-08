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

class MEMBERX_ACTRL_AndroidSignUp extends SKANDROID_ACTRL_SignUp{
    
   public function questionList($params) {
        parent::questionList($params);
        
        $accountTypeRestric = MEMBERX_CMP_SearchResultSetting::getString(MEMBERX_CMP_SearchResultSetting::ACCOUNT_TYPE_RESTRICT);
        if ($accountTypeRestric !== 'yes'){
            return;
        }
        
        $questions = $this->assignedVars['questions'];
        $newQuestions = array();
        foreach ($questions as $key => $question) {
            if ($question['name'] !== 'match_sex') {
                $newQuestions[] = $question;
            }
        }
        

        $this->assign('questions', $newQuestions);
        
    }
    
    public function save($params) {
        
        $accountTypeRestric = MEMBERX_CMP_SearchResultSetting::getString(MEMBERX_CMP_SearchResultSetting::ACCOUNT_TYPE_RESTRICT);
        if ($accountTypeRestric !== 'yes'){
            parent::save($params);
            return;
        }
        
        $matchSex = 0;
        $questionService = BOL_QuestionService::getInstance();
        $questionOptions = $questionService->findQuestionValues('match_sex');
        if ($questionOptions && isset($params['sex'])){
            foreach ($questionOptions as $option){
                if ($option->value != $params['sex']){
                    $matchSex += $option->value;
                }
            }
        }
        
        $params['match_sex'] = $matchSex;
        parent::save($params);
        
        
        
        
    }
    
}