<?php

class prestamoduledisplayFrontController extends ModuleFrontController {
    
    public function initContent() {
        parent::initContent();

        $this->setTemplate('display.tpl');
    }
    
}