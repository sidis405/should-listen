<?php

namespace App\Listeners;

use App\Events\NewPurchase;
use App\Mail\ThankYouEmail;
use Illuminate\Support\Facades\Mail;
use App\Notifications\SendInvoiceNotification;

class SendInvoice
{
    public function handle(NewPurchase $event)
    {
        $event->order->customer->notify(new SendInvoiceNotification($event->order));
        Mail::to($event->order->customer)->send(new ThankYouEmail($event->order));
    }
}
