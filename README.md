# Autoframe is a low level framework that is oriented on SOLID flexibility

[![Build Status](https://github.com/autoframe/class-dependency/workflows/PHPUnit-tests/badge.svg?branch=main)](https://github.com/autoframe/class-dependency/actions?query=branch:main)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
![Packagist Version](https://img.shields.io/packagist/v/autoframe/class-dependency?label=packagist%20stable)
[![Downloads](https://img.shields.io/packagist/dm/autoframe/class-dependency.svg)](https://packagist.org/packages/autoframe/class-dependency)

*Autoframe PHP dependency resolver for classes, interfaces and traits using static reflection and all in one class*

Namespace:
- Autoframe\\ClassDependency

Class: AfrClassDependency

Static methods:
- public static function getClassInfo(string $sFQCN): AfrClassDependency;
- public static function clearClassInfo(string $sFQCN): bool;
- public static function getDependencyInfo(): array;
- public static function clearDependencyInfo(): void;
- public static function getDebugFatalError(): array;
- public static function clearDebugFatalError(): void;
- public static function setSkipClassInfo(array $aFQCN): void;
- public static function setSkipNamespaceInfo(array $aNamespaces): void;

Instance methods:
- public function getType(): string;
- public function __toString(): string;
- public function iDependOnThis($mClass): bool;
- public function getAllDependencies(): array;
- public function getClassName(): string;
- public function getParents(): array;
- public function getTraits(): array;
- public function getInterfaces(): array;
- public function isClass(): bool;
- public function isTrait(): bool;
- public function isInterface(): bool;
- public function isEnum(): bool;
- public function isAbstract(): bool;
- public function isInstantiable(): bool;
- public function isSingleton(): bool;

Instance methods are available using getClassInfo(...): AfrClassDependency
