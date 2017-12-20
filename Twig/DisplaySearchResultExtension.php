<?php

/*
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\SearchBundle\Twig;

class DisplaySearchResultExtension extends \Twig_Extension
{
    /**
     * @var array
     */
    private $blastSearchParameters;

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('displaySearchResult', [$this, 'displaySearchResult'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    public function displaySearchResult(\Twig_Environment $twig, $result, $asJson = false)
    {
        $resultClass = get_class($result);
        $template = $this->blastSearchParameters['default_result_template'];

        if (array_key_exists($resultClass, $this->blastSearchParameters['templates'])) {
            $template = $this->blastSearchParameters['templates'][$resultClass]['template'];
        }

        if ($asJson) {
            $template = str_replace('.html.twig', '.json.twig', $template);

            if (!$twig->getLoader()->exists($template)) {
                $template = str_replace('.html.twig', '.json.twig', $this->blastSearchParameters['default_result_template']);
            }
        }

        return $twig->render($template, ['result' => $result]);
    }

    /**
     * @param array $blastSearchParameters
     */
    public function setBlastSearchParameters(array $blastSearchParameters): void
    {
        $this->blastSearchParameters = $blastSearchParameters;
    }
}
