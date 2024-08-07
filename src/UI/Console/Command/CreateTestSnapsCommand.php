<?php

declare(strict_types=1);

namespace App\UI\Console\Command;

use App\Application\Domain\Entity\Snap\Exception\FileNotFoundException;
use App\Application\Domain\Entity\Snap\Exception\FileSizeTooBigException;
use App\Application\Domain\Entity\Snap\Exception\UnsupportedFileTypeException;
use App\Application\Domain\Entity\Snap\MimeType;
use App\Application\UseCase\Snap\Create\CreateSnapRequest;
use App\Application\UseCase\Snap\Create\CreateSnapUseCase;
use App\Application\UseCase\User\FindOneByEmail\FindOneUserByEmailRequest;
use App\Application\UseCase\User\FindOneByEmail\FindOneUserByEmailUseCase;
use Random\RandomException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\File;

#[AsCommand(name: 'app:create-test-snaps')]
final class CreateTestSnapsCommand extends Command
{
    public const string ARGUMENT_EMAIL = 'email';
    public const string ARGUMENT_QUANTITY = 'quantity';

    /**
     * @var list<File>
     */
    private array $files;

    /**
     * @throws \Exception
     */
    public function __construct(
        private readonly CreateSnapUseCase $createSnapUseCase,
        private readonly FindOneUserByEmailUseCase $findOneUserByEmailUseCase,
        #[Autowire(param: 'app.environment')] private readonly string $environment,
        #[Autowire(param: 'app.project_directory')] private readonly string $projectDirectory
    ) {
        $authorizedExtensions = [];

        foreach (MimeType::cases() as $mimeType) {
            $extensions = match ($mimeType) {
                MimeType::ImageJpeg => ['jpg', 'jpeg'],
                MimeType::ImagePng => ['png'],
                MimeType::ImageGif => ['gif'],
                MimeType::VideoMp4 => ['mp4'],
                MimeType::VideoWebm => ['webm']
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
            throw new \Exception('An error occurred with the glob function');
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
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->environment !== 'dev') {
            $output->writeln(
                'This command is for development purpose only. It will not execute outside of dev environment'
            );

            return Command::FAILURE;
        }

        if ($this->files === []) {
            $output->writeln(sprintf('No files in %s/create_test_snaps_files/', $this->projectDirectory));

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

        $filesArrayIndexes = array_keys($this->files);
        $filesArrayMinIndex = min($filesArrayIndexes);
        $filesArrayMaxIndex = max($filesArrayIndexes);

        for ($i = 0; $i < (int) $quantityArgument; ++$i) {
            $randomIndex = random_int($filesArrayMinIndex, $filesArrayMaxIndex);
            $file = $this->files[$randomIndex];

            $mimeType = $file->getMimeType();
            $size = $file->getSize();

            if ($mimeType === null) {
                throw new \Exception('Unable to determine file mimetype');
            }

            if ($size === false) {
                throw new \Exception('Unable to determine file size');
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
}
