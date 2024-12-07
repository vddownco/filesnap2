<?php

declare(strict_types=1);

namespace App\UI\Console\Command;

use App\Application\Domain\Snap\Exception\FileNotFoundException;
use App\Application\Domain\Snap\Exception\FileSizeTooBigException;
use App\Application\Domain\Snap\Exception\UnsupportedFileTypeException;
use App\Application\Domain\Snap\MimeType;
use App\Application\UseCase\Snap\Create\CreateSnapRequest;
use App\Application\UseCase\User\FindOneByEmail\FindOneUserByEmailRequest;
use App\Application\UseCase\User\FindOneByEmail\FindOneUserByEmailUseCase;
use App\Infrastructure\UseCase\Snap\CreateSnapUseCaseDispatcher;
use Random\RandomException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

#[AsCommand(name: 'app:create-test-snaps')]
final class CreateTestSnapsCommand extends Command
{
    private const string ARGUMENT_EMAIL = 'email';
    private const string ARGUMENT_QUANTITY = 'quantity';

    /**
     * @var non-empty-list<File>
     */
    private array $files;

    /**
     * @throws \Exception
     */
    public function __construct(
        #[Autowire(param: 'app.environment')] private readonly string $environment,
        #[Autowire(param: 'app.project_directory')] private readonly string $projectDirectory,
        private readonly CreateSnapUseCaseDispatcher $createSnapUseCase,
        private readonly FindOneUserByEmailUseCase $findOneUserByEmailUseCase,
        private readonly Filesystem $filesystem = new Filesystem(),
    ) {
        $authorizedExtensions = [];

        foreach (MimeType::cases() as $mimeType) {
            $extensions = match ($mimeType) {
                MimeType::ImageJpeg => ['jpg', 'jpeg'],
                MimeType::ImagePng => ['png'],
                MimeType::ImageGif => ['gif'],
                MimeType::ImageWebp => ['webp'],
                MimeType::VideoMp4 => ['mp4'],
                MimeType::VideoWebm => ['webm'],
            };

            $authorizedExtensions = [...$authorizedExtensions, ...$extensions];
        }

        $filePaths = glob(
            sprintf(
                '%s/create_test_snaps_files/*{%s}',
                $this->projectDirectory,
                implode(',', $authorizedExtensions)
            ),
            GLOB_BRACE
        );

        if ($filePaths === false) {
            throw new \RuntimeException('An error occurred with the glob function');
        }

        if ($filePaths === []) {
            throw new \RuntimeException('No files were found');
        }

        $this->files = array_map(
            static fn (string $filePath) => new File($filePath),
            $filePaths
        );

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            self::ARGUMENT_EMAIL,
            InputArgument::REQUIRED,
            'The account email.'
        );

        $this->addArgument(
            self::ARGUMENT_QUANTITY,
            InputArgument::OPTIONAL,
            'The quantity of snaps to create.',
            '125'
        );
    }

    /**
     * @throws FileSizeTooBigException
     * @throws FileNotFoundException
     * @throws UnsupportedFileTypeException
     * @throws RandomException
     * @throws \Exception
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->environment !== 'dev') {
            $output->writeln(
                'This command is for development purpose only. It will not execute outside of dev environment'
            );

            return Command::FAILURE;
        }

        $quantityArgument = $input->getArgument(self::ARGUMENT_QUANTITY);
        $emailArgument = $input->getArgument(self::ARGUMENT_EMAIL);

        if (is_numeric($quantityArgument) === false) {
            $output->writeln('The quantity parameter must be a number.');

            return Command::FAILURE;
        }

        if (is_string($emailArgument) === false) {
            $output->writeln('The email parameter must be a string.');

            return Command::FAILURE;
        }

        $findUserUserCaseResponse = ($this->findOneUserByEmailUseCase)(new FindOneUserByEmailRequest($emailArgument));
        $user = $findUserUserCaseResponse->getUser();

        if ($user === null) {
            $output->writeln('No user existing with this email.');

            return Command::FAILURE;
        }

        for ($i = 0; $i < (int) $quantityArgument; ++$i) {
            $file = $this->getRandomTempFile();
            $mimeType = $file->getMimeType();
            $size = $file->getSize();

            if ($mimeType === null) {
                throw new \RuntimeException('Unable to determine file mimetype');
            }

            if ($size === false) {
                throw new \RuntimeException('Unable to determine file size');
            }

            ($this->createSnapUseCase)(new CreateSnapRequest(
                $user->getId(),
                $file->getFilename(),
                $mimeType,
                $file->getPathname(),
                $size
            ));
        }

        return Command::SUCCESS;
    }

    /**
     * @throws RandomException
     */
    private function getRandomTempFile(): File
    {
        $filesArrayMaxIndex = max(array_keys($this->files));

        $randomIndex = random_int(0, $filesArrayMaxIndex);
        $file = $this->files[$randomIndex];

        $tempPath = $this->filesystem->tempnam(sys_get_temp_dir(), 'test_snap_');
        $this->filesystem->appendToFile($tempPath, $file->getContent());

        return new File($tempPath);
    }
}
