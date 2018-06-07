BITRIX/TWIG
===========

> Module for rendering twig templates in bitrix framework

[rus](README_RU.md)

## Intro

Bitrix framework allows render content with third-party template engines, in particular, [twig](https://twig.symfony.com).

To implement this, bitrix required a global (_sic!_) array `$arCustomTemplateEngines` with next structure:

```php
$arCustomTemplateEngines['twig'] = [
    'templateExt' => ['twig', 'html.twig'],
    'function'    => 'renderTwigTemplate'
];
```

The `templateExt` key describes extensions of template files, and in `function` key of this array contains the global (_sic!_) function name, which implement the render of template.

Also, function must instantiate the template engine class and echo (yes, fucking echo, not return!) the result of rendering.

If you even feel headache because of this, wait a minute, **this is not all**. 

## Installation

You **MUST** use the composer autoloader in application init. You site **MUST** use utf-8 codepage.

To install module, simple run 

```
composer require andrew72ru/bitrix-twig
```

Module will be stored in you `vendor`, register it classes and functions.

## Configuration

Default configuration:

```php
'debug' => false,
'charset' => 'utf-8',
'cache' => $_SERVER['DOCUMENT_ROOT'] . '/bitrix/cache/twig',
'auto_reload' => $this->request->get('clear_cache') ? 'Y' === strtoupper($this->request->get('clear_cache')) : false,
'autoescape' => false,
```

You may override or extend this configuration in `bitrix/.settings.php` file. The configuration key is `twigRenderer`. You config may be like this:

```php
// bitrix/.settings.php

<?php

return [
    // skip
    'twigRenderer' => [
        'value' => [
            'debug' => true,
        ]
    ],
    // skip
];
```

## Usage

Simple place the file `<component_template_name>.thml.twig` (or `<component_template_name>.twig`) to you template folder, instead of `<component_template_name>.php`. All html-markup and twig functions / filters will be processed by twig engine. Module implement some custom functions to integrate template engine and bitrix framework (see below).

#### Custom twig variables functions

##### Global variables:

- `$_SERVER` — `_SERVER`;
- `$_REQUEST` — `_REQUEST`;
- `$_GET` — `_GET`;
- `$_POST` — `_POST`;
- `$_FILES` — `_FILES`;
- `$_SESSION` — `_SESSION`;
- `$_COOKIE` — `_COOKIE`;
- `$_GLOBALS` — `_GLOBALS`

##### Custom functions

- `showError`
- `showMessage`
- `showNote`
- `bitrix_sessid_post`
- `bitrix_sessid_get`
- `getMessage`
- `include_component`

All of this the same bitrix framework functions.

- `_call_static($class, $method, $arguments = [])` is the function to call static method from available class from framework kernel or autoloaded classes.

##### Variables in template

- `result` — `$arResult` in traditional template;
- `params` — `$arParams` in traditional template;
- `lang` — array with all loaded localized messages;
- `template` — instance of `CBitrixComponentTemplate` class;
- `templateFolder` — path to template folder;
- `parentTemplateFolder` — path to folder of parent template;

## Events

The module call the `onAfterTwigEngineInit` event after initialization. You may use it for add you own functions / filters / extensions.

## Tests

Now the test contains a minimal (example) test, because of bitrix framework not have a psr autoloader, service container and other framework-must-have thinks. Tests are possible only if you have installed framework, installed module and at least one template, renders by this module.

If you know how to make test for all of classes — you welcome.