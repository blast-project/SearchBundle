<?php

/*
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\SearchBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ResultsTemplatesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('twig') || !$container->hasParameter('blast_search')) {
            return;
        }

        $twig = $container->findDefinition('twig');

        $templates = $container->getParameter('blast_search')['templates'];

        $templateTwigVar = [];

        foreach ($templates as $class => $configData) {
            $templateTwigVar[$class] = $configData['template'];
        }

        $twig->addMethodCall('addGlobal', ['blast_search', $templateTwigVar]);
    }
}
