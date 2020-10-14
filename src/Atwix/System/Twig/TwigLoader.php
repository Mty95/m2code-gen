<?php
/**
 * This file is part of m2code-gen <https://github.com/roma-glushko/m2code-gen>
 *
 * @author Roman Glushko <https://github.com/roma-glushko>
 */

namespace Atwix\System\Twig;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Class TwigLoader
 */
class TwigLoader
{
    /**
     * @param string $templateDirPath
     *
     * @return Environment
     */
    public function load(string $templateDirPath): Environment
    {
        $twigLoader = new FilesystemLoader($templateDirPath);

        return new Environment($twigLoader);
    }
}