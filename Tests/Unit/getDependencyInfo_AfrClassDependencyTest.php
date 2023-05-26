<?php
declare(strict_types=1);

namespace Unit;

use Autoframe\ClassDependency\AfrClassDependency;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../TestClasses/bootstrapTestClasses.php';


class getDependencyInfo_AfrClassDependencyTest extends TestCase
{
    function getDependencyInfoProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        AfrClassDependency::clearDebugFatalError();
        AfrClassDependency::clearDependencyInfo();
        AfrClassDependency::setSkipClassInfo([]);
        AfrClassDependency::setSkipNamespaceInfo([]);

        $aReturn = [];
        $aReturn[] = [AfrClassDependency::getClassInfo('GlobalMockSingleton')];

        return $aReturn;
    }

    /**
     * @test
     * @dataProvider getDependencyInfoProvider
     */
    public function getDependencyInfoTest($oDep): void
    {
        $this->assertEquals(true, $oDep instanceof AfrClassDependency);

        foreach (AfrClassDependency::getDependencyInfo() as $sFQCN =>$oAfrClassDependency){
            $this->assertEquals(true, $oAfrClassDependency instanceof AfrClassDependency);
        }

    }


}