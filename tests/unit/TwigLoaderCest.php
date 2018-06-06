<?php


class TwigLoaderCest
{
    public function tryToCreateClass(UnitTester $I)
    {
        $I->assertInstanceOf(\Creative\Twig\TwigLoader::class, new \Creative\Twig\TwigLoader());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ReflectionException
     */
    public function tryToGetComponentTemplatePath(UnitTester $I)
    {
        $loader = new \Creative\Twig\TwigLoader();
        $reflect = new ReflectionClass($loader);
        $method = $reflect->getMethod('normalizeName');
        $method->setAccessible(true);

        $withSlashes = $method->invokeArgs($loader, ['vendor/component.name/template/view']);
        $I->assertEquals('vendor/component.name/template/view', $withSlashes);

        $notFull = $method->invokeArgs($loader, ['vendor:component.name']);
        $I->assertEquals('vendor:component.name:.default:template', $notFull);
    }
}