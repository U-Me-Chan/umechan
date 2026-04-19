<?php

use Monolog\Logger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ridouchire\RadioScheduler\Mpd;
use Ridouchire\RadioScheduler\Tasks\Services\SequenceGenerator;
use Ridouchire\RadioScheduler\Tasks\Services\TaskManager;
use Ridouchire\RadioScheduler\Tasks\Services\TaskParser;
use Ridouchire\RadioScheduler\Tasks\Services\YamlSchemasDirectoryIterator;
use Ridouchire\RadioScheduler\Tasks\Task;

class WeekdayRotationTest extends TestCase
{
    /** @var MockObject|SequenceGenerator */
    private MockObject|SequenceGenerator $sequence_generator;

    /** @var MockObject|Mpd */
    private MockObject|Mpd $mpd;

    /** @var MockObject|Logger */
    private MockObject|Logger $logger;

    private TaskParser $task_parser;
    private TaskManager $task_manager;

    public function setUp(): void
    {
        $this->sequence_generator = $this->createMock(SequenceGenerator::class);
        $this->mpd                = $this->createMock(Mpd::class);
        $this->logger             = $this->createMock(Logger::class);
        $this->task_parser        = new TaskParser();
        $this->task_manager       = new TaskManager(
            $this->sequence_generator,
            $this->mpd,
            $this->logger
        );

        $iterator = new YamlSchemasDirectoryIterator(__DIR__ . '/../../schemas/');

        foreach ($iterator->getIterator() as $yaml_file) {
            $tasks = $this->task_parser->parseFromFile($yaml_file);

            array_walk($tasks, function (Task $task) {
                $this->task_manager->add($task);
            });
        }

        date_default_timezone_set('Europe/Ulyanovsk');
    }

    #[Test]
    #[TestWith(['last Monday', 'Первый эксперимент'])]
    #[TestWith(['last Monday +1 hour', 'Первый эксперимент'])]
    #[TestWith(['last Monday +2 hour', 'Первый эксперимент'])]
    #[TestWith(['last Monday +3 hour', 'Первый эксперимент'])]
    #[TestWith(['last Monday +4 hour', 'Первый эксперимент'])]
    #[TestWith(['last Monday +5 hour', 'Первый эксперимент'])]
    #[TestWith(['last Monday +6 hour', 'Голоса понедельника'])]
    #[TestWith(['last Monday +7 hour', 'Poniedelnik Jazz'])]
    #[TestWith(['last Monday +8 hour', 'Poniedelnik Jazz'])]
    #[TestWith(['last Monday +9 hour', 'Monday Waves'])]
    #[TestWith(['last Monday +10 hour', 'Monday Waves'])]
    #[TestWith(['last Monday +11 hour', 'Monday Waves'])]
    #[TestWith(['last Monday +18 hour', 'Monday Waves'])]
    #[TestWith(['last Monday +12 hour', 'Понедельничные Хиты'])]
    #[TestWith(['last Monday +13 hour', 'Понедельничные Хиты'])]
    #[TestWith(['last Monday +14 hour', 'Понедельник На Русском'])]
    #[TestWith(['last Monday +15 hour', 'Понедельничные Хиты'])]
    #[TestWith(['last Monday +16 hour', 'Понедельничные Хиты'])]
    #[TestWith(['last Monday +17 hour', 'Monday Waves'])]
    #[TestWith(['last Monday +19 hour', 'Первый эксперимент'])]
    #[TestWith(['last Monday +20 hour', 'Первый эксперимент'])]
    #[TestWith(['last Monday +21 hour', 'Первый эксперимент'])]
    #[TestWith(['last Monday +22 hour', 'Первый эксперимент'])]
    #[TestWith(['last Monday +23 hour', 'Первый эксперимент'])]
    public function testMondayRotation(string $datetime, string $task_name): void
    {
        $this->assertEquals(
            TaskManager::EXIT_CODE_NO_ERROR,
            $this->task_manager->tick(strtotime($datetime))
        );

        $this->assertEquals($task_name, $this->task_manager->getCurrentTaskName());
    }

    #[Test]
    #[TestWith(['last Tuesday', 'Tuesday Dial Tone'])]
    #[TestWith(['last Tuesday +1 hour', 'Deep Dive Tuesday'])]
    #[TestWith(['last Tuesday +2 hour', 'Deep Dive Tuesday'])]
    #[TestWith(['last Tuesday +3 hour', 'Slow Tuesday'])]
    #[TestWith(['last Tuesday +4 hour', 'Slow Tuesday'])]
    #[TestWith(['last Tuesday +5 hour', 'Night Drive: Tuesday'])]
    #[TestWith(['last Tuesday +6 hour', 'Night Drive: Tuesday'])]
    #[TestWith(['last Tuesday +7 hour', 'Вторник. Завтрак. Чилл.'])]
    #[TestWith(['last Tuesday +8 hour', 'Вторник. Завтрак. Чилл.'])]
    #[TestWith(['last Tuesday +9 hour', 'Pop Global & Local: Вторник'])]
    #[TestWith(['last Tuesday +10 hour', 'Pop Global & Local: Вторник'])]
    #[TestWith(['last Tuesday +11 hour', 'Pop Global & Local: Вторник'])]
    #[TestWith(['last Tuesday +12 hour', 'Tuesday Pop Boost'])]
    #[TestWith(['last Tuesday +13 hour', 'Tuesday Pop Boost'])]
    #[TestWith(['last Tuesday +14 hour', 'Tuesday Dnb Pop'])]
    #[TestWith(['last Tuesday +15 hour', 'Tuesday Pop Boost'])]
    #[TestWith(['last Tuesday +16 hour', 'Tuesday Pop Boost'])]
    #[TestWith(['last Tuesday +17 hour', 'Tuesday Pop Boost'])]
    #[TestWith(['last Tuesday +18 hour', 'Pop Dance Evening: Tuesday Edition'])]
    #[TestWith(['last Tuesday +19 hour', 'Tuesday: Chill Mode: ON'])]
    #[TestWith(['last Tuesday +20 hour', 'Tuesday: Retrowave'])]
    #[TestWith(['last Tuesday +21 hour', 'Медленная волна'])]
    #[TestWith(['last Tuesday +22 hour', 'Медленная волна'])]
    #[TestWith(['last Tuesday +23 hour', 'Chill Hop'])]
    public function testTuesdayRotation(string $datetime, string $task_name): void
    {
        $this->assertEquals(
            TaskManager::EXIT_CODE_NO_ERROR,
            $this->task_manager->tick(strtotime($datetime))
        );

        $this->assertEquals($task_name, $this->task_manager->getCurrentTaskName());
    }

    #[Test]
    #[TestWith(['last Wednesday', 'Жидкие кастрюли'])]
    #[TestWith(['last Wednesday +1 hour', 'Жидкие кастрюли'])]
    #[TestWith(['last Wednesday +2 hour', 'Digital Resistance'])]
    #[TestWith(['last Wednesday +3 hour', 'Воздушные кастрюли'])]
    #[TestWith(['last Wednesday +4 hour', 'Video Game Music'])]
    #[TestWith(['last Wednesday +5 hour', 'Video Game Music'])]
    #[TestWith(['last Wednesday +6 hour', 'Chill Hop'])]
    #[TestWith(['last Wednesday +7 hour', 'Chill Hop'])]
    #[TestWith(['last Wednesday +8 hour', 'Чилловая среда, чюваки'])]
    #[TestWith(['last Wednesday +9 hour', 'Чилловая среда, чюваки'])]
    #[TestWith(['last Wednesday +10 hour', 'Выжить до обеда: Среда'])]
    #[TestWith(['last Wednesday +11 hour', 'Выжить до обеда: Среда'])]
    #[TestWith(['last Wednesday +12 hour', 'Средний разгон'])]
    #[TestWith(['last Wednesday +13 hour', 'Средний разгон'])]
    #[TestWith(['last Wednesday +14 hour', 'Fast Food Core'])]
    #[TestWith(['last Wednesday +15 hour', 'Среда: Чистый звук'])]
    #[TestWith(['last Wednesday +16 hour', 'Среда: Чистый звук'])]
    #[TestWith(['last Wednesday +17 hour', 'Evening Pop Dance 17'])]
    #[TestWith(['last Wednesday +18 hour', 'Pop Retrowave'])]
    #[TestWith(['last Wednesday +19 hour', 'Future Funk: Среда Special'])]
    #[TestWith(['last Wednesday +20 hour', 'Future Funk: Среда Special'])]
    #[TestWith(['last Wednesday +21 hour', 'Чилловая среда, чюваки'])]
    #[TestWith(['last Wednesday +22 hour', 'Retrowave'])]
    #[TestWith(['last Wednesday +23 hour', 'Digital Resistance'])]
    public function testWednesdayRotation(string $datetime, string $task_name): void
    {
        $this->assertEquals(
            TaskManager::EXIT_CODE_NO_ERROR,
            $this->task_manager->tick(strtotime($datetime))
        );

        $this->assertEquals($task_name, $this->task_manager->getCurrentTaskName());
    }

    #[Test]
    #[TestWith(['last Thursday', 'House'])]
    #[TestWith(['last Thursday +1 hour', 'Chill Electronica'])]
    #[TestWith(['last Thursday +2 hour', 'Chill Electronica'])]
    #[TestWith(['last Thursday +3 hour', 'Кастрюльби'])]
    #[TestWith(['last Thursday +4 hour', 'Кастрюльби'])]
    #[TestWith(['last Thursday +5 hour', 'Живой голос'])]
    #[TestWith(['last Thursday +6 hour', 'Живой голос'])]
    #[TestWith(['last Thursday +7 hour', 'Воздушные инструменты'])]
    #[TestWith(['last Thursday +8 hour', 'Воздушные инструменты'])]
    #[TestWith(['last Thursday +9 hour', 'Воздушные инструменты'])]
    #[TestWith(['last Thursday +10 hour', 'Воздушные инструменты'])]
    #[TestWith(['last Thursday +11 hour', 'Воздушные инструменты'])]
    #[TestWith(['last Thursday +12 hour', 'Alternative Rock'])]
    #[TestWith(['last Thursday +13 hour', 'Alternative Rock'])]
    #[TestWith(['last Thursday +14 hour', 'Pop Dance'])]
    #[TestWith(['last Thursday +15 hour', 'Pop Dance'])]
    #[TestWith(['last Thursday +16 hour', 'Pop Dance Evening'])]
    #[TestWith(['last Thursday +17 hour', 'Pop Dance Evening'])]
    #[TestWith(['last Thursday +18 hour', 'Retrowave'])]
    #[TestWith(['last Thursday +19 hour', 'Четверг: остатки'])]
    #[TestWith(['last Thursday +20 hour', 'Четверг: остатки'])]
    #[TestWith(['last Thursday +21 hour', 'Video Game Music'])]
    #[TestWith(['last Thursday +22 hour', 'Video Game Music'])]
    #[TestWith(['last Thursday +23 hour', 'Video Game Music'])]
    public function testThursdayRotation(string $datetime, string $task_name): void
    {
        $this->assertEquals(
            TaskManager::EXIT_CODE_NO_ERROR,
            $this->task_manager->tick(strtotime($datetime))
        );

        $this->assertEquals($task_name, $this->task_manager->getCurrentTaskName());
    }

    #[Test]
    #[TestWith(['last Friday', 'Воздушные кастрюли'])]
    #[TestWith(['last Friday +1 hour', 'Жидкие кастрюли'])]
    #[TestWith(['last Friday +2 hour', 'Digital Resistance'])]
    #[TestWith(['last Friday +3 hour', 'Digital Resistance'])]
    #[TestWith(['last Friday +4 hour', 'Future Funk'])]
    #[TestWith(['last Friday +5 hour', 'Кастрюльное'])]
    #[TestWith(['last Friday +6 hour', 'Pop Dance Morning'])]
    #[TestWith(['last Friday +7 hour', 'Pop Dance Morning'])]
    #[TestWith(['last Friday +8 hour', 'Pop Dance'])]
    #[TestWith(['last Friday +9 hour', 'Pop Dance'])]
    #[TestWith(['last Friday +10 hour', 'Pop Dance'])]
    #[TestWith(['last Friday +11 hour', 'DnB Pop'])]
    #[TestWith(['last Friday +12 hour', 'Pop Dance'])]
    #[TestWith(['last Friday +13 hour', 'Dancecore'])]
    #[TestWith(['last Friday +14 hour', 'Dancecore'])]
    #[TestWith(['last Friday +15 hour', 'Pop Dance'])]
    #[TestWith(['last Friday +16 hour', 'Pop Dance'])]
    #[TestWith(['last Friday +17 hour', 'Digital Resistance'])]
    #[TestWith(['last Friday +18 hour', 'Pop Retrowave'])]
    #[TestWith(['last Friday +19 hour', 'Pop Retrowave'])]
    #[TestWith(['last Friday +20 hour', 'Future Funk'])]
    #[TestWith(['last Friday +21 hour', 'Pop Chill'])]
    #[TestWith(['last Friday +22 hour', 'Pop Chill'])]
    #[TestWith(['last Friday +23 hour', 'Slowave'])]
    public function testFridayRotation(string $datetime, string $task_name): void
    {
        $this->assertEquals(
            TaskManager::EXIT_CODE_NO_ERROR,
            $this->task_manager->tick(strtotime($datetime))
        );

        $this->assertEquals($task_name, $this->task_manager->getCurrentTaskName());
    }

    #[Test]
    #[TestWith(['last Saturday', 'Fusion Night'])]
    #[TestWith(['last Saturday +1 hour', 'Fusion Night'])]
    #[TestWith(['last Saturday +2 hour', 'Fusion Night'])]
    #[TestWith(['last Saturday +3 hour', 'Кастрюльби'])]
    #[TestWith(['last Saturday +4 hour', 'Кастрюльби'])]
    #[TestWith(['last Saturday +5 hour', 'Retrowave'])]
    #[TestWith(['last Saturday +6 hour', 'Video Game Music'])]
    #[TestWith(['last Saturday +7 hour', 'Pop Chill'])]
    #[TestWith(['last Saturday +8 hour', 'Pop Chill'])]
    #[TestWith(['last Saturday +9 hour', 'Pop'])]
    #[TestWith(['last Saturday +10 hour', 'Pop'])]
    #[TestWith(['last Saturday +11 hour', 'Pop'])]
    #[TestWith(['last Saturday +12 hour', 'DnB Pop + Pop Dance'])]
    #[TestWith(['last Saturday +13 hour', 'Dancecore'])]
    #[TestWith(['last Saturday +14 hour', 'DnB Pop + Pop Dance'])]
    #[TestWith(['last Saturday +15 hour', 'DnB Pop + Pop Dance'])]
    #[TestWith(['last Saturday +16 hour', 'DnB Pop + Pop Dance'])]
    #[TestWith(['last Saturday +17 hour', 'DnB Pop + Pop Dance'])]
    #[TestWith(['last Saturday +18 hour', 'Pop Chill'])]
    #[TestWith(['last Saturday +19 hour', 'Slowave'])]
    #[TestWith(['last Saturday +20 hour', 'Slowave'])]
    #[TestWith(['last Saturday +21 hour', 'Slowave'])]
    #[TestWith(['last Saturday +22 hour', 'Night Mix'])]
    #[TestWith(['last Saturday +23 hour', 'Night Mix'])]
    public function testSaturdayRotation(string $datetime, string $task_name): void
    {
        $this->assertEquals(
            TaskManager::EXIT_CODE_NO_ERROR,
            $this->task_manager->tick(strtotime($datetime))
        );

        $this->assertEquals($task_name, $this->task_manager->getCurrentTaskName());
    }

    #[Test]
    #[TestWith(['last Sunday', 'Telewave'])]
    #[TestWith(['last Sunday +1 hour', 'Кухонный комбайн'])]
    #[TestWith(['last Sunday +2 hour', 'Night Mix'])]
    #[TestWith(['last Sunday +3 hour', 'Night Mix'])]
    #[TestWith(['last Sunday +4 hour', 'Night Mix'])]
    #[TestWith(['last Sunday +5 hour', 'Night Mix'])]
    #[TestWith(['last Sunday +6 hour', 'Chill Mix'])]
    #[TestWith(['last Sunday +7 hour', 'Chill Mix'])]
    #[TestWith(['last Sunday +8 hour', 'Pop Chill'])]
    #[TestWith(['last Sunday +9 hour', 'Pop Chill'])]
    #[TestWith(['last Sunday +10 hour', 'Pop'])]
    #[TestWith(['last Sunday +11 hour', 'Pop'])]
    #[TestWith(['last Sunday +12 hour', 'Day Mix'])]
    #[TestWith(['last Sunday +13 hour', 'Day Mix'])]
    #[TestWith(['last Sunday +14 hour', 'Pop Dance'])]
    #[TestWith(['last Sunday +15 hour', 'Pop Dance'])]
    #[TestWith(['last Sunday +16 hour', 'Pop Dance'])]
    #[TestWith(['last Sunday +17 hour', 'Chill Mix'])]
    #[TestWith(['last Sunday +18 hour', 'Chill Mix'])]
    #[TestWith(['last Sunday +19 hour', 'Future Funk'])]
    #[TestWith(['last Sunday +20 hour', 'Future Funk'])]
    #[TestWith(['last Sunday +21 hour', 'Night Mix'])]
    #[TestWith(['last Sunday +22 hour', 'Telewave'])]
    #[TestWith(['last Sunday +23 hour', 'Video Game Music'])]
    public function testSundayRotation(string $datetime, string $task_name): void
    {
        $this->assertEquals(
            TaskManager::EXIT_CODE_NO_ERROR,
            $this->task_manager->tick(strtotime($datetime))
        );

        $this->assertEquals($task_name, $this->task_manager->getCurrentTaskName());
    }
}
