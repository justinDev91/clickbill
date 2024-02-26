<?php


namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

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

    public function sendTemplatedEmailWithAttachment(string $to, string $subject, string $template, array $variables, array $attachments)
    {
        $email = (new TemplatedEmail())
            ->from($this->sender)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($variables);

        foreach ($attachments as $attachment){
            $email->attachFromPath($attachment);
        }

        $this->mailer->send($email);
    }
}