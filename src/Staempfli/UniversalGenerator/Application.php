<?php
/**
 * Application
 *
 * @copyright Copyright (c) 2016 Staempfli AG
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\UniversalGenerator;

use Staempfli\UniversalGenerator\Helper\Files\ApplicationFilesHelper;
use Symfony\Component\Console\Application as SymfonyConsoleApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends SymfonyConsoleApplication
{
    /**
     * @var array
     */
    protected $generatorCommands = [
        'config:set' => 'Staempfli\UniversalGenerator\Command\Config\ConfigSetCommand',
        'config:display' => 'Staempfli\UniversalGenerator\Command\Config\ConfigDisplayCommand',
        'config:unset' => 'Staempfli\UniversalGenerator\Command\Config\ConfigUnsetCommand',
        'template:list' => 'Staempfli\UniversalGenerator\Command\Template\TemplateListCommand',
        'template:info' => 'Staempfli\UniversalGenerator\Command\Template\TemplateInfoCommand',
        'template:generate' => 'Staempfli\UniversalGenerator\Command\Template\TemplateGenerateCommand',

        'setup:gen' => \Mty95\Mg2CodeGenerator\Command\SetupFolderCommand::class,
        'schema:run' => \Mty95\Mg2CodeGenerator\Command\LoadSchemaFileCommand::class,
        'zip:module' => \Mty95\Mg2CodeGenerator\Command\ZipModule::class,
        'phpstan' => \Mty95\Mg2CodeGenerator\Command\PhpStan::class,
    ];

    /**
     * @param string $version
     */
    public function __construct($version = 'UNKNOWN')
    {
        $applicationFilesHelper = new ApplicationFilesHelper();
        $applicationName = $applicationFilesHelper->getApplicationFileName();

        parent::__construct($applicationName, $version);
    }

    /**
     * @param string $name
     * @param sting $class
     */
    public function addGeneratorCommand($name, $class)
    {
        $this->generatorCommands[$name] = $class;
    }

    /**
     * Load generator commands
     */
    protected function loadGeneratorCommands()
    {
        foreach ($this->generatorCommands as $name => $class) {
            $parsedClass = '\\' . trim($class, '\\');
            $this->add(new $parsedClass($name));
        }
    }

    /**
     * @param InputInterface|null $input
     * @param OutputInterface|null $output
     * @return int
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->loadGeneratorCommands();
        return parent::run($input, $output);
    }
}