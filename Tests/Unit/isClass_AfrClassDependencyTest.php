<?php
declare(strict_types=1);

namespace Unit;

use Autoframe\ClassDependency\AfrClassDependency;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../TestClasses/bootstrapTestClasses.php';

class isClass_AfrClassDependencyTest extends TestCase
{


    function isClassProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;

        AfrClassDependency::flush();

        $aDeps = [
            'GlobalMockInterfaceExa' => false,
            'GlobalMockInterfaceExb' => false,
            'GlobalMockInterface' => false,
            'GlobalMockTraitSub' => false,
            'GlobalMockTrait' => false,
            'GlobalMockAbstract' => true,
            'GlobalMockClass' => true,
            'GlobalMockClass2' => true,
            'GlobalMockSingleton' => true,
            __CLASS__ => true,
        ];
        if (PHP_VERSION_ID >= 81000) {
            $aDeps['GlobalMockEnum'] = false;
        }

        $aReturn = [];
        foreach ($aDeps as $sClassDep => $bExp) {
            $aReturn[] = [AfrClassDependency::getClassInfo($sClassDep), $bExp];
        }
        return $aReturn;
    }

    /**
     * @test
     * @dataProvider isClassProvider
     */
    public function isClassTest(AfrClassDependency $oDep, bool $bExpected): void
    {
        $this->assertEquals($bExpected, $oDep->isClass());
    }


}