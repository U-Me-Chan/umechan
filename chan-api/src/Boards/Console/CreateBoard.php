<?php

namespace PK\Boards\Console;

use PK\Boards\Services\BoardService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CreateBoard extends Command
{
    public function __construct(
        private BoardService $board_service
    ) {
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setName('boards:create')
            ->setDescription('Создаёт новый раздел')
            ->addArgument('tag', InputArgument::REQUIRED, 'Тег раздела')
            ->addArgument('name', InputArgument::REQUIRED, 'Имя раздела');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $id = $this->board_service->createBoard(
                $input->getArgument('tag'),
                $input->getArgument('name')
            );

            $io->success("Создан #{$id}!");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::INVALID;
        }
    }
}
