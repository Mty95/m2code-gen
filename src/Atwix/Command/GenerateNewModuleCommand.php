<?php
/**
 * This file is part of m2code-gen <https://github.com/roma-glushko/m2code-gen>
 *
 * @author Roman Glushko <https://github.com/roma-glushko>
 */

namespace Atwix\Command;

use Atwix\Service\Snippet\GenerateSnippetService;
use Atwix\System\VarRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateNewModuleCommand
 */
class GenerateNewModuleCommand extends Command
{
    const SNIPPET_NAME = 'module-new';

    /**
     * @var string
     */
    protected static $defaultName = 'module:old';

    /**
     * @var VarRegistry
     */
    protected $varRegistry;

    /**
     * @var GenerateSnippetService
     */
    protected $generateSnippetService;

    /**
     * @param GenerateSnippetService $generateSnippetService
     * @param VarRegistry $varRegistry
     * @param null|string $name
     */
    public function __construct(
        GenerateSnippetService $generateSnippetService,
        VarRegistry $varRegistry,
        ?string $name = null
    ) {
        parent::__construct($name);

        $this->varRegistry = $varRegistry;
        $this->generateSnippetService = $generateSnippetService;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('module:old');
        $this->setDescription('Create a new Magento 2 module');

        $this->addArgument(
            'module-name',
            InputArgument::REQUIRED,
            'Module Name <info>(format: Vendor_Module)</info>'
        );
        $this->addArgument(
            'module-root-dir',
            InputArgument::OPTIONAL,
            'Path to module directory',
            'app/code/'
        );

        $this->addUsage('module:old Atwix_OrderComment');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $moduleName = $input->getArgument('module-name');

        $this->varRegistry->set('module-full-name', $moduleName);
        $this->varRegistry->set('module-root-dir', $input->getArgument('module-root-dir'));

        $this->generateSnippetService->execute(static::SNIPPET_NAME, $this->varRegistry);

        $output->writeln(sprintf('✅ <info>%s</info> module has been created', $moduleName));
    }
}
