<?php
declare(strict_types=1);

namespace App\UI\Console\Command;

use App\Application\Domain\Entity\User\UserRole;
use App\Application\UseCase\User\Create\CreateUserRequest;
use App\Application\UseCase\User\Create\CreateUserUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'app:create-test-user')]
final class CreateTestUserCommand extends Command
{
    public function __construct(
        private readonly CreateUserUseCase $createUserUseCase,
        #[Autowire(param: 'app.environment')] private readonly string $environment
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ('dev' !== $this->environment) {
            $output->writeln(
                'This command is for development purpose only. It will not execute outside of dev environment'
            );

            return Command::FAILURE;
        }

        ($this->createUserUseCase)(
            new CreateUserRequest(
                'test@test.test',
                'pass',
                [UserRole::User, UserRole::Admin]
            )
        );

        return Command::SUCCESS;
    }
}