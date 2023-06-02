<?php
declare(strict_types=1);

namespace Unit;

use Autoframe\ClassDependency\AfrClassDependency;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../TestClasses/bootstrapTestClasses.php';

class setSkipClassInfo_AfrClassDependencyTest extends TestCase
{
    function setSkipClassInfoProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        $aReturn = [];
        $aReturn[] = ['imaginary\un_existent'];
        $aReturn[] = ['GlobalMockSingleton'];
        $aReturn[] = ['PHPUnit\Framework\TestSuite'];
        return $aReturn;
    }

    /**
     * @test
     * @dataProvider setSkipClassInfoProvider
     */
    public function setSkipClassInfoTest(string $sFQCN): void
    {
        $this->resetClassDep();
        $this->assertEquals(true,AfrClassDependency::getClassInfo($sFQCN)->getType() !== 'skip');

        AfrClassDependency::setSkipClassInfo([$sFQCN]);
        $this->assertEquals(true, in_array(
            AfrClassDependency::getClassInfo($sFQCN)->getType(),
            ['skip', 'unknown']
        ));
    }







    function setSkipClassInfoMergeProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        $aFQCN = ['imaginary\un_existent','GlobalMockSingleton','PHPUnit\Framework\TestSuite'];
        shuffle($aFQCN);
        return [[$aFQCN]];
    }

    /**
     * @test
     * @dataProvider setSkipClassInfoMergeProvider
     */
    public function setSkipClassInfoMergeFalseTest(array $aFQCN): void
    {
        $this->resetClassDep();
        foreach($aFQCN as $sFQCN){
            $this->assertEquals(true,AfrClassDependency::getClassInfo($sFQCN)->getType() !== 'skip');
            AfrClassDependency::setSkipClassInfo([$sFQCN],false);
            $this->assertEquals(true, in_array(
                AfrClassDependency::getClassInfo($sFQCN)->getType(),
                ['skip', 'unknown']
            ));
            foreach(array_diff($aFQCN,[$sFQCN]) as $sClassDiff){
                $this->assertEquals(true,AfrClassDependency::getClassInfo($sClassDiff)->getType() !== 'skip');
            }
        }
    }


    /**
     * @test
     * @dataProvider setSkipClassInfoMergeProvider
     */
    public function setSkipClassInfoMergTrueTest(array $aFQCN): void
    {
        $this->resetClassDep();
        $aSkipped = [];
        foreach($aFQCN as $sFQCN){
            $this->assertEquals(true,AfrClassDependency::getClassInfo($sFQCN)->getType() !== 'skip');
            AfrClassDependency::setSkipClassInfo([$sFQCN],true);
            $aSkipped[] = $sFQCN;
            $this->assertEquals(true, in_array(
                AfrClassDependency::getClassInfo($sFQCN)->getType(),
                ['skip', 'unknown']
            ));
            foreach(array_diff($aFQCN,$aSkipped) as $sClassDiff){
                $this->assertEquals(true,AfrClassDependency::getClassInfo($sClassDiff)->getType() !== 'skip');
            }
        }
    }
    /**
     * @return void
     * @throws \Autoframe\Components\Exception\AfrException
     */
    private function resetClassDep(): void
    {
        AfrClassDependency::flush();

    }


}