<?php
declare(strict_types=1);

class EmailMessage
{
    private string $to;
    private string $subject;
    private string $body;
    private bool $isHtml;

    public function __construct(string $to, string $subject, string $body, bool $isHtml = true)
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->body = $body;
        $this->isHtml = $isHtml;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function isHtml(): bool
    {
        return $this->isHtml;
    }
}

