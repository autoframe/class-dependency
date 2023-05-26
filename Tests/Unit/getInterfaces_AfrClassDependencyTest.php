<?php
declare(strict_types=1);

namespace Unit;

use Autoframe\ClassDependency\AfrClassDependency;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../TestClasses/bootstrapTestClasses.php';

class getInterfaces_AfrClassDependencyTest extends TestCase
{
    private function flipT(array $a): array
    {
        $a = array_flip($a);
        foreach ($a as &$v) {
            $v = true;
        }
        return $a;
    }

    function getInterfacesProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;

        AfrClassDependency::clearDebugFatalError();
        AfrClassDependency::clearDependencyInfo();
        AfrClassDependency::setSkipClassInfo([]);
        AfrClassDependency::setSkipNamespaceInfo([]);
        $allInterfaces = $this->flipT(['GlobalMockInterfaceExa', 'GlobalMockInterfaceExb','GlobalMockInterface']);
        $aDeps = [
            'GlobalMockInterfaceExa' => $this->flipT([]),
            'GlobalMockInterfaceExb' => $this->flipT([]),
            'GlobalMockInterface' => $this->flipT(['GlobalMockInterfaceExa', 'GlobalMockInterfaceExb']),
            'GlobalMockTraitSub' => $this->flipT([]),
            'GlobalMockTrait' => $this->flipT([]),
            'GlobalMockAbstract' => $allInterfaces,
            'GlobalMockClass' => $allInterfaces,
            'GlobalMockClass2' => $allInterfaces,
            'GlobalMockSingleton' => $allInterfaces,
        ];
        if (PHP_VERSION_ID >= 81000) {
            $aDeps['GlobalMockEnum'] = $this->flipT([]);
        }

        $aReturn = [];
        foreach ($aDeps as $sClassDep => $aVals) {
            $aReturn[] = [AfrClassDependency::getClassInfo($sClassDep), $aVals];
        }
        return $aReturn;
    }

    /**
     * @test
     * @dataProvider getInterfacesProvider
     */
    public function getInterfacesTest(AfrClassDependency $oDep, array $aExpected): void
    {
        $aDetected = $oDep->getInterfaces();
        ksort($aDetected);
        ksort($aExpected);
        $this->assertEquals($aExpected, $aDetected);
    }


}