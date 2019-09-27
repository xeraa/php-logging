<?php

require_once __DIR__."/vendor/autoload.php";

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Monolog\Formatter\LogstashFormatter;
use Monolog\ElasticLogstashHandler;
use Monolog\Formatter\JsonFormatter;



// Create the logger
$logger = new Logger('app_logger');

// Create the log line handler
// The default date format is "Y-m-d H:i:s"
$dateFormat = "Y-m-d H:i:s";

// The default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
$output = "[%datetime%] %extra% %channel%.%level_name%: %message% %context%\n";

// Create the log file handler
$lineFormatter = new LineFormatter($output, $dateFormat, true, true); //allow line breaks, discard empty brackets at the end
$lineStream = new StreamHandler(__DIR__.'/logs/app.log', Logger::DEBUG);
$lineStream->setFormatter($lineFormatter);

// Create the Elasticsearch handler
/** Too much coupling — we're skipping this one
$elasticsearchClient = ClientBuilder::create()->setHosts(['http://elasticsearch:9200'])->build();
$logstashFormatter = new LogstashFormatter('app', null, null, '', 1); //$applicationName, $systemName (default hostname), $extraPrefix, $contextPrefix, $version
$elasticsearchHandler = new ElasticLogstashHandler($elasticsearchClient, ['index' => 'send']);
$elasticsearchHandler->setFormatter($logstashFormatter);
**/

// Create the JSON handler
$jsonFormatter = new JsonFormatter();
$jsonStream = new StreamHandler(__DIR__ . '/logs/app.json', Logger::DEBUG);
$jsonStream->setFormatter($jsonFormatter, $dateFormat, false, true); //$includeStacktraces, $appendNewline

// Create the stdout handler, but reuse the log file formatter
$stdoutStream = new StreamHandler('php://stdout', Logger::DEBUG);
$stdoutStream->setFormatter($lineFormatter);

// Now add some handlers
$logger->pushHandler($lineStream);
//$logger->pushHandler($elasticsearchHandler);
$logger->pushHandler($jsonStream);
$logger->pushHandler($stdoutStream);

// Add some extra info when logging
// Don't use MemoryUsageProcessor because it returns it with a unit (MB) and we want the raw number
$logger->pushProcessor(function ($entry) {
    $entry['extra']['memory'] = memory_get_usage();
    return $entry;
});



// Application
for($i=1; $i<=20; $i++) {

    // Debug information for the loop run
    $logger->debug("Iteration '$i'");

    // Log some alerts, errors, warns, and infos
    if($i % 15 == 0){
        try {
        	throw new Exception("Wake me up at night");
        } catch (Exception $e) {
            $logger->alert(
            	"Wake me up at night",
            	array('user_experience' => '🤬')
            );
        }
    } else if ($i % 5 == 0){
        $logger->error("Investigate tomorrow\nNot that urgent");
    } else if ($i % 3 == 0){
        $logger->warning("Collect in production");
    } else {
        $logger->info("Collect in development");
    }
}
