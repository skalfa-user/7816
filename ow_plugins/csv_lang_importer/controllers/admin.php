<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2011, Oxwall Foundation
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

class LANGCSVIMPORTER_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        
        $this->service = LANGCSVIMPORTER_BOL_Service::getInstance();      
    }

    public function uploadFile( $params = NULL )
    {
        $form = new UploaderForm();
        $this->addForm($form);
        $this->addComponent('menu', $this->getMenu('import'));

        $this->setPageTitle(OW::getLanguage()->text('langcsvimporter', 'langcsvimporter_page'));
        $this->setPageHeading(OW::getLanguage()->text('langcsvimporter', 'langcsvimporter_page'));

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                UTIL_File::removeDir( OW::getPluginManager()->getPlugin('langcsvimporter')->getPluginFilesDir(), TRUE );
                OW::getConfig()->saveConfig( 'langcsvimporter', 'current_position', 0 );
                move_uploaded_file( $_FILES['csv_file']['tmp_name'], $this->service->getFilename() );

                $data = $form->getValues();

                $language = BOL_LanguageService::getInstance()->findByTag($data['lang_tag']);

                if ( empty($language) )
                {
                    $language = new BOL_Language();
                    $language->label = $data['lang_name'];
                    $language->tag = $data['lang_tag'];
                    $language->rtl = 0;
                    $language->order = 20;
                    $language->status = 'active';

                    BOL_LanguageService::getInstance()->save($language);
                }

                $column_number = ( (int)$data['column_number'] >= 2 ) ? ((int)$data['column_number']) - 1 : 1;

                $this->service->parseAttempt($this->service->getFilename());
                $this->service->process($language->id, $column_number);

                BOL_LanguageService::getInstance()->generateCache($language->id);

                if ( $this->service->errors )
                {
                    OW::getFeedback()->error('import faild!');
                }
                else
                {
                    OW::getFeedback()->info('import success!');
                }

                $this->redirect();
            }
        }
    }

    /**
     * Action export
     */
    public function export()
    {
        $isSubmit = false;

        $form = new LANGCSVIMPORTER_CLASS_XlsxExportForm();
        $this->addForm($form);

        if ( OW::getRequest()->isPost() )
        {
            if ( !empty($_FILES['xml_file']) )
            {
                $path = OW::getPluginManager()->getPlugin('langcsvimporter')->getPluginFilesDir() . 'dump.xml';

                if ( move_uploaded_file($_FILES['xml_file']['tmp_name'], $path) )
                {
                    $isSubmit = true;

                    OW::getFeedback()->info(OW::getLanguage()->text('langcsvimporter', 'success_xml_uploded'));
                }
            }
        }

        $this->assign('isSubmit', $isSubmit);
        $this->addComponent('menu', $this->getMenu('export'));
        $this->setPageTitle(OW::getLanguage()->text('langcsvimporter', 'langcsvimporter_page'));
        $this->setPageHeading(OW::getLanguage()->text('langcsvimporter', 'langcsvimporter_page'));
    }

    /**
     * Action Download xlsx
     */
    public function downloadXlsx()
    {
        $filePath = OW::getPluginManager()->getPlugin('langcsvimporter')->getPluginFilesDir() . 'dump.xml';

        if ( !file_exists($filePath) )
        {
            OW::getFeedback()->error(OW::getLanguage()->text('langcsvimporter', 'error_dowload_xlsx'));
            $this->redirect(OW::getRouter()->urlForRoute('langcsvimporter.admin.export'));
        }

        $exportData = $this->getExportData($filePath);

        $fileName = 'export_data' . date('d-m-y') . '.xlsx';

        if ( !empty($exportData) && is_array($exportData) )
        {
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getActiveSheet()->setTitle('Languages');

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'lang_key')
                ->setCellValue('B1', 'English');

            $nextRow = 2;

            foreach ( $exportData as $item )
            {
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $nextRow, $item['lang_key'])
                    ->setCellValue('B' . $nextRow,  $item['lang_value']);

                $nextRow++;
            }

            $objPHPExcel->setActiveSheetIndex(0);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"$fileName\"");
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
        }
        else
        {
            OW::getFeedback()->error(OW::getLanguage()->text('langcsvimporter', 'error_dowload_xlsx'));
            $this->redirect(OW::getRouter()->urlForRoute('langcsvimporter.admin.export'));
        }

        exit;
    }

    /**
     * Get menu
     */
    private function getMenu( $active = 'import' )
    {
        $item = new BASE_MenuItem();
        $item->setLabel(OW::getLanguage()->text('langcsvimporter', 'admin_import_page_menu'));
        $item->setUrl(OW::getRouter()->urlForRoute('langcsvimporter.admin'));
        $item->setKey('import');
        $item->setOrder(1);
        $item->setActive($active == 'import');

        $item2 = new BASE_MenuItem();
        $item2->setLabel(OW::getLanguage()->text('langcsvimporter', 'admin_export_page_menu'));
        $item2->setUrl(OW::getRouter()->urlForRoute('langcsvimporter.admin.export'));
        $item2->setKey('export');
        $item2->setOrder(2);
        $item2->setActive($active == 'export');

        return new BASE_CMP_ContentMenu([$item, $item2]);
    }

    /**
     * Convert languages xml to array
     */
    protected function convertLangXmlKeysToArray( $xml )
    {
        $data = json_decode(json_encode($xml) , 1);
        $xmlKeys = isset($data['key']) ? $data['key'] : '';

        $xmlKeysList = [];

        if ( !empty($xmlKeys) && is_array($xmlKeys) )
        {
            foreach ( $xmlKeys as $item )
            {
                $xmlKeysList[] = [
                    'key' => $item['@attributes']['name'],
                    'value' => $item['value']
                ];
            }
        }

        return $xmlKeysList;
    }

    /**
     * Get languages xml attributes
     */
    protected function getLangXmlAttributes( $xml )
    {
        $data = json_decode(json_encode($xml) , 1);
        $xmlAttributes = isset($data['@attributes']) ? $data['@attributes'] : '';

        $xmlAttributesList = [];

        if ( !empty($xmlAttributes) && is_array($xmlAttributes) )
        {
            $xmlAttributesList = [
                'lang_key' => $xmlAttributes['name'],
                'label' => $xmlAttributes['label'],
                'language_tag' => $xmlAttributes['language_tag'],
                'language_label' => $xmlAttributes['language_label']
            ];
        }

        return $xmlAttributesList;
    }

    /**
     * Get export data
     */
    protected function getExportData( $filePath )
    {
        $xml = simplexml_load_file($filePath);

        $xmlKeysList = $this->convertLangXmlKeysToArray($xml);
        $xmlAttributesList = $this->getLangXmlAttributes($xml);

        $exportData = [];

        if ( !empty($xmlKeysList) )
        {
            foreach ( $xmlKeysList as $item )
            {
                $exportData[] = [
                    'lang_key' => $xmlAttributesList['lang_key'] . '+' . $item['key'],
                    'lang_value' => $item['value'],
                ];
            }
        }

        return $exportData;
    }
}

class UploaderForm extends Form
{
    public function __construct()
    {
        parent::__construct( 'csvimport_form' );
        
        $this->setAjax( FALSE );
        $this->setEnctype( Form::ENCTYPE_MULTYPART_FORMDATA );

        $element = new TextField('lang_name');
        $element->setLabel('Language Name');
        $element->setRequired();
        $this->addElement($element);
               
        $element = new TextField('lang_tag');
        $element->setLabel('Language Tag');
        $element->setRequired();
        $this->addElement($element);
               
        $element = new TextField('column_number');
        $element->setLabel('Import Language Column Number');
        $element->setValue(2);
        $element->setRequired();
        $this->addElement($element);
        
        $file = new FileField( 'csv_file' );
        $file->addAttribute( 'id', 'csv_file' );
        $file->addValidator( new CsvFileValidator() );
        $file->setLabel( "CSV File" );
        $this->addElement( $file );
        
        $submit = new Submit( 'send' );
        $submit->setValue( 'Submit' );
        $this->addElement( $submit );
    }
}

class CsvFileValidator extends OW_Validator
{
    public function isValid( $value = NULL )
    {
        if ( !empty($_FILES['csv_file']) && in_array($_FILES['csv_file']['type'], array('text/csv', 'application/zip', 'application/octet-stream')) && is_uploaded_file($_FILES['csv_file']['tmp_name']) )
        {
            return TRUE;
        }
        else
        {
            if ( !empty($_FILES['csv_file']['error']) )
            {
                switch ( $_FILES['csv_file']['error'] )
                {
                    case UPLOAD_ERR_INI_SIZE:
                        $this->setErrorMessage( OW::getLanguage()->text('langcsvimporter', 'upload_error_ini_size') ); 
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $this->setErrorMessage( OW::getLanguage()->text('langcsvimporter', 'upload_error_form_size') );
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $this->setErrorMessage( OW::getLanguage()->text('langcsvimporter', 'upload_error_partial') );
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $this->setErrorMessage( OW::getLanguage()->text('langcsvimporter', 'upload_error_no_file') );
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $this->setErrorMessage( OW::getLanguage()->text('langcsvimporter', 'upload_error_no_tmp_fir') );
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $this->setErrorMessage( OW::getLanguage()->text('langcsvimporter', 'upload_error_cant_write') );
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $this->setErrorMessage( OW::getLanguage()->text('langcsvimporter', 'upload_error_extension') );
                        break;
                }
            }
            else
            {
                $this->setErrorMessage( OW::getLanguage()->text('langcsvimporter', 'upload_error_unknow') );
            }
        }
    }
    
    public function getJsValidator()
    {
        return '{
            validate : function( value )
            {
                if ( !value.match("\.(csv|zip)$") )
                {
                    throw OW.getLanguageText( "langcsvimporter", "upload_type_error" );
                }
            }
        }';
    }
}
