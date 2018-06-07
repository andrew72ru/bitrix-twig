<?php


class TwigLoaderCest
{
    /**
     * @var \Creative\Twig\TwigLoader
     */
    private $loader;

    public function _before()
    {
        $this->loader = new \Creative\Twig\TwigLoader();
    }

    public function tryToCreateClass(UnitTester $I)
    {
        $I->assertInstanceOf(\Creative\Twig\TwigLoader::class, $this->loader);
    }

    /**
     * @param UnitTester $I
     *
     * @throws ReflectionException
     */
    public function tryToGetComponentTemplatePath(UnitTester $I)
    {
        $reflect = new ReflectionClass($this->loader);
        $method = $reflect->getMethod('normalizeName');
        $method->setAccessible(true);

        $withSlashes = $method->invokeArgs($this->loader, ['vendor/component.name/template/view']);
        $I->assertEquals('vendor/component.name/template/view', $withSlashes);

        $notFull = $method->invokeArgs($this->loader, ['vendor:component.name']);
        $I->assertEquals('vendor:component.name:.default:template', $notFull);
    }

    public function tryToMakeEngineClass(UnitTester $I)
    {
        $engine = new \Creative\Twig\TemplateEngine();
        $I->assertInstanceOf(\Creative\Twig\TemplateEngine::class, $engine);

        $refl = new ReflectionClass($engine);
        $method = $refl->getMethod('getEngine');
        $method->setAccessible(true);

        /** @var \Twig_Environment $result */
        $result = $method->invoke($engine);
        $I->assertInstanceOf(\Twig_Environment::class, $result);
    }
}
