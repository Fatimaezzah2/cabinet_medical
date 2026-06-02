<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Mail\Mailable;

class AppointmentCreatedMail extends Mailable
{
    public function __construct(public Appointment $appointment)
    {
        $this->appointment->load(['user', 'doctor', 'service']);
    }

    public function build(): self
    {
        return $this
            ->subject('Appointment Created')
            ->text('emails.appointment-created');
    }
}
