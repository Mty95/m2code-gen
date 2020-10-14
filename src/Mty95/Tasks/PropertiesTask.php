<?php
declare(strict_types=1);

namespace Mty95\Tasks;

use Staempfli\UniversalGenerator\Handler\TemplateFilesHandler;
use Staempfli\UniversalGenerator\Helper\Files\ApplicationFilesHelper;
use Staempfli\UniversalGenerator\Helper\PropertiesHelper;
use Symfony\Component\Console\Input\InputInterface;

final class PropertiesTask
{
    /**
     * @var InputInterface
     */
    private InputInterface $input;
    /**
     * @var PropertiesHelper
     */
    private PropertiesHelper $propertiesHelper;
    /**
     * @var ApplicationFilesHelper
     */
    private ApplicationFilesHelper $applicationFilesHelper;
    /**
     * @var TemplateFilesHandler
     */
    private TemplateFilesHandler $templateFilesHandler;

    public function __construct(InputInterface $input)
    {
        $this->input = $input;

        $this->propertiesHelper = new PropertiesHelper();
        $this->applicationFilesHelper = new ApplicationFilesHelper();
        $this->templateFilesHandler = new TemplateFilesHandler();
    }

    /**
     * @return TemplateFilesHandler
     */
    public function getTemplateFilesHandler(): TemplateFilesHandler
    {
        return $this->templateFilesHandler;
    }
}