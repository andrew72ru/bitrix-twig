<?php
/**
 * User: andrew
 * Date: 07/06/2018
 * Time: 10:06
 */

require dirname(dirname(__DIR__)). '/vendor/autoload.php';

class CBitrixComponent {
    public $__templatePage;
    public $__name;
    public $__templateName;
    public function InitComponent($componentName, $template) {
        $this->__name = $componentName;
        $this->__templateName = $template;
    }
    public function getName() {
        return $this->__name;
    }
}

class CBitrixComponentTemplate {
    public $__fileAlt;
    public $__page;
    public $__name;
    public $__component;
    public $__file;

    public function Init(CBitrixComponent $component) {
        $this->__component = $component;
        $this->__name = $this->__component->__templatePage;
    }
    public function GetFolder() {
        return $this->__name;
    }
    public function GetFile() {
        $this->__page = $this->__component->__templatePage;
        return $this->__page;
    }
}

include dirname(__DIR__) . '/_support/Classes/Application.php';
include dirname(__DIR__) . '/_support/Classes/Configuration.php';

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__) . '/_data/www';