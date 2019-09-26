<?php

require_once __DIR__."/vendor/autoload.php";

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\JsonFormatter;

// Create the logger
$logger = new Logger('app_logger');

// Create the log line handler
// the default date format is "Y-m-d H:i:s"
$dateFormat = "Y-m-d H:i:s";
// the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
$output = "[%datetime%] %extra% %channel%.%level_name%: %message% %context%\n";
// Create the formatter and allow line breaks, and discard empty brackets in the end
$lineFormatter = new LineFormatter($output, $dateFormat, true, true);
$lineStream = new StreamHandler(__DIR__.'/logs/app.log', Logger::DEBUG);
$lineStream->setFormatter($lineFormatter);

// Create the JSON handler
$jsonFormatter = new JsonFormatter();
$jsonStream = new StreamHandler(__DIR__ . '/logs/app.json', Logger::DEBUG);
$jsonStream->setFormatter($jsonFormatter, $dateFormat, false, true);

// Now add some handlers
$logger->pushHandler($lineStream);
$logger->pushHandler($jsonStream);
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

// Add some extra info when logging
// Don't use MemoryUsageProcessor because it returns MB
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
        $logger->warn("Collect in production");
    } else {
        $logger->info("Collect in development");
    }
}
