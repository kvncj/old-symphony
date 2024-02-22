<?php

namespace App\Model\Email;

use Doctrine\Common\Collections\ArrayCollection;

class RecipientList
{
    private $recipients;

    public function __construct()
    {
        $this->recipients = new ArrayCollection();
    }

    public function addRecipient(string $name, string $email): void
    {
        $recipient = ['name' => $name, 'email' => $email];
        if (!$this->recipients->contains($recipient)) :
            $this->recipients->add($recipient);
        endif;
    }

    public function removeRecipient(string $name, string $email): void
    {
        $recipient = ['name' => $name, 'email' => $email];
        if ($this->recipients->contains($recipient)) :
            $this->recipients->removeElement($recipient);
        endif;
    }

    public function format(): array
    {
        $formatted = array_map(fn ($recipient) => ['address' => $recipient], $this->recipients->toArray());
        return $formatted;
    }
}
