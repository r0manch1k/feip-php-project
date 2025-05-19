<?php

declare(strict_types=1);

namespace App\Command;

use App\Dto\UserDto;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create:admin',
    description: 'Creates a new admin user',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private AuthService $authService,
        private ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure()
    {
        $this
            ->setDescription('Creates a new admin user')
            ->addArgument('phoneNumber', InputArgument::REQUIRED, 'Phone number of the admin user')
            ->addArgument('password', InputArgument::REQUIRED, 'Password for the admin user');
    }

    #[Override]
    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $phoneNumber = $input->getArgument('phoneNumber');
        $password = $input->getArgument('password');

        $user = new UserDto(
            id: null,
            phoneNumber: $phoneNumber,
            roles: ['ROLE_ADMIN'],
            password: $password,
        );

        try {
            $this->authService->saveUser($this->validator, $user);

            $output->writeln("<info>Admin user with phone {$phoneNumber} created successfully.</info>");

            return Command::SUCCESS;
        } catch (Exception $e) {
            $output->writeln('<error>Failed to create admin user: ' . $e->getMessage() . '</error>');

            return Command::FAILURE;
        }
    }
}
