<?php
declare(strict_types=1);

namespace Unit;

use Unit\AfrClassDependencyExtended as AfrClassDependency;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../TestClasses/bootstrapTestClasses.php';
//AfrClassDependency::setSkipNamespaceInfoGathering(['Autoframe\\Core\\','Symfony\Component\Finder']);
//AfrClassDependency::setSkipClassInfoGathering(['URLify','phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen']);




class clearClassInfo_AfrClassDependencyTest extends TestCase
{
    function clearClassInfoProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        AfrClassDependency::clearDebugFatalError();
        AfrClassDependency::clearDependencyInfo();
        AfrClassDependency::setSkipClassInfo([]);
        AfrClassDependency::setSkipNamespaceInfo([]);


        $aReturn = [];
        $aReturn[] = ['imaginary\un_existent'];
        $aReturn[] = ['GlobalMockInterface'];
        $aReturn[] = ['GlobalMockTrait'];
        $aReturn[] = ['GlobalMockClass2'];
        $aReturn[] = ['GlobalMockSingleton'];

        return $aReturn;
    }

    /**
     * @test
     * @dataProvider clearClassInfoProvider
     */
    public function clearClassInfoTest($sFQCN): void
    {
        AfrClassDependency::getClassInfo($sFQCN);
        $this->assertEquals(true, isset(AfrClassDependency::getADependency()[$sFQCN]));
        AfrClassDependency::clearClassInfo($sFQCN);
        $this->assertEquals(false, isset(AfrClassDependency::getADependency()[$sFQCN]));
    }


}