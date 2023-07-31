<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csvuploads
 *
 * @copyright   Copyright (C) NPEU 2023.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\HTML\Registry;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use NPEU\Component\Csvuploads\Administrator\Extension\CsvuploadsComponent;
#use NPEU\Component\Csvuploads\Administrator\Helper\AssociationsHelper;

use NPEU\Component\Csvuploads\Administrator\Service\Provider;

return new class implements ServiceProviderInterface {

    public function register(Container $container): void {

        $container->registerServiceProvider(new MVCFactory('\\NPEU\\Component\\Csvuploads'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\NPEU\\Component\\Csvuploads'));
        $container->registerServiceProvider(new RouterFactory('\\NPEU\\Component\\Csvuploads'));
        // Why doesn't the following work in conjunction with the 'use' above? I get 'class not found' but I can't tell the difference.
    /**$container->registerServiceProvider(new RouterFactoryProvider('\\NPEU\\Component\\Csvuploads'));*/
    /**/$container->registerServiceProvider(new \NPEU\Component\Csvuploads\Administrator\Service\Provider\RouterFactoryProvider('\\NPEU\\Component\\Csvuploads'));/**/

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                // Use the following instead if an Extension class isn't needed (i.e. it won't use
                // anything that class implements, like routing)
                #$component = new MVCComponent($container->get(ComponentDispatcherFactoryInterface::class));
                $component = new CsvuploadsComponent($container->get(ComponentDispatcherFactoryInterface::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setRegistry($container->get(Registry::class));
                $component->setRouterFactory($container->get(RouterFactoryInterface::class));
                $component->setDatabase($container->get(DatabaseInterface::class));


                return $component;
            }
        );
    }
};