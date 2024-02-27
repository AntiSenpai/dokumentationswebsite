<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpProviderInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Totp\Totp;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Creates a new user with a randomly generated TOTP secret.'
)]
class CreateUserCommand extends Command
{
    protected static $defaultName = 'app:create-user';

    private $entityManager;
    private $passwordHasher;
    private $totpProvider;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, TotpProviderInterface $totpProvider)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->totpProvider = $totpProvider;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Creates a new user with a randomly generated TOTP secret.')
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the new user.')
            ->addArgument('email', InputArgument::REQUIRED, 'The email address of the new user.')
            ->addArgument('password', InputArgument::REQUIRED, 'The password of the new user.')
            ->addArgument('roles', InputArgument::OPTIONAL, 'The roles of the new user, separated by commas.', 'ROLE_USER');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $roles = explode(',', $input->getArgument('roles'));

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles($roles);

        // Generate a TOTP secret
        $secret = $this->totpProvider->generateSecret();
        $user->setTotpSecret($secret);

        // Enable TOTP authentication for the user
        $totp = new Totp($secret, 6, 30, 'SHA1');
        $user->setTotpConfiguration($totp->getConfiguration());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(sprintf('Created new user "%s" with TOTP secret "%s".', $username, $secret));

        return Command::SUCCESS;
    }
}