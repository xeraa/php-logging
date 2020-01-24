<?php

require_once __DIR__."/vendor/autoload.php";

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ElasticsearchHandler;
use Elasticsearch\ClientBuilder;
use Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter;



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
$elasticsearchClient = ClientBuilder::create()->setHosts(['http://elasticsearch:9200'])->build();
$elasticsearchHandler = new ElasticsearchHandler($elasticsearchClient, ['index' => 'send', 'type' => '_doc']);

// Create the JSON handler
$jsonFormatter = new JsonFormatter();
$jsonStream = new StreamHandler(__DIR__ . '/logs/app.json', Logger::DEBUG);
$jsonStream->setFormatter($jsonFormatter, $dateFormat, false, true); //$includeStacktraces, $appendNewline

// Create the ECS hanlder
$ecsFormatter = new ElasticCommonSchemaFormatter();
$ecsStream = new StreamHandler(__DIR__ . '/logs/ecs.json', Logger::DEBUG);
$ecsStream->setFormatter($ecsFormatter);

// Create the stdout handler, but reuse the log file formatter
$stdoutStream = new StreamHandler('php://stdout', Logger::DEBUG);
$stdoutStream->setFormatter($lineFormatter);

// Now add some handlers
$logger->pushHandler($lineStream);
//$logger->pushHandler($elasticsearchHandler); //Only enable if you want to see this fail; wait for Elasticsearch to start up and run with `$ docker restart php_app` again
$logger->pushHandler($jsonStream);
$logger->pushHandler($ecsStream);
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
