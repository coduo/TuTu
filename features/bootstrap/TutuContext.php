<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class TutuContext extends RawMinkContext implements SnippetAcceptingContext
{
    /**
     * @var Process
     */
    private $tutuProcess;

    /**
     * @var int
     */
    private $tutuPort;

    /**
     * @var string
     */
    private $webPath;

    /**
     * @var string
     */
    private $workDir;

    public function __construct($webPath)
    {
        if (!file_exists($webPath)) {
            throw new \InvalidArgumentException(sprintf("Path %s does not exist", $webPath));
        }
        $this->webPath = $webPath;
    }

    /**
     * @BeforeScenario
     */
    public function createWorkDir()
    {
        $this->workDir = sprintf(
            '%s/%s',
            sys_get_temp_dir(),
            uniqid('TuTuContext')
        );
        $fs = new Filesystem();
        $fs->mkdir($this->workDir, 0777);
        chdir($this->workDir);
        $fs->mkdir($this->workDir . '/resources');
        $fs->mkdir($this->workDir . '/config');
    }

    /**
     * @AfterScenario
     */
    public function removeWorkDir()
    {
        $fs = new Filesystem();
        $fs->remove($this->workDir);
    }

    /**
     * @AfterScenario
     */
    public function killEveryProcessRunningOnTuTuPort()
    {
        $killerProcess = new Process(sprintf("kill $(lsof -t -i:%d)", (int) $this->tutuPort));
        $killerProcess->run();
    }

    /**
     * @Given there is a empty responses config file :fileName
     */
    public function thereIsAEmptyFile($fileName)
    {
        $this->thereIsARoutingFileWithFollowingContent($fileName, new PyStringNode([], 0));
    }

    /**
     * @Given there is a resource file :fileName with following content
     */
    public function thereIsAResourceFileWithFollowingContent($fileName, PyStringNode $fileContent)
    {
        $fs = new Filesystem();
        $resourceFilePath = $this->workDir . '/resources/' . $fileName;
        if ($fs->exists($resourceFilePath)) {
            $fs->remove($resourceFilePath);
        }

        $fs->dumpFile($resourceFilePath, (string) $fileContent);
    }

    /**
     * @Given there is a routing file :fileName with following content:
     * @Given there is a responses config file :fileName with following content:
     * @Given there is a config file :fileName with following content:
     */
    public function thereIsARoutingFileWithFollowingContent($fileName, PyStringNode $fileContent)
    {
        $fs = new Filesystem();
        $configFilePath = $this->workDir . '/config/' . $fileName;
        if ($fs->exists($configFilePath)) {
            $fs->remove($configFilePath);
        }

        $fs->dumpFile($configFilePath, (string) $fileContent);
    }

    /**
     * @Given TuTu is running on host :host at port :port
     */
    public function tutuIsRunningOnHostAtPort($host, $port)
    {
        $this->tutuPort = $port;
        $this->killEveryProcessRunningOnTuTuPort();
        $builder = new ProcessBuilder([
            PHP_BINARY,
            '-S',
            sprintf('%s:%s', $host, $port)
        ]);
        if (file_exists($this->workDir . '/config/config.yml')) {
            $builder->setEnv('tutu_config', $this->workDir . '/config/config.yml');
        }

        $builder->setEnv('tutu_responses', $this->workDir . '/config/responses.yml');
        $builder->setEnv('tutu_resources', $this->workDir . '/resources');
        $builder->setWorkingDirectory($this->webPath);
        $builder->setTimeout(null);
        $this->tutuProcess = $builder->getProcess();
        $this->tutuProcess->start();
        sleep(1);
    }
}
