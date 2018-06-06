<?php
/**
 * User: andrew
 * Date: 04/06/2018
 * Time: 13:30
 */

use Creative\Twig\TemplateEngine;

if(!function_exists('renderTwigTemplate')) {
    function renderTwigTemplateInit() {
        global $arCustomTemplateEngines;
        $arCustomTemplateEngines['twig'] = [
            'templateExt' => ['twig', 'html.twig'],
            'function'    => 'renderTwigTemplate'
        ];
    }

    /**
     * @param $templateFile
     * @param $arResult
     * @param $arParams
     * @param $arLangMessages
     * @param $templateFolder
     * @param $parentTemplateFolder
     * @param \CBitrixComponentTemplate $template
     *
     * @throws \Creative\Twig\Exception\BitrixTwigException
     */
    function renderTwigTemplate($templateFile, $arResult, $arParams, $arLangMessages,
        $templateFolder, $parentTemplateFolder, \CBitrixComponentTemplate $template)
    {
        TemplateEngine::render($templateFile, $arResult, $arParams, $arLangMessages, $templateFolder,
            $parentTemplateFolder, $template);
    }

    renderTwigTemplateInit();
}
