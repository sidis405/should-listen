<?php

namespace App\Listeners;

use App\Events\NewPurchase;
use App\Notifications\SendInvoiceNotification;

class SendInvoice
{
    public function handle(NewPurchase $event)
    {
        $event->order->customer->notify(new SendInvoiceNotification($event->order));
    }
}
