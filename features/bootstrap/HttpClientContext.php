<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Testwork\Tester\Result\TestResult;

class HttpClientContext extends RawMinkContext implements SnippetAcceptingContext
{

    /** @AfterScenario */
    public function printLastResponseWhenScenarioFail(AfterScenarioScope $scope)
    {
        if ($scope->getTestResult()->getResultCode() === TestResult::FAILED) {
            echo "Last response:\n";
            echo "Code " . $this->getSession()->getStatusCode() . "\n";
            $this->printLastResponse();
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
     * @Then response status code should be :arg1
     */
    public function responseStatusCodeShouldBe($expectedStatus)
    {
        $status = $this->getSession()->getStatusCode();
        if ($status !== (int) $expectedStatus) {
            throw new \RuntimeException(sprintf("Status %d is not equal to %d.", $status, (int) $expectedStatus));
        }
    }

    /**
     * @Then the response content should be equal to:
     */
    public function theResponseContentShouldBeEqualTo(PyStringNode $expectedContent)
    {
        $content = $this->getSession()->getDriver()->getContent();
        if ($content !== (string) $expectedContent) {
            throw new \RuntimeException("Content is different than expected.");
        }
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
     * @When http client send :method request on :arg1
     */
    public function httpClientSendGetRequestOn($method, $url)
    {
        $session = $this->getSession();

        $client = $session->getDriver()->getClient();
        $client->followRedirects(false);
        $client->request($method, $url);
    }

    /**
     * @When http client send :method request on :arg1 with following parameters:
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
}
