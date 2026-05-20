<?php

namespace IH\Console;

use IH\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use IH\Services\Files;
use IH\FileCollection;
use Symfony\Component\Console\Input\InputArgument;

final class RebuildThumbnails extends Command
{
    public function __construct(
        private Files $files_service
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setName('files:rebuild-thumbnails')
            ->setDescription('Регенирирует миниатюры файлов')
            ->addArgument('from_offset', InputArgument::OPTIONAL, 'Смещение в списке для повторных запусков');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Пройдусь по всем файлам и регенирирую для них миниатюры');

        $from_offset = $input->getArgument('from_offset') ?? 0;

        /** @var FileCollection  */
        $files_collection = $this->files_service->getFileList(0, 1);

        $count = $files_collection->count;

        $from_offset = $count - $from_offset;

        for ($i = $from_offset; $i < $count; $i++) {
            $files_collection = $this->files_service->getFileList($i, 1);

            /** @var File */
            $file = current($files_collection->files);

            $io->info('Получен файл: ' . $file->name);

            try {
                $this->files_service->rebuildThumbnailFile($file);

                $io->info('Миниатюра успешно регенерирована');
            } catch (\Throwable $e) {
                $io->error('Ошибка при регенерации миниатюры: ' . $e::class . ': ' . $e->getMessage());

                return Command::FAILURE;
            }

            $io->info('Осталось ' . $count - ($i + 1));
        }

        $io->success('Все миниатюры успешно регенерированы');

        return Command::SUCCESS;
    }
}
