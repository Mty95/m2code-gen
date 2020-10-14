<?php
declare(strict_types=1);

namespace App\Command\Module;

use App\Command\AbstractTemplateCommand;
use Atwix\Service\Snippet\GenerateSnippetService;
use Atwix\System\VarRegistry;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateModuleCommand extends AbstractTemplateCommand
{
    public const NAME = 'module:new';
    public const ARGUMENT_MODULE_NAME = 'module-name';
    /**
     * @var GenerateSnippetService
     */
    private GenerateSnippetService $generateSnippetService;
    /**
     * @var VarRegistry
     */
    private VarRegistry $varRegistry;

    public function __construct(
        GenerateSnippetService $generateSnippetService,
        VarRegistry $varRegistry,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->generateSnippetService = $generateSnippetService;
        $this->varRegistry = $varRegistry;
    }

    protected function configure()
    {
        $this->setName(self::NAME);
        $this->setDescription('Create a new Magento 2 module');
        $this->addUsage(self::NAME . ' Mty95_SampleModule');

        $this->addArgument(
            self::ARGUMENT_MODULE_NAME,
            InputArgument::REQUIRED,
            'Module Name <info>(format: Vendor_Module)</info>'
        );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->beforeExecute();

        $moduleName = $input->getArgument(self::ARGUMENT_MODULE_NAME);

        $this->varRegistry->set('module-full-name', $moduleName);
        $this->generateSnippetService->execute('empty/module', $this->varRegistry, $this->propertiesTask, $this->io);

        $output->writeln(sprintf('✅ <info>%s</info> module has been created', $moduleName));

        return 1;
    }

    public function beforeExecute()
    {

    }
}