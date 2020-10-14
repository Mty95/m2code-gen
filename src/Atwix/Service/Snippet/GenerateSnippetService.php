<?php
/**
 * This file is part of m2code-gen <https://github.com/roma-glushko/m2code-gen>
 *
 * @author Roman Glushko <https://github.com/roma-glushko>
 */

namespace Atwix\Service\Snippet;

use Atwix\Applier\ApplierInterface;
use Atwix\Processor\Snippet\TwigSnippetFileProcessor;
use Atwix\Service\Module\ResolveModulePathService;
use Atwix\System\Snippet\SnippetConfigLoader;
use Atwix\System\VarRegistry;
use Exception;
use Mty95\Helper\IOHelper;
use Mty95\Tasks\PropertiesTask;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GenerateSnippetService
 */
class GenerateSnippetService
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var SnippetConfigLoader
     */
    protected $snippetConfigLoader;

    /**
     * @var ResolveModulePathService
     */
    protected $resolveModulePathService;

    /**
     * @var TwigSnippetFileProcessor
     */
    protected $twigSnippetFileProcessor;

    /**
     * @var ProcessVariablesService
     */
    protected $processVariablesService;

    /**
     * @param ContainerInterface $container
     * @param SnippetConfigLoader $snippetConfigLoader
     * @param ResolveModulePathService $resolveModulePathService
     * @param TwigSnippetFileProcessor $twigSnippetFileProcessor
     * @param ProcessVariablesService $processVariablesService
     */
    public function __construct(
        ContainerInterface $container,
        SnippetConfigLoader $snippetConfigLoader,
        ResolveModulePathService $resolveModulePathService,
        TwigSnippetFileProcessor $twigSnippetFileProcessor,
        ProcessVariablesService $processVariablesService
    )
    {
        $this->snippetConfigLoader = $snippetConfigLoader;
        $this->resolveModulePathService = $resolveModulePathService;
        $this->container = $container;
        $this->twigSnippetFileProcessor = $twigSnippetFileProcessor;
        $this->processVariablesService = $processVariablesService;
    }

    /**
     * @param string $snippetName
     * @param VarRegistry $variableRegistry
     *
     * @param PropertiesTask $propertiesTask
     * @param IOHelper $io
     * @return void
     * @throws Exception
     */
    public function execute(string $snippetName, VarRegistry $variableRegistry, PropertiesTask $propertiesTask, IOHelper $io)
    {
        $moduleName = $variableRegistry->get('module-full-name');
        $moduleRootDir = $variableRegistry->get('module-root-dir');

        $modulePath = $this->resolveModulePathService->execute($moduleName, $moduleRootDir);
        $variables = $this->processVariablesService->execute($variableRegistry);

        $applierService = 'applier.copyFile';
        $snippetTemplatePath = $snippetName;
        $snippetFiles = $propertiesTask->getTemplateFilesHandler()->getTemplateFiles($snippetName);
        $templatesDirPath = $propertiesTask->getTemplateFilesHandler()->getTemplateFilesHelper()->getBaseTemplateDir();

        // Verificar todas la variables que sean required
        // {{\s*([^{]*{([^{]*):\s*(.*?)}.*?|[^{]*)\s*}}

        $templateVars = [];

        foreach ($snippetFiles as $file) {
            preg_match_all('/{+(.*?)}/', $file['content'], $matches);

            foreach ($matches[1] as $var) {
                $var = trim($var);

                if (strpos($var, '|') !== false)
                    continue;

                $templateVars[] = $var;
            }
        }

        $variablesKeys = array_keys($variables);

        // check if variable is not passed
        foreach ($templateVars as $var) {
            if (!in_array($var, $variablesKeys)) {
                $value = $io->ask(sprintf('Please write <options=bold>%s</>', $var), '');

                $variableRegistry->set($var, $value);
                $variables = $this->processVariablesService->execute($variableRegistry);
                $variablesKeys = array_keys($variables);
            }
        }

        foreach ($snippetFiles as $file) {
            $applier = $this->container->get($applierService);
            $snippetFileTemplatePath = str_replace($templatesDirPath, '', $file['template_path']);

            $renderedSnippetFileContent = $this->twigSnippetFileProcessor->process(
                $snippetFileTemplatePath,
                $variables
            );

            $snippetFileTemplatePath = str_replace($snippetName, '', $snippetFileTemplatePath);
            $snippetFileTemplatePath = str_replace('.twig', '', $snippetFileTemplatePath);

            $applier->apply(
                $modulePath,
                $snippetTemplatePath,
                $snippetFileTemplatePath,
                $renderedSnippetFileContent
            );

            $io->writeln(
                sprintf('Created file: %s', substr($snippetFileTemplatePath, 2))
            );
        }

        return;


        // apply snippet
        if (false) {
            $snippetConfig = $this->snippetConfigLoader->load($snippetName);

            $snippetFiles = $snippetConfig['files'] ?? [];
            $snippetTemplatePath = $snippetConfig['templatePath'] ?? null;

            // validate generating snippet
            foreach ($snippetFiles as $snippetFilePath => $snippetFileConfig) {

            }

            foreach ($snippetFiles as $snippetFilePath => $snippetFileConfig) {
                /** @var ApplierInterface $applier */
                $applier = $this->container->get($snippetFileConfig['applier']);
                $snippetFileTemplatePath = sprintf('%s/%s.twig', $snippetTemplatePath, $snippetFilePath);

                $renderedSnippetFileContent = $this->twigSnippetFileProcessor->process(
                    $snippetFileTemplatePath,
                    $variables
                );

                $applier->apply($modulePath, $snippetTemplatePath, $snippetFilePath, $renderedSnippetFileContent);
            }
        }
    }
}