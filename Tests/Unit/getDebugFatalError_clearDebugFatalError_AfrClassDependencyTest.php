<?php
declare(strict_types=1);

namespace Unit;

use Unit\AfrClassDependencyExtended as AfrClassDependency;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../TestClasses/bootstrapTestClasses.php';


class getDebugFatalError_clearDebugFatalError_AfrClassDependencyTest extends TestCase
{
    function getClearDebugFatalErrorProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        AfrClassDependency::clearDebugFatalError();
        AfrClassDependency::clearDependencyInfo();
        AfrClassDependency::setSkipClassInfo([]);
        AfrClassDependency::setSkipNamespaceInfo([]);

        $aReturn = [];
        $aReturn[] = [AfrClassDependency::getClassInfo(new \stdClass())];

        return $aReturn;
    }

    /**
     * @test
     * @dataProvider getClearDebugFatalErrorProvider
     */
    public function getClearDebugFatalErrorTest($oDep): void
    {
        $this->assertEquals(true, $oDep instanceof AfrClassDependency);

        $mErrArr = AfrClassDependency::getDebugFatalError();
        $this->assertEquals('array', gettype($mErrArr));
        $this->assertCount(0, $mErrArr);

        AfrClassDependency::setAFatalErr('CorruptedClass');
        $mErrArr = AfrClassDependency::getDebugFatalError();
        $this->assertEquals('array', gettype($mErrArr));
        $this->assertCount(1, $mErrArr);


        AfrClassDependency::clearDebugFatalError();
        $mErrArr = AfrClassDependency::getDebugFatalError();
        $this->assertEquals('array', gettype($mErrArr));
        $this->assertCount(0, $mErrArr);
    }


}