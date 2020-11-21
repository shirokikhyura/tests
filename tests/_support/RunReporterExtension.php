<?php

use Codeception\Events;
use Codeception\Event\FailEvent;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Predis\ClientInterface;

/**
 * Class RunReporterExtension
 */
class RunReporterExtension extends \Codeception\Extension
{
    public static $events = [
        Events::TEST_BEFORE => 'testBefore',
        Events::TEST_FAIL => 'testFailed',
        Events::TEST_SUCCESS => 'testSuccess',
        Events::TEST_ERROR => 'testError',
        Events::STEP_AFTER => 'stepAfter',
        Events::STEP_BEFORE => 'stepBefore',
        Events::TEST_FAIL_PRINT => 'testFailPrint',
    ];

    protected $steps = [];

    protected $currentStep = [];

    protected $lastMetaStepKey = [];

    protected $runId;

    protected $metaStep;

    protected $testResult;

    protected $testStartTime;

    protected $testEndTime;

    protected $testFailMessage;

    private $client;

    /**
     * @var array
     */
    protected static $artifactsForPush = [];

    /**
     * @var \Codeception\Lib\Console\MessageFactory
     */
    protected $messageFactory;

    public function __construct($config, $options)
    {
        parent::__construct($config, $options);

        if (empty($config['runId'])) {
            throw new \RuntimeException("'Can not enable RunReporterExtension. Missing required config parameter 'runId'");
        }

        $this->runId = $config['runId'];
        $this->messageFactory = new \Codeception\Lib\Console\MessageFactory($this->output);
    }

    public function testBefore(\Codeception\Event\TestEvent $e)
    {
        $this->testStartTime = microtime(true);
        $this->steps = [];
        $this->lastMetaStepKey = 0;
    }

    public function testFailed(\Codeception\Event\FailEvent $e)
    {
        if (count($this->steps) > 0) {
            $reason = 'Can not reproduce step - ' . end($this->steps)['step'];
        } else {
            $reason = 'Can not reproduce any step';
        }

        $testClass = $this->getTestClassFromTestFile($e->getTest()->getMetadata()->getFilename());

        $result = [
            'result' => 'failed',
            'reason' => $reason,
            'exceptionMessage' => $e->getFail()->getMessage(),
            'steps' => $this->steps,
            'testClass' => $testClass,
            'execTime' => microtime(true) - $this->testStartTime,
        ];

        if (strpos($result['exceptionMessage'], 'QA API') !== false) {
            $result['result'] = 'broken';
        }

        $this->pushResultToRunner($result);
    }

    public function testError(\Codeception\Event\FailEvent $e)
    {
        if (count($this->steps) > 0) {
            $reason = 'Can not reproduce step - ' . end($this->steps)['step'];
        } else {
            $reason = 'Can not reproduce any step';
        }

        $testClass = $this->getTestClassFromTestFile($e->getTest()->getMetadata()->getFilename());

        $result = [
            'result' => 'broken',
            'reason' => $reason,
            'exceptionMessage' => $e->getFail()->getMessage(),
            'steps' => $this->steps,
            'testClass' => $testClass,
            'execTime' => microtime(true) - $this->testStartTime,
        ];

        $this->pushResultToRunner($result);
    }

    public function testSuccess(\Codeception\Event\TestEvent $e)
    {
        $testClass = $this->getTestClassFromTestFile($e->getTest()->getMetadata()->getFilename());

        $result = [
            'result' => 'passed',
            'steps' => $this->steps,
            'testClass' => $testClass,
            'execTime' => microtime(true) - $this->testStartTime,
        ];

        $this->pushResultToRunner($result);
    }

    public function stepAfter(\Codeception\Event\StepEvent $e)
    {
        $stepName = $e->getStep()->getHumanizedActionWithoutArguments();
        $filename = preg_replace(
            '~\W~',
            '.',
            \Codeception\Test\Descriptor::getTestSignatureUnique($e->getTest())
        );
        $outputDir = codecept_output_dir();
        if (strpos($stepName, 'don\'t see visual changes') !== false && $e->getStep()->hasFailed()) {
            $dataDir = codecept_data_dir();

            $arguments = $e->getStep()->getArguments();
            $this->writeln("# Arguments: " . $filename);
            $this->writeln(var_export($arguments, true));
            $humanName = $arguments[0];
            $referencePng = mb_strcut($filename, 0, 245, 'utf-8') . '.' . $humanName . '.png';
            $currentPng = mb_strcut($filename, 0, 245, 'utf-8') . '.' . $humanName . '.png';
            $diffPng = 'compare' . mb_strcut($filename, 0, 245, 'utf-8') . '.' . $humanName . '.png';

            $reportReferencePngPath = $dataDir . 'VisualCeption/' . $referencePng;
            $reportCurrentPngPath = $outputDir . 'debug/visual/' . $currentPng;
            $reportDiffPngPath = $outputDir . 'debug/' . $diffPng;

            $newReferencePng = $this->pushArtifactsToRunner($reportReferencePngPath, 'reference');
            $newCurrentPngPath = $this->pushArtifactsToRunner($reportCurrentPngPath, 'current');
            $this->pushArtifactsToRunner($reportDiffPngPath);
            $this->currentStep['artifacts']['reference'] = $newReferencePng;
            $this->currentStep['artifacts']['current'] = $newCurrentPngPath;
            $this->currentStep['artifacts']['diff'] = (file_exists($reportDiffPngPath)) ? $diffPng : "";
        }

        $this->currentStep['execTime'] = microtime(true) - $this->currentStep['startTime'];
        $this->currentStep['result'] = $e->getStep()->hasFailed() ? 'failed' : 'passed';

        if ($this->currentStep['isMetaStep'] == 0) {
            if ($e->getStep()->hasFailed()) {
                $reportPng = mb_strcut($filename, 0, 245, 'utf-8') . '.fail.png';
                $reportPngPath = $outputDir . $reportPng;
                $reportHtml = mb_strcut($filename, 0, 244, 'utf-8') . '.fail.html';
                $reportHtmlPath = $outputDir . $reportHtml;

                self::$artifactsForPush[] = $reportPngPath;
                self::$artifactsForPush[] = $reportHtmlPath;

                $this->steps[$this->lastMetaStepKey]['result'] = 'failed';
                $this->currentStep['artifacts']['png'] = $reportPng;
                $this->currentStep['artifacts']['html'] = $reportHtml;
            }

            $this->steps[$this->lastMetaStepKey]['steps'][] = $this->currentStep;

        } else {
            $this->steps[] = $this->currentStep;
        }
    }

    public function stepBefore(\Codeception\Event\StepEvent $e)
    {
        $metaStep = $e->getStep()->getMetaStep();

        if ($metaStep && $this->metaStep != $metaStep) {

            $stepMessage = ' ' . $metaStep->getPrefix() . ' ' . $metaStep->__toString();

            // Проставим предыдущему metaStep execTime
            if ($this->lastMetaStepKey) {
                $this->steps[$this->lastMetaStepKey]['execTime'] = microtime(true)
                    - $this->steps[$this->lastMetaStepKey]['startTime'];
            }

            $this->steps[] = [
                'step' => $stepMessage,
                'result' => $e->getStep()->hasFailed() ? 'failed' : 'passed',
                'isMetaStep' => 1,
                'startTime' => microtime(true),
                'steps' => [],
            ];

            $this->lastMetaStepKey = count($this->steps) - 1;
        }

        $this->metaStep = $metaStep;

        $step = $e->getStep();
        if ($step instanceof \Codeception\Step\Comment and $step->__toString() == '') {
            return;
        }

        $stepMessage = ' ';
        $isMetaStep = 1;
        if ($this->metaStep) {
            $stepMessage .= '  ';
            $isMetaStep = 0;
        }
        $stepMessage .= $step->getPrefix();
        $stepMessage .= $step->toString(1000);

        // Проставим предыдущему metaStep execTime
        if ($isMetaStep && $this->lastMetaStepKey) {
            $this->steps[$this->lastMetaStepKey]['execTime'] = microtime(true)
                - $this->steps[$this->lastMetaStepKey]['startTime'];
        }

        $this->currentStep = [
            'step' => $stepMessage,
            'result' => $e->getStep()->hasFailed() ? 'failed' : 'passed',
            'isMetaStep' => $isMetaStep,
            'startTime' => microtime(true),
        ];

        if ($isMetaStep) {
            $this->lastMetaStepKey = count($this->steps);
        }
    }

    public function testFailPrint(FailEvent $event)
    {
        foreach (self::$artifactsForPush as $artifact) {
            $this->pushArtifactsToRunner($artifact);
        }

        self::$artifactsForPush = [];
    }

    private function pushArtifactsToRunner($filePath, $type = '')
    {
        $this->writeln(" ");
        $this->messageFactory->message('Send artifacts --')->style('comment')->writeln();
        $newPath = basename($filePath);

        $post = [
            'data' => base64_encode(file_get_contents($filePath)),
            'name' => $newPath,
        ];
        $response = $this->pushToRunner('/test-run/result/artifacts/' . $this->runId, $post);
        $this->writeln("#Send artifact status: " . $response->getStatusCode());

        return $newPath;
    }

    private function pushResultToRunner(array $result)
    {
        $post = [
            'automatedClass' => $result['testClass'],
            'result' => $result['result'],
            'resultMessage' => isset($result['exceptionMessage']) ? $result['exceptionMessage'] : '',
            'steps' => $result['steps'],
            'execTime' => $result['execTime'],
        ];
        $this->writeln("# Send result to runner");
        $this->writeln(json_encode($post));

        $response = $this->pushToRunner('/test-run/result/' . $this->runId, $post);
        $this->writeln("# Send status: " . $response->getStatusCode());
    }

    private function pushToRunner($uri, array $post)
    {
        $hostSite = getenv('APP_ENV') == 'development' ? 'http://runner.nginx' : 'http://runner.nginx';
        $url = $hostSite . $uri;

        $config = array(
            'adapter' => 'Zend\Http\Client\Adapter\Curl',
            'curloptions' => array(
                CURLOPT_TIMEOUT_MS => 60 * 10000,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ),
        );

        $client = new \Zend\Http\Client($url, $config);
        $client->setRawBody(json_encode($post));
        $client->setMethod('POST');
        $client->setHeaders([
            'Content-Type' => 'application/json',
        ]);

        return $client->send();
    }

    /**
     * @param string $fileName
     * @return mixed|string
     */
    private function getTestClassFromTestFile($fileName)
    {
        if (strpos($fileName, 'tests/acceptance/') !== false) {
            $testDir = 'tests/acceptance/';
        } else {
            $testDir = 'tests/ui/';
        }

        $testClass = strstr($fileName, $testDir);
        $testClass = str_replace($testDir, '', $testClass);
//        $testClass = str_replace('.php', '', $testClass);
        $testClass = str_replace('/', '\\', $testClass);
        return $testClass;
    }
}
