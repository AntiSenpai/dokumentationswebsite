<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(
    name: 'app:set-totp-secret',
    description: 'Setzt ein TOTP Secret für alle Benutzer ohne eines.'
)]
class SetTotpSecretCommand extends Command
{
    protected static $defaultName = 'app:set-totp-secret';

    private $entityManager;
    private $totpAuthenticator;

    public function __construct(EntityManagerInterface $entityManager, TotpAuthenticatorInterface $totpAuthenticator)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->totpAuthenticator = $totpAuthenticator;
    }

    protected function configure()
    {
        $this->setDescription('Setzt ein TOTP Secret für alle Benutzer ohne eines.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userRepo = $this->entityManager->getRepository(User::class);
        $users = $userRepo->findBy(['totpSecret' => null]);

        foreach ($users as $user) {
            $secret = $this->totpAuthenticator->generateSecret();
            $user->setTotpAuthenticationSecret($secret);
            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();
        $output->writeln('TOTP Secrets gesetzt für ' . count($users) . ' Benutzer.');

        return Command::SUCCESS;
    }
}