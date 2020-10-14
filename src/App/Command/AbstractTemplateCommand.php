<?php
declare(strict_types=1);

namespace App\Command;

use Mty95\Helper\IOHelper;
use Mty95\Helper\MagentoHelper;
use Mty95\Tasks\PropertiesTask;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractTemplateCommand extends Command
{
    /**
     * @var IOHelper
     */
    protected IOHelper $io;
    /**
     * @var PropertiesTask
     */
    protected PropertiesTask $propertiesTask;
    /**
     * @var MagentoHelper
     */
    protected MagentoHelper $magentoHelper;

    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $this->magentoHelper = new MagentoHelper();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->io = new IOHelper($input, $output);
        $this->propertiesTask = new PropertiesTask($input);

        return parent::run($input, $output);
    }
}