<?php

namespace PK\Boards\Console;

use PK\Boards\Exceptions\BoardNotFoundException;
use PK\Boards\Services\BoardService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SetPublicBoard extends Command
{
    public function __construct(
        private BoardService $board_service
    ) {
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setName('boards:set-public')
            ->setDescription('Делает раздел публичным')
            ->addArgument('tag', InputArgument::REQUIRED, 'Тег раздела');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->board_service->setPublicFlagState($input->getArgument('tag'), true);

            $io->success('Установлен!');

            return Command::SUCCESS;
        } catch (BoardNotFoundException $e) {
            $io->error($e->getMessage());

            return Command::INVALID;
        }
    }
}
