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

class LANGCSVIMPORTER_BOL_Service
{
    CONST FILE_NAME     = 'dump.csv';
    
    /**
     * @var LANGCSVIMPORTER_BOL_Service
     */
    private static $classInstance;
    
    public $errors = 0;
    
    /*
     * @return LANGCSVIMPORTER_BOL_Service
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    private $availableSeparators;
    private $filename;
    private $fileHandler;
    /*
     * 
     * @return LANGCSVIMPORTER_BOL_Service
     */
    private function __construct()
    {
        $this->availableSeparators = array( ",", '"', '\\', "'", '/', "\t", ':', ';', '#', '|', '$', '%', '^', '&', '*', '@', ' ' );
        $this->filename = OW::getPluginManager()->getPlugin( 'langcsvimporter' )->getPluginFilesDir() . self::FILE_NAME;
    }
    
    public function getFilename()
    {
        return $this->filename;
    }

    private function getSeparator( $separator )
    {
        $key = array_search( $separator, $this->availableSeparators );
        
        return isset( $this->availableSeparators[$key + 1] ) ? $this->availableSeparators[$key + 1] : FALSE;
    }

    private function getSeparators( $delimiter = NULL, $enclosure = NULL, $escape = NULL )
    {
        $escape = $this->getSeparator( $escape );
        
        if ( $escape === FALSE )
        {
            $escape = $this->availableSeparators[0];
            $enclosure = $this->getSeparator( $enclosure );
            
            if ( $enclosure === FALSE )
            {
                $enclosure = $this->availableSeparators[0];
                $delimiter = $this->getSeparator( $delimiter );
                
                if ( $delimiter === FALSE )
                {
                    return FALSE;
                }
            }
        }
        
        return array(
            $delimiter,
            $enclosure,
            $escape
        );
    }
    
    private function checkEnclosure( array $data )
    {
        $match = 0;
        
        foreach ( $data as $val )
        {
            if ( $val == 'NULL' )
            {
                $match++;
                continue;
            }
            
            $strlen = strlen( $val );

            if ( $strlen > 1 )
            {
                if ( $val{0} == $val{$strlen - 1} && in_array($val{0}, $this->availableSeparators) )
                {
                    $match++;
                }
            }
            else
            {
                continue;
            }
        }

        return count( array_filter(array_map('trim', $data), 'strlen') ) === $match;
    }
    
    private function checkEscape( array $data, $escape )
    {
        $count = 0;
        
        foreach ( $data as $val )
        {
            $split = str_split( $val );
            $intersect = array_intersect( $split, array('\\', "'", '"') );
            
            if ( !empty($intersect) )
            {
                if ( $escape != $split[key($intersect) - 1] )
                {
                    return TRUE;
                }
            }
            else
            {
                continue;
            }
        }
        
        return $count > 0;
    }

    private function parse( $delimiter, $enclosure, $escape )
    {
        $rowCount = 0;
        $csvDataCount = 0;
        
        fseek( $this->fileHandler, 0 );
        
        while ( ($data = fgetcsv($this->fileHandler, 0, $delimiter, $enclosure, $escape)) !== FALSE )
        {
            if ( ($count = count($data)) === 1 || $this->checkEnclosure($data) )
            {
                return FALSE;
            }

            if ( $rowCount === 0 )
            {
                $csvDataCount = $count;
                $rowCount++;
                continue;
            }

            if ( $count === $csvDataCount )
            {
                if ( $rowCount === 10 )
                {
                    break;
                }

                $csvDataCount = $count;
                $rowCount++;
            }
            else
            {
                return FALSE;
            }
        }
        
        return $rowCount === 10 || feof( $this->fileHandler );
    }
    
    public function parseAttempt( $filename )
    {        
        if ( !file_exists($filename) or ($this->fileHandler = fopen($filename, 'r')) === FALSE )
        {
            return FALSE;
        }
        
        $result = FALSE;
        
        $delimiter = ',';
        $enclosure = '"';
        $escape = '\\';
            
        if ( $this->parse($delimiter, $enclosure, $escape) )
        {
            $result = TRUE;
        }
        else
        {
            $delimiter = $this->availableSeparators[0];
            $enclosure = $this->availableSeparators[0];
            $escape = $this->availableSeparators[0];
            
            while ( ($_result = $this->parse($delimiter, $enclosure, $escape)) !== TRUE )
            {
                if ( ($separators = $this->getSeparators($delimiter, $enclosure, $escape)) !== FALSE )
                {
                    list( $delimiter, $enclosure, $escape ) = $separators;
                }
                else
                {
                    $_result = FALSE;
                    break;
                }
            }
            
            $result = $_result;
        }

        if ( $result )
        {
            $config = OW::getConfig();
            
            $config->saveConfig( 'langcsvimporter', 'delimiter', $delimiter );
            $config->saveConfig( 'langcsvimporter', 'enclosure', $enclosure );
            $config->saveConfig( 'langcsvimporter', 'escape', $escape );
        }
        
        return $result;
    }
    
    public function process($langId, $column_number)
    {        
        $config = OW::getConfig();
        
        $delimiter = $config->getValue( 'langcsvimporter', 'delimiter' );
        $enclosure = $config->getValue( 'langcsvimporter', 'enclosure' );
        $escape = $config->getValue( 'langcsvimporter', 'escape' );
        $currentPosition = $config->getValue( 'langcsvimporter', 'current_position' );
        
        $rowCount = 0;
        $insertData = array();
        
        if ( ($handle = fopen($this->filename, 'r')) !== FALSE )
        {
            fseek( $handle, $currentPosition );
            
            while ( ($data = fgetcsv($handle, 0, $delimiter, $enclosure, $escape)) !== FALSE )
            {               
                if ( $rowCount === 10000 )
                {
                    break;
                }
//                elseif ( $rowCount === (2000) )
//                {
//                    $currentPosition = ftell( $handle );
//                }
                $this->importLanguage($data[0], $data[$column_number], $langId);
                
                $rowCount++;
            }
            
            if ( feof($handle) )
            {
                $currentPosition = 0;
            }
            
            fclose( $handle );
            $config->saveConfig( 'langcsvimporter', 'current_position', $currentPosition );
        }
    }
    
    
    public function importLanguage( $lang, $value, $langId )
    {
        $matches = array();
        
        preg_match('/^([\w-]+)[+]([\w-]+)$/', $lang, $matches);
        
        if ( empty($matches[0]) )
        {
            return;
        }
        
        $prefix = $matches[1];
        $key = $matches[2];
        
        $prefixDto = BOL_LanguageService::getInstance()->findPrefix($prefix);
        
        if ( empty($prefixDto) )
        {
            $prefixDto = new BOL_LanguagePrefix();
            $prefixDto->label = $prefix;
            $prefixDto->prefix = $prefix;
            
            BOL_LanguageService::getInstance()->savePrefix($prefixDto);
        }
        
        try {
            BOL_LanguageService::getInstance()->addOrUpdateValue($langId, $prefix, $key, $value, false);
        } catch (Exception $ex) {
            $this->errors++;
            printVar($lang);
            printVar($value);
            printVar($matches);
            printVar($ex->getMessage());
        }
             
    }
}
