<?php

namespace Blast\Bundle\SearchBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Blast\Bundle\SearchBundle\DependencyInjection\ResultsTemplatesCompilerPass;

class BlastSearchBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ResultsTemplatesCompilerPass());
    }
}
