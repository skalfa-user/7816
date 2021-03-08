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

class MEMBERX_ACTRL_AndroidUser extends SKANDROID_ACTRL_User{
    
    public function getSearchQuestions() {
        parent::getSearchQuestions();
        
        $accountTypeRestric = MEMBERX_CMP_SearchResultSetting::getBoolean(MEMBERX_CMP_SearchResultSetting::ACCOUNT_TYPE_RESTRICT);
        
        $questionServie = BOL_QuestionService::getInstance();
        $basicQuestions = $this->assignedVars['basicQuestions'];
        $newBasic = array();
        
        $myGender = 0;
        if (OW::getUser()->isAuthenticated()){
            $myUserId = OW::getUser()->getId();
            $myGenderData = $questionServie->getQuestionData(array($myUserId), array('match_sex'));
      
            if ($myGenderData && isset($myGenderData[$myUserId]['match_sex'])){
                $myGender = $myGenderData[$myUserId]['match_sex'];
            }
        }
        
        
        $sexQuestion = $questionServie->findQuestionByName('match_sex');
        $sexQuestionLable = $questionServie->getQuestionLang('match_sex');
        $sexQuestionValues = $questionServie->findQuestionValues('match_sex');

        $sexQuestionOptions = array();
        foreach($sexQuestionValues as $questionValue){
            $label = $questionServie->getQuestionValueLang($questionValue->questionName, $questionValue->value);
            $value = $questionValue->value;

            if ($accountTypeRestric && $myGender && $value == $myGender){
                continue;
            }
            
            $sexQuestionOptions[] = array('label' => $label, 'value' => $value);
        }
        
        
        
        foreach($basicQuestions as $gender => $questions){
            foreach($questions as $num => $question){
                if ($question['name'] === 'match_sex'){
                    unset($basicQuestions[$gender][$num]);
                }
            }
        }
        
        $sexOption = array();
        foreach($basicQuestions as $gender => $questions){
            
            $sexOption['name'] = $sexQuestion->name;
            $sexOption['label'] = $sexQuestionLable;
            $sexOption['options']['values'] = $sexQuestionOptions;
            $sexOption['count'] = count($sexQuestionOptions);
            $sexOption['presentation'] = BOL_QuestionService::QUESTION_PRESENTATION_MULTICHECKBOX;
            $sexOption['custom'] = array();
            
            array_unshift($questions, $sexOption);
           // $questions[] = $sexOption;
            //$newQuestions = array();
            /*foreach($questions as $key => $question){
                if ($question['name'] !== 'match_sex'){
                    $newQuestions[] = $question;
                }else{
                    $matchSex = $question['options']['values'];
                    //$accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();
                    $myAccountType = ow::getUser()->getUserObject()->accountType;
                    $myAccountTypeLabel = BOL_QuestionService::getInstance()->getAccountTypeLang($myAccountType);


                    $newMatchSex = array();
                    foreach($matchSex as $option){
                        if ($option['label'] !== $myAccountTypeLabel){
                           $newMatchSex[] = $option;
                        }
                    }
                    
                    $question['options']['values'] = $newMatchSex;
                    $newQuestions[] = $question;
                }
            }*/
            
            $newBasic[$gender] = $questions;
            //$newBasic[$gender] = $newQuestions;

            
        }
        $this->assign('basicQuestions', $newBasic);

    }
    
}