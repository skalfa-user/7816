<?php


class MEMBERX_CLASS_AgeRangeValidator extends OW_Validator
{
    private $from;

    private $to;

    public function __construct( $from, $to )
    {
        $this->from = $from;
        $this->to = $to;

        $this->setErrorMessage(OW::getLanguage()->text('memberx', 'age_range_incorrect'));
    }

    public function isValid( $value )
    {
        if ( !isset($value['from']) || !isset($value['to']) )
        {
            return false;
        }

        if ( (int) $value['from'] < $this->from || (int) $value['from'] > $this->to )
        {
            return false;
        }

        if ( (int) $value['to'] < $this->from || (int) $value['to'] > $this->to )
        {
            return false;
        }

        if ( (int) $value['from'] > (int) $value['to'] )
        {
            return false;
        }

        return true;
    }

    public function getJsValidator()
    {
        $js = "{
            validate : function( value ){
                if ( value.from == undefined || value.to == undefined )
                {
                    throw " . json_encode($this->getError()) . "; return;
                }
                if ( parseInt(value.from) < ".$this->from." || parseInt(value.from) > ".$this->to." )
                {
                    throw " . json_encode($this->getError()) . "; return;
                }
                if ( parseInt(value.to) < ".$this->from." || parseInt(value.to) > ".$this->to." )
                {
                    throw " . json_encode($this->getError()) . "; return;
                }

                if ( parseInt(value.from) > parseInt(value.to) )
                {
                    throw " . json_encode($this->getError()) . "; return;
                }
            },
            getErrorMessage : function(){ return " . json_encode($this->getError()) . " }
        }";

        return $js;
    }
}