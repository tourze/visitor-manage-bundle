<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineFunctionBundle\DoctrineFunctionBundle;

class VisitorManageBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            DoctrineFunctionBundle::class => ['all' => true],
        ];
    }
}
