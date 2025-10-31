<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Service;

use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouteCollection;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;
use Tourze\VisitorManageBundle\Controller\VisitorApprovalCrudController;
use Tourze\VisitorManageBundle\Controller\VisitorCrudController;
use Tourze\VisitorManageBundle\Controller\VisitorInvitationCrudController;
use Tourze\VisitorManageBundle\Controller\VisitorLogCrudController;
use Tourze\VisitorManageBundle\Controller\VisitorPassCrudController;

/**
 * 访客管理包的路由自动加载器
 */
#[AutoconfigureTag(name: 'routing.loader')]
class AttributeControllerLoader extends Loader implements RoutingAutoLoaderInterface
{
    private AttributeRouteControllerLoader $controllerLoader;

    private RouteCollection $collection;

    public function __construct()
    {
        parent::__construct();
        $this->controllerLoader = new AttributeRouteControllerLoader();

        $this->collection = new RouteCollection();

        $controllers = [
            VisitorApprovalCrudController::class,
            VisitorCrudController::class,
            VisitorInvitationCrudController::class,
            VisitorLogCrudController::class,
            VisitorPassCrudController::class,
        ];

        foreach ($controllers as $controller) {
            try {
                $routes = $this->controllerLoader->load($controller);
                $this->collection->addCollection($routes);
            } catch (\Throwable $e) {
                // 忽略加载失败的控制器，但在开发模式下可以记录错误
                $appEnv = $_ENV['APP_ENV'] ?? 'prod';
                if ('dev' === $appEnv) {
                    error_log("Failed to load routes for {$controller}: " . $e->getMessage());
                }
            }
        }
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        return $this->collection;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return false;
    }

    public function autoload(): RouteCollection
    {
        return $this->collection;
    }
}
