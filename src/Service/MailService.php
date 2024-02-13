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

    public function sendTemplatedEmailWithAttachment(string $to, string $subject, string $template, array $variables, array $attachements)
    {
        $email = (new TemplatedEmail())
            ->from($this->sender)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($variables);

        foreach ($attachments as $attachment){
            # TODO: Refacto Logic for sending mail with attachment (PDF) when PDF generation is implemented
            // if (file_exists($attachment['path'])) {
            //     $attachmentFileName = basename($attachment['path']);
            //     $attachmentData = file_get_contents($attachment['path']);
            //     $attachmentPart = new DataPart($attachmentData, $attachmentFileName, $attachment['contentType']);
            //     $email->attach($attachmentPart);
            // }
        }

        $this->mailer->send($email);
    }
}