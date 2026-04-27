<?php

namespace App\Command;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

#[AsCommand(
    name: 'app:test-email',
    description: 'Test email sending',
)]
class TestEmailCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test d\'envoi d\'email');
        
        try {
            $email = (new TemplatedEmail())
                ->from(new Address('test@example.com', 'Test'))
                ->to(new Address('augustin.gantelmi@gmail.com'))
                ->subject('Test depuis Symfony')
                ->htmlTemplate('emails/contact.html.twig')
                ->context([
                    'name' => 'Test User',
                    'user_email' => 'test@example.com',
                    'subject' => 'Email de test',
                    'message' => 'Ceci est un email de test envoyé depuis la commande Symfony.',
                ]);

            $this->mailer->send($email);
            
            $io->success('Email envoyé avec succès à augustin.gantelmi@gmail.com !');
            $io->note('Vérifiez votre boîte email (et les spams)');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur lors de l\'envoi : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
