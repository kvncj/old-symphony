<?php

namespace App\Model\Email;

class EmailTemplate
{
    private $fromName;

    private $fromEmail;

    private $subject;

    private $content;

    private $recipients = [];

    private $cc = [];

    private $bb = [];

    public function __construct()
    {
        $this->fromName = 'Bargus';
        $this->fromEmail = 'no-reply@pandorabox.com.my';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    public function setFromName(string $fromName): self
    {
        $this->fromName = $fromName;

        return $this;
    }

    public function getFromEmail(): ?string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(string $fromEmail): self
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getRecipients(): ?array
    {
        return $this->recipients;
    }

    public function setRecipients(array $recipients): self
    {
        $this->recipients = $recipients;

        return $this;
    }

    public function getCc(): ?array
    {
        return $this->cc;
    }

    public function setCc(array $cc): self
    {
        $this->cc = $cc;

        return $this;
    }

    public function getBb(): ?array
    {
        return $this->bb;
    }

    public function setBb(array $bb): self
    {
        $this->bb = $bb;

        return $this;
    }
}
