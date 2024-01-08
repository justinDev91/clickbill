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
        $this->sender = $_ENV['CLICKBILL_MAIL'];
    }

    public function sendTemplatedEmail(string $to, string $subject, string $template, array $variables)
    {
        $email = (new TemplatedEmail())
            ->from($this->sender)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($variables);

        $this->mailer->send($email);
    }
}