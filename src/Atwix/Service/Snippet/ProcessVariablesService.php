<?php
/**
 * This file is part of m2code-gen <https://github.com/roma-glushko/m2code-gen>
 *
 * @author Roman Glushko <https://github.com/roma-glushko>
 */

namespace Atwix\Service\Snippet;

use Atwix\System\VarRegistry;

/**
 * Class ProcessVariablesService
 */
class ProcessVariablesService
{
    /**
     * @param VarRegistry $variableRegistry
     *
     * @return array
     */
    public function execute(VarRegistry $variableRegistry)
    {
        $result = [];

        foreach ($variableRegistry->getAll() as $varName => $varValue) {
            $camelizedVarName = $this->camelize($varName);

            $result[$camelizedVarName] = $varValue;
        }

        // todo: decouple
        $moduleFullName = $result['moduleFullName'];
        $result['moduleNamespace'] = str_replace('_', '\\', $moduleFullName);

        $array1 = explode('_', $moduleFullName, -1);
        $result['vendorName'] = reset($array1);

        $array = explode('_', $moduleFullName);
        $result['moduleName'] = end($array);

        return $result;
    }

    /**
     * @param string $varName
     * @param string $separator
     *
     * @return string
     */
    protected function camelize(string $varName, string $separator = '-')
    {
        return lcfirst(str_replace($separator, '', ucwords($varName, $separator)));
    }
}