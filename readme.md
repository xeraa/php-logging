# Centralized Application Logs for PHP with the Elastic Stack

This repository dives into five different logging patterns:

* **Parse**: Take the log files of your applications and extract the relevant pieces of information.
* **Send**: Add a log appender to send out your events directly without persisting them to a log file.
* **Structure**: Write your events in a structured file, which you can then centralize.
* **Containerize**: Keep track of short-lived containers and configure their logging correctly.
* **Orchestrate**: Stay on top of your logs even when services are short lived and dynamically allocated on Kubernetes.

The [slides for this talk are available on my website](https://xeraa.net/talks/centralized-php-logging-patterns/).


## Dependencies

Run `$ docker run --rm --interactive --tty --volume $PWD/app:/app composer:1.9.1 install` before everything else to fetch the dependencies (or `update` if you have run it before).


## Usage

* Bring up the Elastic Stack: `$ docker-compose up --build`
* Rerun the PHP application to generate more logs: `$ docker restart php_app`
* Remove the Elastic Stack and its volumes: `$ docker-compose down -v`


## Demo

1. Start the demo with `$ docker-compose up --build`.
1. Look at the code — which pattern are we building with log statements here?
1. Look at Management -> Index Management in Kibana.


### Parse

1. How many log events should we have? 40. But we have 43 entries instead. Since only 42 would be the perfect number, something is wrong here.
1. See the `_grokparsefailure` in the tag field. Enable the multiline rules in Filebeat. It should automatically
   refresh and when you run the application again, it should now only collect 40 events.
1. Show that this works as expected now and drill down to the errors to see which emoji we are logging.
1. Copy a log line and parse it with the Grok Debugger in Kibana, for example, with the pattern
   `^\[%{TIMESTAMP_ISO8601:timestamp}\]%{SPACE}\{"memory":%{NUMBER:memory}` — show
   [https://github.com/logstash-plugins/logstash-patterns-core/blob/master/patterns/grok-patterns](https://github.com/logstash-plugins/logstash-patterns-core/blob/master/patterns/grok-patterns)
   to get started. We can copy the rest of the pattern from *logstash.conf*.
1. Point to [https://github.com/elastic/ecs](https://github.com/elastic/ecs) for the naming conventions.
1. Show the Data Visualizer in Machine Learning by uploading the LOG file. The output is actually quite good already,
   but we are sticking to our manual rules for now.
1. Find the log statements in Kibana's Discover view for the *parse* index.
1. Show the pipeline in Kibana's Monitoring view and the other components in Monitoring.
1. Create a vertical bar chart visualization on the `log.level` field.


### Send

1. Describe how the logs would be missing from the first run, since no connection to Elasticsearch would have been established yet.
1. Skip the approach after discussing its downsides.


### Structure

1. Run the application and show the data in the *structure* index.
1. Show the PHP configuration for JSON, since it is a little more complicated than the others.


### Containerize

1. Show the metadata we are collecting now.
1. Point to the ingest pipeline and show how everything is working.
1. Filter to down to `container.name : "php_app"` and point out the hinting that stops the multiline statements from being broken up.
1. Point out how you could break up the output into two indices — *docker-\** and *docker-php-\**.
1. Show the new Logs UI (adapt the pattern to match the right index).


### Todo

