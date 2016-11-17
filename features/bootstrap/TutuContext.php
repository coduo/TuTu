<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Testwork\Tester\Result\TestResult;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class TutuContext extends RawMinkContext implements SnippetAcceptingContext
{
    const MAX_PHP_SERVER_RESTARTS = 5;

    /**
     * @var Process
     */
    private $tutuProcess;

    /**
     * @var int
     */
    private $tutuPort;

    /**
     * @var string;
     */
    private $tutuHost;

    /**
     * @var string
     */
    private $webPath;

    /**
     * @var string
     */
    private $workDir;

    /**
     * @var int
     */
    private $httpCallAttemptsPerScenario = 0;

    public function __construct($webPath)
    {
        if (!file_exists($webPath)) {
            throw new \InvalidArgumentException(sprintf("Path %s does not exist", $webPath));
        }
        $this->webPath = $webPath;
        $this->headersToRemove = [];
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
        $fs->dumpFile($this->workDir . '/config/responses.yml', '');
    }

    /** @AfterScenario */
    public function printLastResponseWhenScenarioFail(AfterScenarioScope $scope)
    {
        if ($scope->getTestResult()->getResultCode() === TestResult::FAILED) {
            echo "Last response:\n";
            echo "Code " . $this->getSession()->getStatusCode() . "\n";
            $this->printLastResponse();
        }
    }

    /** @AfterScenario */
    public function resetHttpCallAttempts()
    {
        $this->httpCallAttemptsPerScenario = 0;
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
     * @Given there is a :filePath file with following content
     */
    public function thereIsAFileWithFollowingContent($filePath, PyStringNode $fileContent)
    {
        $fs = new Filesystem();
        $fs->dumpFile($this->workDir . '/' . $filePath, (string) $fileContent);
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

        $content = (string) $fileContent;
        $content = str_replace('%workDir%', $this->workDir, $content);

        $fs->dumpFile($configFilePath, $content);
    }

    /**
     * @Given TuTu is running on host :host at port :port
     */
    public function tutuIsRunningOnHostAtPort($host, $port)
    {
        $this->tutuPort = $port;
        $this->tutuHost = $host;
        $this->killEveryProcessRunningOnTuTuPort();
        $builder = new ProcessBuilder([
            PHP_BINARY,
            '-S',
            sprintf('%s:%s > /Users/norzechowicz/Workspace/PHP/coduo/TuTu/log.txt', $host, $port)
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

    /**
     * @Then print last response
     */
    public function printLastResponse()
    {
        $content = $this->getSession()->getDriver()->getContent();

        echo "\n\033[36m|  " . strtr($content, array("\n" => "\n|  ")) . "\033[0m\n\n";
    }

    /**
     * @Then response status code should be :expectedStatus
     */
    public function responseStatusCodeShouldBe($expectedStatus)
    {
        $status = $this->getSession()->getStatusCode();
        if ($status !== (int)$expectedStatus) {
            throw new \RuntimeException(sprintf("Status %d is not equal to %d.", $status, (int) $expectedStatus));
        }
    }

    /**
     * @Then the response content should be equal to:
     */
    public function theResponseContentShouldBeEqualTo(PyStringNode $expectedContent)
    {
        $content = $this->getSession()->getDriver()->getContent();
        if ((string) $content !== (string) $expectedContent) {
            throw new \RuntimeException("Content is different than expected.");
        }
    }

    /**
     * @Then the response content should match expression:
     */
    public function theResponseContentShouldMatchExpression(PyStringNode $pattern)
    {
        $content = $this->getSession()->getDriver()->getContent();
        expect($content)->toMatch('/'.$pattern.'/');
    }

    /**
     * @Then the response content should be empty
     */
    public function theResponseContentShouldBeEmpty()
    {
        $content = $this->getSession()->getDriver()->getContent();
        if (!empty($content)) {
            throw new \RuntimeException("Content is not empty.");
        }
    }

    /**
     * @Then response should have following hedaers:
     */
    public function responseShouldHaveFollowingHedaers(TableNode $responseHeaders)
    {
        $headers = $this->getSession()->getResponseHeaders();

        foreach ($responseHeaders->getHash() as $header) {
            if (!array_key_exists($header['Name'], $headers)) {
                throw new \RuntimeException(sprintf("There is no \"%s\" header in response.", $header['Name']));
            }

            if ($headers[$header['Name']][0] !== $header['Value']) {
                throw new \RuntimeException(sprintf(
                    "Header \"%s\" value \"%s\" is not equal to \"%s\".",
                    $header['Name'],
                    $headers[$header['Name']][0],
                    $header['Value']
                ));
            }
        }
    }


    /**
     * @When http client sends :method request on :url
     */
    public function httpClientSendRequestOn($method, $url)
    {
        $this->makeHttpCall($method, $url);
    }

    /**
     * @When http client sends :method request on :url with following parameters:
     */
    public function httpClientSendPostRequestOnWithFollowingParameters($method, $url, TableNode $parametersTable)
    {
        $parameters = [];
        foreach ($parametersTable->getHash() as $parameterData) {
            $parameters[$parameterData['Parameter']] = $parameterData['Value'];
        }

        $this->makeHttpCall($method, $url, $parameters);
    }

    /**
     * @When http client sends :method request on :url with following headers
     */
    public function httpClientSendGetRequestOnWithFollowingHeaders($method, $url, TableNode $headersTable)
    {
        $session = $this->getSession();
        $client = $session->getDriver()->getClient();
        $headers = [];
        foreach ($headersTable->getHash() as $headerData) {
            $client->setHeader($headerData['Header'], $headerData['Value']);
            $headers[] = $headerData['Header'];
        }

        $this->makeHttpCall($method, $url, [], $headers);
    }

    /**
     * @When http client sends :method request on :url with body
     */
    public function httpClientSendGetRequestOnWithBody($method, $url, PyStringNode $body)
    {
        $this->makeHttpCall($method, $url, [], [], (string) $body);
    }

    /**
     * @param $method
     * @param $url
     * @param array $parameters
     * @param null $body
     */
    private function makeHttpCall($method, $url, array $parameters = [], array $headers = [], $body = null)
    {
        $session = $this->getSession();
        $client = $session->getDriver()->getClient();
        $client->followRedirects(false);
        try {
            $client->request(
                $method,
                $url,
                $parameters, // parameters
                [], // files
                $headers, // $_SERVER
                $body
            );
        } catch (RequestException $exception) {
            if (strpos($exception->getMessage(), 'cURL error 7') !== false) {
                if ($this->httpCallAttemptsPerScenario < self::MAX_PHP_SERVER_RESTARTS) {
                    $this->httpCallAttemptsPerScenario++;
                    $this->tutuIsRunningOnHostAtPort($this->tutuHost, $this->tutuPort);
                    $this->makeHttpCall($method, $url, $parameters, $headers, $body);
                    return ;
                }
            }

            throw $exception;
        }
    }
}
