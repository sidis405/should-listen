<?php

namespace Tests\Feature;

use App\Order;
use Tests\TestCase;
use App\Events\NewPurchase;
use App\Mail\ThankYouEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendInvoiceNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SendsInvoiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function whenPurchaseIsMadeTheEventIsFired()
    {
        $initialDispatcher = Event::getFacadeRoot();
        Event::fake();
        Model::setEventDispatcher($initialDispatcher);

        $order = factory(Order::class)->create();

        Event::assertDispatched(NewPurchase::class, function ($e) use ($order) {
            return $e->order->id === $order->id;
        });
    }

    /** @test */
    public function whenPurchaseIsMadeTheNotificationIsFired()
    {
        Notification::fake();

        $order = factory(Order::class)->create();

        $order->load('customer');

        Notification::assertSentTo(
            $order->customer,
            SendInvoiceNotification::class,
            function ($notification, $channels) use ($order) {
                return $notification->order->id === $order->id;
            }
        );
    }

    /** @test */
    public function whenPurchaseIsMadeTheThankYouEmailIsSent()
    {
        Mail::fake();

        $order = factory(Order::class)->create();

        Mail::assertSent(ThankYouEmail::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id;
        });
    }
}
