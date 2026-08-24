<?php

namespace App\Services;

use App\Mail\AdminMailing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class MailingService
{
    public function sendBulkMail(Collection $users, string $subject, string $messageBody): int
    {
        $sentCount = 0;

        foreach ($users as $user) {
            if ($user->email) {
                Mail::to($user->email)->queue(new AdminMailing($subject, $messageBody, $user));
                $sentCount++;
            }
        }

        return $sentCount;
    }
}