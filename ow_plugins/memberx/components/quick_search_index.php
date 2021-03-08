<?php


class MEMBERX_CMP_QuickSearchIndex extends OW_Component
{
    public function __construct()
    {
        parent::__construct();

        $component = OW::getClassInstance('MEMBERX_CMP_QuickSearch', $this);
        $this->addComponent('quickSearch', $component);
    }
}
