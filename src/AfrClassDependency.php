<?php
declare(strict_types=1);

namespace Autoframe\ClassDependency;

use Autoframe\Components\Exception\AfrException;
use ReflectionClass;
use ReflectionMethod;

use function array_flip;
use function is_string;
use function strlen;
use function trim;
use function strpos;
use function explode;
use function array_pop;
use function implode;
use function rtrim;
use function substr;
use function is_object;
use function get_class;
use function class_exists;
use function trait_exists;
use function interface_exists;
use function array_merge;
use function method_exists;
use function class_uses;
use function class_implements;
use function class_parents;

//use function enum_exists; //php8.1

class AfrClassDependency
{
    /**
     * Static methods for global scoping:
     */

    /** @var self[] */
    protected static array $aDependency = [];

    /** @var self[] */
    protected static array $aFatalErr = [];

    /**
     * Object or string containing Fully Qualified Class Name
     * @param mixed $obj_sFQCN
     * @return self
     */
    public static function getClassInfo($obj_sFQCN): self
    {
        $sFQCN = is_object($obj_sFQCN) ? get_class($obj_sFQCN) : (string)$obj_sFQCN;
        if (!isset(self::$aDependency[$sFQCN])) {
            if (isset(self::$aSkipClasses[$sFQCN]) || self::mustSkipNamespaceInfoGatheringForClass($sFQCN)) {
                self::$aDependency[$sFQCN] = self::makeBlank($sFQCN, self::S);
            } else {
                self::$aDependency[$sFQCN] = self::makeBlank($sFQCN, self::F);
                self::$aFatalErr[$sFQCN] = 'Fatal error?';
                // register_shutdown_function + error_get_last() because fatal are not catchable
                try {
                    self::$aDependency[$sFQCN] = new static($sFQCN);
                    unset(self::$aFatalErr[$sFQCN]);
                } catch (\Throwable $oEx) {
                    self::$aFatalErr[$sFQCN] = $oEx->getMessage();
                    self::$aDependency[$sFQCN] = self::makeBlank($sFQCN, self::F);
                }
            }
        } else {
            //isset, so we check for namespace skipping after merge / reconfigure
            if (self::$aDependency[$sFQCN]->getType() !== self::S && (
                    isset(self::$aSkipClasses[$sFQCN]) ||
                    self::mustSkipNamespaceInfoGatheringForClass($sFQCN)
                )) {
                self::morphToSkipped($sFQCN);
            }
        }
        return self::$aDependency[$sFQCN];
    }

    /**
     * Object or string containing Fully Qualified Class Name
     * @param mixed $obj_sFQCN
     * @return bool
     */
    public static function clearClassInfo($obj_sFQCN): bool
    {
        $sFQCN = is_object($obj_sFQCN) ? get_class($obj_sFQCN) : (string)$obj_sFQCN;

        if (isset(self::$aDependency[$sFQCN])) {
            unset(self::$aDependency[$sFQCN]);
            return true;
        }
        return false;
    }

    /**
     * @return self[]
     */
    public static function getDependencyInfo(): array
    {
        return self::$aDependency;
    }

    /**
     * @return void
     */
    public static function clearDependencyInfo(): void
    {
        self::$aDependency = [];
    }


    /**
     * Use in context with register_shutdown_function + error_get_last() because fatal are not catchable
     * @return self[]
     */
    public static function getDebugFatalError(): array
    {
        return self::$aFatalErr;
    }

    /**
     * @return void
     */
    public static function clearDebugFatalError(): void
    {
        self::$aFatalErr = [];
    }


    /**
     * @param array $aFQCN
     * @param bool $bMergeWithExisting
     * @return array
     */
    public static function setSkipClassInfo(array $aFQCN, bool $bMergeWithExisting = false): array
    {
        if ($bMergeWithExisting) {
            $aCurrent = !isset(self::$aSkipClasses) || empty(self::$aSkipClasses) ? [] : self::$aSkipClasses;
            self::$aSkipClasses = array_merge($aCurrent, array_flip($aFQCN));
        } else {
            self::$aSkipClasses = array_flip($aFQCN);
        }
        foreach (self::$aSkipClasses as $sFQCN => &$x) {
            if (isset(self::$aDependency[$sFQCN])) {
                unset(self::$aDependency[$sFQCN]);
                $x = true;
            }
            $x = false;
        }
        //cleanup previously skipped classes
        foreach (self::$aDependency as $sFQCN => $oSelf) {
            if ($oSelf->getType() === self::S && !isset(self::$aSkipClasses[$sFQCN])) {
                unset(self::$aDependency[$sFQCN]);
            }
        }
        return self::$aSkipClasses;
    }

    /**
     * @param array $aNamespaces
     * @param bool $bMergeWithExisting
     * @return array
     * @throws AfrException
     */
    public static function setSkipNamespaceInfo(array $aNamespaces, bool $bMergeWithExisting = false): array
    {
        if (!$bMergeWithExisting || !isset(self::$aSkipNamespaces)) {
            self::$aSkipNamespaces = []; //clear
        }
        foreach ($aNamespaces as &$sNs) {
            if (!is_string($sNs)) {
                throw new AfrException(
                    'Namespace must be a string! Please use an array of Namespaces in ' .
                    __CLASS__ . '::' . __FUNCTION__
                );
            }
            if ($sNs === '') {
                self::$aSkipNamespaces[''] = 0; //class without namespace
            } elseif ($sNs === '\\') {
                self::$aSkipNamespaces['\\'] = 1; //skip any class and namespace
            } else {
                self::$aSkipNamespaces[$sNs] = strlen($sNs);
            }
        }
        return self::$aSkipNamespaces;
    }

    /**
     * @param string $sFQCN
     * @param string $sType
     * @return static
     */
    private static function makeBlank(string $sFQCN, string $sType = ''): self
    {
        $oBlank = new static('');//blank init
        $oBlank->sFQCN = $sFQCN;
        if (!$sType) {
            $sType = __FUNCTION__;
        }
        $oBlank->sType = $sType;
        return $oBlank;
    }

    /**
     * @param string $sFQCN
     * @return bool
     */
    private static function mustSkipNamespaceInfoGatheringForClass(string $sFQCN): bool
    {
        if (!isset(self::$aSkipNamespaces) || empty(self::$aSkipNamespaces)) {
            return false; //no rules set
        }
        if (isset(self::$aSkipNamespaces['\\'])) {
            return true; //this will match any namespace
        }
        $sFQCN = trim($sFQCN, '\\');
        if (strpos($sFQCN, '\\') === false) { //class without namespace
            return isset(self::$aSkipNamespaces['']); //matching classes from the default blank namespace
        }

        $aClassNs = explode('\\', $sFQCN);
        array_pop($aClassNs); //clear class name
        $sClassNs = implode('\\', $aClassNs);

        if (strlen($sClassNs) < 1) {
            return false;
        }

        foreach (self::$aSkipNamespaces as $sSkipNs => $iSkipNsLength) {
            if (!$iSkipNsLength) {
                continue; //class without namespace
            }
            if ($sSkipNs === $sClassNs || rtrim($sSkipNs, '\\') === $sClassNs) {
                return true; //exact match
            } elseif (substr($sSkipNs, -1, 1) === '\\') {
                if (substr($sClassNs, 0, $iSkipNsLength) === $sSkipNs) {
                    return true; //class is contained into parent ns
                }
            }

        }
        return false;
    }

    /**
     * Instantiable methods:
     * Object or String Full Qualified Class Name
     * Everything else is cast to string and can fail!
     * @param $mClass
     */

    protected static array $aSkipClasses;
    protected static array $aSkipNamespaces;

    protected const C = 'class';
    protected const T = 'trait';
    protected const I = 'interface';
    protected const E = 'enum';
    protected const U = 'unknown';
    protected const S = 'skip';
    protected const F = 'fatal-error';

    protected string $sFQCN;
    protected string $sType;
    protected array $aParents;
    protected array $aTraits;
    protected array $aInterfaces;
    protected bool $bAbstract;
    protected bool $bInstantiable;
    protected bool $bSingleton;

    protected function __construct(string $sFQCN)
    {
        $this->sFQCN = $sFQCN;
        $this->sType = $this->getType();
        $this->detectComponents();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        if (isset($this->sType)) {
            return $this->sType;
        }
        $this->sType = self::U; //set as unknown until we find out what it is
        if (!$this->sFQCN) {
            return $this->sType;
        }
        if (class_exists($this->sFQCN)) {
            $this->sType = self::C;
        } elseif (trait_exists($this->sFQCN)) {
            $this->sType = self::T;
        } elseif (interface_exists($this->sFQCN)) {
            $this->sType = self::I;
        } elseif (PHP_VERSION_ID >= 81000 && enum_exists($this->sFQCN)) {
            $this->sType = self::E;
        }
        return $this->sType;
    }

    /**
     * @param string $sFQCN
     * @return void
     */
    private static function morphToSkipped(string $sFQCN): void
    {
        self::$aDependency[$sFQCN]->sType = self::S;
        self::$aDependency[$sFQCN]->aInterfaces =
        self::$aDependency[$sFQCN]->aTraits =
        self::$aDependency[$sFQCN]->aParents = [];
        unset(self::$aDependency[$sFQCN]->bAbstract);
        unset(self::$aDependency[$sFQCN]->bInstantiable);
        unset(self::$aDependency[$sFQCN]->bSingleton);
    }


    /**
     * @return array
     */
    public function getAllDependencies(): array
    {
        return array_merge($this->getParents(), $this->getTraits(), $this->getInterfaces());
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->sFQCN;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getClassName();
    }


    /** Get parent Classes
     * @return array
     */
    public function getParents(): array
    {
        if (!isset($this->aParents)) {
            return [];
        }
        return $this->aParents;
    }

    /** Get parent Traits
     * @return array
     */
    public function getTraits(): array
    {
        if (!isset($this->aTraits)) {
            return [];
        }
        return $this->aTraits;
    }

    /** Get parent Interfaces
     * @return array
     */
    public function getInterfaces(): array
    {
        if (!isset($this->aInterfaces)) {
            return [];
        }
        return $this->aInterfaces;
    }


    /**
     * @return bool
     */
    public function isClass(): bool
    {
        return $this->getType() === self::C;
    }

    /**
     * @return bool
     */
    public function isTrait(): bool
    {
        return $this->getType() === self::T;
    }

    /**
     * @return bool
     */
    public function isInterface(): bool
    {
        return $this->getType() === self::I;
    }

    /**
     * @return bool
     */
    public function isEnum(): bool
    {
        return $this->getType() === self::E;
        //return (new ReflectionClass($this->getClassName()))->isEnum();
    }

    /**
     * @return bool
     */
    public function isAbstract(): bool
    {
        if (isset($this->bAbstract)) {
            return $this->bAbstract;
        }
        if ($this->getType() !== self::C) { //only classes can be abstract
            return $this->bAbstract = false;
        }
        if (isset($this->bInstantiable) && $this->bInstantiable) { //abstract classes can't be instanced
            return $this->bAbstract = false;
        }
        try {
            return $this->bAbstract = (new ReflectionClass($this->getClassName()))->isAbstract();
        } catch (\ReflectionException $e) {
            return $this->bAbstract = false;
        }
    }

    /**
     * @return bool
     */
    public function isInstantiable(): bool
    {
        if (isset($this->bInstantiable)) {
            return $this->bInstantiable;
        }
        if ($this->getType() !== self::C) { //only classes can have instances
            return $this->bInstantiable = false;
        }
        if (isset($this->bAbstract) && $this->bAbstract) { //abstract classes can't have instances
            return $this->bInstantiable = false;
        }
        try {
            return $this->bInstantiable = (new ReflectionClass($this->getClassName()))->isInstantiable();
        } catch (\ReflectionException $e) {
            return $this->bInstantiable = false;
        }
    }


    /**
     * @return bool
     */
    public function isSingleton(): bool
    {
        if (isset($this->bSingleton)) {
            return $this->bSingleton;
        }
        if ($this->getType() !== self::C) {
            return $this->bSingleton = false;
        }
        try {
            return (
                method_exists($this->getClassName(), 'getInstance') &&
                ((new ReflectionMethod($this->getClassName(), 'getInstance'))->isStatic())
            );
        } catch (\ReflectionException $e) {
            return $this->bSingleton = false;
        }
    }

    /**
     * Object or String Fully Qualified Class Name
     * @param $mClass
     * @return bool
     */
    public function doIDependOn($mClass): bool
    {
        return isset($this->getAllDependencies()[is_object($mClass) ? get_class($mClass) : (string)$mClass]);
    }

    /**
     * @return void
     */
    private function detectComponents(): void
    {
        if (!$this->sFQCN || $this->sType === self::U) {
            return;
        }
        foreach ((array)class_uses($this->sFQCN) as $sTrait) {
            $oCompound = self::getClassInfo($sTrait);
            $this->aTraits[(string)$oCompound] = true;
            $this->detectTraits((string)$oCompound);
        }
        foreach ((array)class_implements($this->sFQCN) as $sInterface) {
            $oCompound = self::getClassInfo($sInterface);
            $this->aInterfaces[(string)$oCompound] = true;
        }
        foreach ((array)class_parents($this->sFQCN) as $sParent) {
            $oCompound = self::getClassInfo($sParent);
            $this->aParents[(string)$oCompound] = true;
            $this->detectTraits((string)$oCompound);
        }
        //https://www.php.net/manual/en/language.enumerations.php
        //if (PHP_VERSION_ID >= 81000 && $this->isEnum()) { new \ReflectionEnum($this->sFQCN); }
    }

    /**
     * class_uses will show only the traits that make up the current class, so we recursively get them
     * @param string $sFQCN
     * @return void
     */
    private function detectTraits(string $sFQCN): void
    {
        foreach (self::getClassInfo($sFQCN)->getTraits() as $sParentTrait => $nx) {
            if (!isset($this->aTraits[$sParentTrait])) {
                $this->aTraits[$sParentTrait] = true;
            }
        }
    }

}