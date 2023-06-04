<?php
declare(strict_types=1);

namespace Unit;

use Autoframe\ClassDependency\AfrClassDependency;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../TestClasses/bootstrapTestClasses.php';

class setSkipNamespaceInfo_AfrClassDependencyTest extends TestCase
{
    static function setSkipNamespaceInfoProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        $aReturn = [];
        $aReturn[] = ['', 'GlobalMockSingleton', true];
        $aReturn[] = ['', 'PHPUnit\Framework\Assert', false];
        $aReturn[] = ['\\', __CLASS__, true];
        $aReturn[] = ['\\', 'GlobalMockSingleton', true];
        $aReturn[] = ['\\', 'PHPUnit\Framework\TestSuite', true];
        $aReturn[] = ['imaginary', 'imaginary\un_existent', true];
        $aReturn[] = ['imaginary', 'imaginary\subNs\un_existent', false];
        $aReturn[] = ['imaginary\\', 'imaginary\un_existent', true];
        $aReturn[] = ['PHPUnit\Framework\\', 'PHPUnit\Framework\MockObject\Exception', true];
        $aReturn[] = ['PHPUnit\Framework', 'PHPUnit\Framework\Assert', true];
        $aReturn[] = ['PHPUnit\Framework', 'PHPUnit\Framework\MockObject\Exception', false];
        return $aReturn;
    }

    /**
     * @test
     * @dataProvider setSkipNamespaceInfoProvider
     */
    public function setSkipNamespaceInfoTest(string $sNamesapce, string $sFQCNToCheck, bool $bExpected): void
    {
        $this->resetClassDep();
        AfrClassDependency::setSkipNamespaceInfo([$sNamesapce], false);
        $this->assertEquals($bExpected, in_array(
            AfrClassDependency::getClassInfo($sFQCNToCheck)->getType(),
            ['skip'/*, 'unknown'*/]
        ));
    }



    static function setSkipNamespaceInfoMergTrueProvider(): array
    {
        echo __CLASS__ . '->' . __FUNCTION__ . PHP_EOL;
        $aCombine = [];

        $aCombine[] = ['|reset|', ['' => false]];

        $aCombine[] = ['PHPUnit\Framework', [ //bool means that class will have type = skip
            'GlobalMockSingleton' => false,
            'PHPUnit\Framework\Assert' => true,
            'PHPUnit\Framework\TestSuite' => true,
            'PHPUnit\Framework\MockObject\Exception' => false,
            'imaginary\un_existent' => false,
            'imaginary\subNs\un_existent' => false,
        ]];

        $aCombine[] = ['imaginary', [ //bool means that class will have type = skip
            'GlobalMockSingleton' => false,
            'PHPUnit\Framework\Assert' => true,
            'PHPUnit\Framework\TestSuite' => true,
            'PHPUnit\Framework\MockObject\Exception' => false,
            'imaginary\un_existent' => true,
            'imaginary\subNs\un_existent' => false,
        ]];
        $aCombine[] = ['|reset|', ['' => false]];

        $aCombine[] = ['PHPUnit\Framework\\', [ //bool means that class will have type = skip
            'GlobalMockSingleton' => false,
            'PHPUnit\Framework\Assert' => true,
            'PHPUnit\Framework\TestSuite' => true,
            'PHPUnit\Framework\MockObject\Exception' => true,
            'imaginary\un_existent' => false,
            'imaginary\subNs\un_existent' => false,
        ]];

        $aCombine[] = ['', [ //bool means that class will have type = skip
            'GlobalMockSingleton' => true,
            'PHPUnit\Framework\Assert' => true,
            'PHPUnit\Framework\TestSuite' => true,
            'PHPUnit\Framework\MockObject\Exception' => true,
            'imaginary\un_existent' => false,
            'imaginary\subNs\un_existent' => false,
        ]];

        $aCombine[] = ['imaginary\\', [ //bool means that class will have type = skip
            'GlobalMockSingleton' => true,
            'PHPUnit\Framework\Assert' => true,
            'PHPUnit\Framework\TestSuite' => true,
            'PHPUnit\Framework\MockObject\Exception' => true,
            'imaginary\un_existent' => true,
            'imaginary\subNs\un_existent' => true,
        ]];

        $aCombine[] = ['|reset|', ['' => false]];

        $aCombine[] = ['\\', [ //bool means that class will have type = skip
            'GlobalMockSingleton' => true,
            'PHPUnit\Framework\Assert' => true,
            'PHPUnit\Framework\TestSuite' => true,
            'PHPUnit\Framework\MockObject\Exception' => true,
            'imaginary\un_existent' => true,
            'imaginary\subNs\un_existent' => true,
            __CLASS__ => true,
        ]];
        $aReturn = [];
        foreach ($aCombine as $aCombineL1) {
            foreach ($aCombineL1[1] as $sFQCN => $bSkip) {
                $aReturn[] = [$aCombineL1[0], $sFQCN, $bSkip];
            }
        }

        return $aReturn;
    }

    private static $sLastNs = ' ';
    private static $iDS = 0;

    /**
     * @test
     * @dataProvider setSkipNamespaceInfoMergTrueProvider
     */
    public function setSkipNamespaceInfoMergTrueTest(string $sNs, string $sFQCN, bool $bSkip): void
    {
        // echo "# " . self::$iDS . " @$sNs\\$sFQCN($bSkip)\n"; self::$iDS++;

        if ($sNs === '|reset|') {
            $this->resetClassDep();
            $this->assertEquals([], AfrClassDependency::getDependencyInfo());
            return;
        }

        if ($sNs != self::$sLastNs) {
            AfrClassDependency::setSkipNamespaceInfo([$sNs], true);
            self::$sLastNs = $sNs;
        }

        $this->assertEquals($bSkip, AfrClassDependency::getClassInfo($sFQCN)->getType() === 'skip');

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