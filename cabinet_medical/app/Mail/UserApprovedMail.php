<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;

class UserApprovedMail extends Mailable
{
    public function __construct(public User $user)
    {
        //
    }

    public function build(): self
    {
        return $this
            ->subject('Account Approved')
            ->text('emails.user-approved');
    }
}
