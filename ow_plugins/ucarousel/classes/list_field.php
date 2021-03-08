<?php

class UCAROUSEL_CLASS_ListField extends Selectbox
{
    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        $optionStrings = array();

        if ( $this->hasInvitation )
        {
            $optionStrings[] = UTIL_HtmlTag::generateTag('option', array('value' => ''), true, $this->invitation);
        }

        $groups = array();
        $noGroupOptions = array();

        foreach ( $this->getOptions() as $key => $value )
        {
            if ( is_array($value) && isset($value['group']) )
            {
                $groups[$value['group']] = empty($groups[$value['group']]) ? array() : $groups[$value['group']];
                $groups[$value['group']][$key] = $value['label'];
            }
            else
            {
                $noGroupOptions[$key] = $value;
            }
        }

        foreach ( $noGroupOptions as $key => $value )
        {
            $attrs = ($this->value !== null && (string) $key === (string) $this->value) ? array('selected' => 'selected') : array();
            $attrs['value'] = $key;
            $optionStrings[] = UTIL_HtmlTag::generateTag('option', $attrs, true, trim($value));
        }

        foreach ( $groups as $label => $options )
        {
            $groupOptionsStrings = array();
            foreach ( $options as $key => $value )
            {
                $attrs = ($this->value !== null && (string) $key === (string) $this->value) ? array('selected' => 'selected') : array();
                $attrs['value'] = $key;
                $groupOptionsStrings[] = UTIL_HtmlTag::generateTag('option', $attrs, true, trim($value));
            }

            $optionStrings[] = UTIL_HtmlTag::generateTag('optgroup', array(
                "label" => $label
            ), true, implode('', $groupOptionsStrings));
        }

        return UTIL_HtmlTag::generateTag('select', $this->attributes, true, implode('', $optionStrings));
    }
}