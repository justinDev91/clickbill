<?php


namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class MailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendTemplatedEmail(string $to, string $subject, string $template, array $variables)
    {
        $email = (new TemplatedEmail())
            ->from('mailer.clickbill@gmail.com')
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($variables);

        $this->mailer->send($email);
    }
}