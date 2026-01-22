<?php

namespace App\Mail;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Employee $employee;
    public string $language;

    /**
     * Create a new message instance.
     * @param Employee $employee
     * @param string $language 'uz', 'ru', or 'en'
     */
    public function __construct(Employee $employee, string $language = 'uz')
    {
        $this->employee = $employee;
        $this->language = $language;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Dynamic Subject based on language
        $subjects = [
            'uz' => 'Jamoamizga xush kelibsiz, ' . $this->employee->first_name . '!',
            'ru' => 'Добро пожаловать в команду, ' . $this->employee->first_name . '!',
            'en' => 'Welcome to the team, ' . $this->employee->first_name . '!',
        ];

        return new Envelope(
            subject: $subjects[$this->language] ?? $subjects['en'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
        );
    }
}
