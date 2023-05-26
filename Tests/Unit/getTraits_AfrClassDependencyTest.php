<?php
declare(strict_types=1);

namespace Unit;

use Autoframe\ClassDependency\AfrClassDependency;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../TestClasses/bootstrapTestClasses.php';

class getTraits_AfrClassDependencyTest extends TestCase
{
    private function flipT(array $a): array
    {
        $a = array_flip($a);
        foreach ($a as &$v) {
            $v = true;
        }
        return $a;
    }

    function getTraitsProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;

        AfrClassDependency::clearDebugFatalError();
        AfrClassDependency::clearDependencyInfo();
        AfrClassDependency::setSkipClassInfo([]);
        AfrClassDependency::setSkipNamespaceInfo([]);
        $aDeps = [
            'GlobalMockInterfaceExa' => $this->flipT([]),
            'GlobalMockInterfaceExb' => $this->flipT([]),
            'GlobalMockInterface' => $this->flipT([]),
            'GlobalMockTraitSub' => $this->flipT([]),
            'GlobalMockTrait' => $this->flipT(['GlobalMockTraitSub']),
            'GlobalMockAbstract' => $this->flipT(['GlobalMockTrait','GlobalMockTraitSub']),
            'GlobalMockClass' => $this->flipT(['GlobalMockTrait','GlobalMockTraitSub']),
            'GlobalMockClass2' => $this->flipT(['GlobalMockTrait','GlobalMockTraitSub']),
            'GlobalMockSingleton' => $this->flipT(['GlobalMockTrait','GlobalMockTraitSub']),
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
     * @dataProvider getTraitsProvider
     */
    public function getTraitsTest(AfrClassDependency $oDep, array $aExpected): void
    {
        $aDetected = $oDep->getTraits();
        ksort($aDetected);
        ksort($aExpected);
        $this->assertEquals($aExpected, $aDetected);
    }


}