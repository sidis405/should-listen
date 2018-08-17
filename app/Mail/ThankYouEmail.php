<?php

namespace App\Mail;

use App\ShouldListen;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ThankYouEmail extends Mailable
{
    public $order;

    use Queueable, SerializesModels, ShouldListen;

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Order #'.$this->order->reference)->view('thank-you-email');
    }
}
