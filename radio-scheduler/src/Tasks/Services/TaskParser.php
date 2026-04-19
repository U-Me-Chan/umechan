<?php

namespace Ridouchire\RadioScheduler\Tasks\Services;

use stdClass;
use RuntimeException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Opis\JsonSchema\Validator as JsonSchemaValidator;
use Ridouchire\RadioScheduler\Tasks\Task\TracklistGeneratorType;
use Ridouchire\RadioScheduler\Tasks\Task\TrackSources\DirectorySource;
use Ridouchire\RadioScheduler\Tasks\Task\SequencePosition;
use Ridouchire\RadioScheduler\Tasks\Task\Rules\ExactHourRule;
use Ridouchire\RadioScheduler\Tasks\Task\Rules\ExactWeekdayRule;
use Ridouchire\RadioScheduler\Tasks\Task\Rules\OddDayRule;
use Ridouchire\RadioScheduler\Tasks\Task\Rules\OrRule;
use Ridouchire\RadioScheduler\Tasks\Task\Rules\AndRule;
use Ridouchire\RadioScheduler\Tasks\Task;

final class TaskParser
{
    private JsonSchemaValidator $validator;
    private string $schema;

    public function __construct()
    {
        $this->validator = new JsonSchemaValidator();
        $this->schema    = file_get_contents(__DIR__ . '/../TaskJsonSchema.json');
    }

    public function parseFromString(string $yaml): array
    {
        $yaml_data = Yaml::parse($yaml, Yaml::PARSE_OBJECT_FOR_MAP);

        if ($yaml_data === null) {
            throw new RuntimeException('YAML not valid');
        }

        if (!$this->isValid($yaml_data)) {
            throw new RuntimeException('YAML not valid');
        }

        return $this->parse(Yaml::parse($yaml));
    }

    public function parseFromFile(string $path_to_yaml): array
    {
        $yaml = Yaml::parseFile($path_to_yaml, Yaml::PARSE_OBJECT_FOR_MAP);

        if ($yaml === null) {
            throw new RuntimeException('YAML not valid');
        }

        if (!$this->isValid($yaml)) {
            throw new RuntimeException($path_to_yaml . ': not valid');
        }

        return $this->parse(Yaml::parseFile($path_to_yaml));
    }

    private function isValid(stdClass|array $data): bool
    {
        return $this->validator->validate($data, $this->schema)->isValid();
    }

    /**
     * @return Task[]
     */
    private function parse(array $data): array
    {
        $tasks = [];

        foreach ($data as $task_data) {
            $sequence = [];

            foreach ($task_data['positions'] as $position_data) {
                $dirs = [];

                foreach ($position_data['sources'] as $source_data) {
                    list($type, $data) = explode(':', $source_data);

                    if ($type == 'dir') {
                        $dirs[] = $data;
                    } else {
                        throw new RuntimeException('Нет реализации для ' . $type);
                    }
                }

                $source = new DirectorySource($dirs);

                $sequence[] = new SequencePosition(
                    TracklistGeneratorType::fromString($position_data['gentype']),
                    [$source],
                    $position_data['count']['min'] ?? $position_data['count']['max'],
                    $position_data['count']['max']
                );
            }

            $_rules = [];

            if (array_key_exists('weekdays', $task_data)) {
                $_weekdays = array_map(function (int $weekday) {
                    return new ExactWeekdayRule($weekday);
                }, $task_data['weekdays']);

                $_rules[] = new OrRule($_weekdays);
            }

            if (array_key_exists('hours', $task_data)) {
                $_hours = array_map(function (int $hour) {
                    return new ExactHourRule($hour);
                }, $task_data['hours']);

                $_rules[] = new OrRule($_hours);
            }

            if (array_key_exists('odd_day', $task_data)) {
                $_rules[] = new OddDayRule();
            }

            if (empty($_rules)) {
                throw new RuntimeException();
            }

            $rules = new AndRule($_rules);

            $tasks[] = new Task($rules, $sequence, $task_data['name']);
        }

        return $tasks;
    }
}
