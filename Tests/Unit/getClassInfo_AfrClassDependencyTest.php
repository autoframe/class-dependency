<?php
declare(strict_types=1);

namespace Unit;

use Autoframe\ClassDependency\AfrClassDependency;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../TestClasses/bootstrapTestClasses.php';

class getClassInfo_AfrClassDependencyTest extends TestCase
{


    function getClassInfoProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;

        AfrClassDependency::flush();

        $aReturn = [
            ['GlobalMockInterfaceExa'],
            ['GlobalMockTraitSub'],
            ['GlobalMockAbstract'],
            ['GlobalMockSingleton'],
            [\GlobalMockSingleton::getInstance()],
            [new \stdClass()],
        ];

        return $aReturn;
    }

    /**
     * @test
     * @dataProvider getClassInfoProvider
     */
    public function getClassInfoTest($obj_sFQCN): void
    {
        $oAfrClassDependency = AfrClassDependency::getClassInfo($obj_sFQCN);
        $this->assertEquals(true, $oAfrClassDependency instanceof AfrClassDependency);
    }


}