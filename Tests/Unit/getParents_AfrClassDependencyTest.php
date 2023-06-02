<?php
declare(strict_types=1);

namespace Unit;

use Autoframe\ClassDependency\AfrClassDependency;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../TestClasses/bootstrapTestClasses.php';

class getParents_AfrClassDependencyTest extends TestCase
{
    private function flipT(array $a): array
    {
        $a = array_flip($a);
        foreach ($a as &$v) {
            $v = true;
        }
        return $a;
    }

    function getParentsProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;

        AfrClassDependency::flush();

        $aDeps = [
            'GlobalMockInterfaceExa' => $this->flipT([]),
            'GlobalMockInterfaceExb' => $this->flipT([]),
            'GlobalMockInterface' => $this->flipT([]),
            'GlobalMockTraitSub' => $this->flipT([]),
            'GlobalMockTrait' => $this->flipT([]),
            'GlobalMockAbstract' => $this->flipT([]),
            'GlobalMockClass' => $this->flipT(['GlobalMockAbstract']),
            'GlobalMockClass2' => $this->flipT(['GlobalMockAbstract', 'GlobalMockClass']),
            'GlobalMockSingleton' => $this->flipT(['GlobalMockAbstract', 'GlobalMockClass', 'GlobalMockClass2']),
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
     * @dataProvider getParentsProvider
     */
    public function getParentsTest(AfrClassDependency $oDep, array $aExpected): void
    {
        $aDetected = $oDep->getParents();
        ksort($aDetected);
        ksort($aExpected);
        $this->assertEquals($aExpected, $aDetected);
    }


}