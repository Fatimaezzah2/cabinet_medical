<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Mail\Mailable;

class AppointmentConfirmedMail extends Mailable
{
    public function __construct(public Appointment $appointment)
    {
        $this->appointment->load(['patient', 'doctor', 'service']);
    }

    public function build(): self
    {
        return $this
            ->subject('Appointment Confirmed')
            ->text('emails.appointment-confirmed');
    }
}
