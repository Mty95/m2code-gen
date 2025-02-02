<?php
/**
 * PropertiesTask
 *
 * @copyright Copyright (c) 2016 Staempfli AG
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\UniversalGenerator\Tasks;

use Staempfli\UniversalGenerator\Handler\TemplateFilesHandler;
use Staempfli\UniversalGenerator\Helper\Files\ApplicationFilesHelper;
use Staempfli\UniversalGenerator\Helper\IOHelper;
use Staempfli\UniversalGenerator\Helper\PropertiesHelper;
use Symfony\Component\Yaml\Yaml;

class PropertiesTask
{
    /**
     * @var array
     */
    protected $properties = [];
    /**
     * @var string
     */
    protected $defaultPropertiesFilename = 'config/default-properties.yml';
    /**
     * @var PropertiesHelper
     */
    protected $propertiesHelper;
    /**
     * @var IOHelper
     */
    protected $io;
    /**
     * @var ApplicationFilesHelper
     */
    protected $applicationFilesHelper;
    /**
     * @var TemplateFilesHandler
     */
    protected $templateFilesHandler;

    /**
     * @param IOHelper $io
     */
    public function __construct(IOHelper $io)
    {
        $this->io = $io;
        $this->propertiesHelper = new PropertiesHelper();
        $this->applicationFilesHelper = new ApplicationFilesHelper();
        $this->templateFilesHandler = new TemplateFilesHandler();
    }

    /**
     * @param string $property
     * @param string $value
     */
    public function setProperty($property, $value, bool $canSaveUpperAndLower = true)
    {
        if ($canSaveUpperAndLower) {
            $propertyUcFirst = ucfirst($property);
            $valueUcFirst = ucfirst($value);
            $this->properties[$propertyUcFirst] = $valueUcFirst;

            $propertyLcFirst = lcfirst($property);
            $valueLowerCase = strtolower($value);
            $this->properties[$propertyLcFirst] = $valueLowerCase;

            return;
        }

        $this->setSingleProperty($property, $value);
    }

    /**
     * @param string $property
     * @param string $value
     */
    public function setSingleProperty($property, $value)
    {
        $this->properties[$property] = $value;
    }
    
    /**
     * @param string $property
     * @param string $value
     */
    public function removeProperty($property)
    {
        unset($this->properties[ucfirst($property)]);
        unset($this->properties[lcfirst($property)]);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getKeyValue(string $key)
    {
        return $this->properties[$key];
    }

    /**
     * format must be an array like ['propertyName', 'propertyValue']
     *
     * @param array $properties
     */
    public function addProperties(array $properties)
    {
        foreach ($properties as $property => $value) {
            $this->setProperty($property, $value);
        }
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return string
     */
    public function getDefaultPropertiesFile()
    {
        $configFilename = pathinfo($this->applicationFilesHelper->getApplicationFileName(), PATHINFO_FILENAME);
        return $this->applicationFilesHelper->getUsersHome() . '/.' . $configFilename . '/' . $this->defaultPropertiesFilename;
    }

    /**
     * @return bool
     */
    public function defaultPropertiesExist()
    {
        if (file_exists($this->getDefaultPropertiesFile())) {
            return true;
        }
        return false;
    }

    /**
     * @throws \Exception
     */
    public function setDefaultPropertiesConfigurationFile()
    {
        $originalPropertiesFilename = $this->applicationFilesHelper->getProjectBaseDir() . '/' . $this->defaultPropertiesFilename;
        $originalProperties = Yaml::parse(file_get_contents($originalPropertiesFilename));

        $defaultProperties = [];
        foreach ($originalProperties as $property => $value) {
            if (!$value) {
                $defaultProperties[$property] = $this->io->ask($property);
            }
        }
        $this->checkAndCreateUserConfigDir();
        file_put_contents($this->getDefaultPropertiesFile(), Yaml::dump($defaultProperties));
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function checkAndCreateUserConfigDir()
    {
        $userConfigDir = dirname($this->getDefaultPropertiesFile());
        if (!is_dir($userConfigDir)) {
            if (!mkdir($userConfigDir, 0766, true)) {
                throw new \Exception('Not possible to create user\'s configuration file: '. $this->getDefaultPropertiesFile());
            }
        }
        return true;
    }

    public function loadDefaultProperties()
    {
        $defaultProperties = Yaml::parse(file_get_contents($this->getDefaultPropertiesFile()));
        $this->addProperties($defaultProperties);
    }

    public function displayLoadedProperties()
    {
        $defaultProperties = $this->getProperties();
        $ioTableContent = [];
        foreach ($defaultProperties as $property => $value) {
            $ioTableContent[] = [$property, $value];
        }

        $this->io->table(['property', 'value'], $ioTableContent);
    }

    /**
     * @param string $templateName
     */
    public function askAndSetInputPropertiesForTemplate($templateName)
    {
        $templateProperties = $this->getAllPropertiesInTemplate($templateName);
        $propertiesAlreadyAsked = [];

        foreach ($templateProperties as $property) {
            if ($this->shouldAskForProperty($property, $propertiesAlreadyAsked)) {
                $value = $this->io->ask(sprintf('Please write <options=bold>%s</>', $property));
                $this->setProperty($property, $value);
                $propertiesAlreadyAsked[] = $property;
            }
        }
    }

    /**
     * @param string $templateName
     * @return array
     */
    protected function getAllPropertiesInTemplate($templateName)
    {
        $templateFiles = $this->templateFilesHandler->getTemplateFiles($templateName);
        $propertiesInTemplate = [];

        foreach ($templateFiles as $file) {

            $propertiesInFilename = $this->propertiesHelper->getPropertiesInText($file['path']);
            $propertiesInCode = $this->propertiesHelper->getPropertiesInText($file['content']);
            $propertiesInTemplate = array_merge($propertiesInTemplate, $propertiesInFilename, $propertiesInCode);
        }
        return $propertiesInTemplate;
    }

    /**
     * @param string $property
     * @param array $propertiesAlreadyAsked
     * @return bool
     */
    protected function shouldAskForProperty($property, array $propertiesAlreadyAsked)
    {
        if (in_array(strtolower($property), array_map('strtolower', $propertiesAlreadyAsked))) {
            return false;
        }
        if (in_array(strtolower($property), array_map('strtolower', array_keys($this->properties)))) {
            return false;
        }
        return true;
    }

    /**
     * @return TemplateFilesHandler
     */
    public function getTemplateFilesHandler()
    {
        return $this->templateFilesHandler;
    }
}