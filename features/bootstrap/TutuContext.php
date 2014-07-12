<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class TutuContext extends \Behat\MinkExtension\Context\RawMinkContext implements SnippetAcceptingContext
{
    /**
     * @var string
     */
    private $routingFilePath;

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

    public function __construct($webPath)
    {
        if (!file_exists($webPath)) {
            throw new \InvalidArgumentException(sprintf("Path %s does not exist", $webPath));
        }
        $this->webPath = $webPath;
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
     * @AfterScenario
     */
    public function removeResponsesConfiguration()
    {
        $fs = new Filesystem();
        if ($fs->exists($this->routingFilePath)) {
            $fs->remove($this->routingFilePath);
        }
    }

    /**
     * @Given there is a empy file "responses.yml"
     */
    public function thereIsAEmpyFile()
    {
        $this->thereIsARoutingFileWithFollowingContent(new PyStringNode([], 0));
    }

    /**
     * @Given there is a routing file "responses.yml" with following content:
     */
    public function thereIsARoutingFileWithFollowingContent(PyStringNode $fileContent)
    {
        $fs = new Filesystem();
        $this->routingFilePath = $this->webPath . '/../config/responses.yml';
        if ($fs->exists($this->routingFilePath)) {
            $fs->remove($this->routingFilePath);
        }

        $fs->dumpFile($this->routingFilePath, (string) $fileContent);
    }

    /**
     * @Given TuTu is running on host :host at port :port
     */
    public function tutuIsRunningOnHostAtPort($host, $port)
    {
        $this->tutuPort = $port;
        $this->killEveryProcessRunningOnTuTuPort();
        $builder = new ProcessBuilder([PHP_BINARY, '-S', sprintf('%s:%s', $host, $port)]);
        $builder->setWorkingDirectory($this->webPath);
        $builder->setTimeout(null);
        $this->tutuProcess = $builder->getProcess();
        $this->tutuProcess->start();
        sleep(1);
    }
}
