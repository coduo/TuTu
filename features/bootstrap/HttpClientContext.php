<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Testwork\Tester\Result\TestResult;

class HttpClientContext extends RawMinkContext implements SnippetAcceptingContext
{
    private $headersToRemove;

    public function __construct()
    {
        $this->headersToRemove = [];
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
    public function removeRemainingHeaders()
    {
        $client = $this->getSession()->getDriver()->getClient();
        foreach ($this->headersToRemove as $header) {
            $client->removeHeader($header);
        }
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
        if ($status !== $expectedStatus) {
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
    public function httpClientSendGetRequestOn($method, $url)
    {
        $session = $this->getSession();

        $client = $session->getDriver()->getClient();
        $client->followRedirects(false);
        $client->request($method, $url);
    }

    /**
     * @When http client sends :method request on :url with following parameters:
     */
    public function httpClientSendPostRequestOnWithFollowingParameters($method, $url, TableNode $parametersTable)
    {
        $session = $this->getSession();
        $parameters = [];
        foreach ($parametersTable->getHash() as $parameterData) {
            $parameters[$parameterData['Parameter']] = $parameterData['Value'];
        }
        $client = $session->getDriver()->getClient();
        $client->followRedirects(false);
        $client->request($method, $url, $parameters);
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

        $client->followRedirects(false);
        $client->request($method, $url, [], [], $headers);
    }

    /**
     * @When http client sends :method request on :url with body
     */
    public function httpClientSendGetRequestOnWithBody($method, $url, PyStringNode $body)
    {
        $session = $this->getSession();
        $client = $session->getDriver()->getClient();

        $client->followRedirects(false);
        $client->request($method, $url, [], [], [], (string) $body, (string) $body);
    }
}
