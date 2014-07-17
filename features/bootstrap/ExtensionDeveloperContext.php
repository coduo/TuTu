<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Filesystem\Filesystem;

class ExtensionDeveloperContext implements SnippetAcceptingContext
{
    /**
     * @var string
     */
    private $workDir;

    public function __construct()
    {

    }

    /**
     * @BeforeScenario
     */
    public function createWorkDir()
    {
        $this->workDir = sprintf(
            '%s/%s',
            sys_get_temp_dir(),
            uniqid('ExtensionDeveloperContext')
        );

        $fs = new Filesystem();
        $fs->mkdir($this->workDir, 0777);
        chdir($this->workDir);
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
     * @Given there is a :filePath file with following content
     */
    public function thereIsAFileWithFollowingContent($filePath, PyStringNode $fileContent)
    {
        $fs = new Filesystem();
        $fs->dumpFile($this->workDir . '/' . $filePath, (string) $fileContent);

        require_once $this->workDir . '/' . $filePath;
    }
}


