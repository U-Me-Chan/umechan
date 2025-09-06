<?php

namespace PK\Posts\Console;

use PK\Posts\Services\PostFacade;
use Throwable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class RestorePostsFromEPDSDump extends Command
{
    public function __construct(
        private PostFacade $post_facade
    ) {
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setName('posts:restore-from-epds-dump')
            ->setDescription('Восстанавливает данные постов из дампа EPDS')
            ->addArgument('from_timestamp', InputArgument::REQUIRED, 'Начальная метка времени');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Будет выполнено восстановление данных постов из дампа EPDS');

        try {
            $this->post_facade->restorePostFromEPDSDump($input->getArgument('from_timestamp'));

            $io->success('Выполнено!');

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
