<?php

namespace App;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

trait ShouldListen
{
    protected $notifiables = [];
    private $event;

    /**
     * Handle the event like a listener
     * @param \Illuminate\Support\Facedes\Event $event
     *
     * @return void
     */
    public function handle(Event $event)
    {
        $this->event = $event;

        $this->mapEventDataToNotifiable();
        $this->findNotifiablesInEvent();

        if ($this->isNotification()) {
            $this->sendAsNotification();
        } elseif ($this->isMailable()) {
            $this->sendAsMailable();
        } else {
            $this->unsupportedException();
        }
    }


    /**
     * Map the event properties to those needed by the notification
     * @return void
     */

    private function mapEventDataToNotifiable()
    {
        foreach ($this->event as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Check if event has defined the $notifiable (dot notation)
     * - return if found
     * - otherwise iterate and find the first notifiable
     *
     * @return void
     */
    public function findNotifiablesInEvent()
    {
        if (isset($this->event->notifiable)) {
            $this->notifiables[] = collect(explode('.', $this->event->notifiable))->reduce(function ($object, $property) {
                return $object->$property;
            }, $this->event);
        } else {
            collect($this->event)->map(function ($property) {
                $this->iterateAndFindNotifiables($property);
            });
        }
    }

    /**
     * Iterate recursively and populate the $notifiables array
     * @param \StdObject $event
     *
     * @return void
     */

    private function iterateAndFindNotifiables($object)
    {
        if (is_object($object) && is_a($object, Model::class)) {
            if (class_uses($object, Notifiable::class)) {
                $this->notifiables[] = $object;
            }

            foreach ($object->getRelations() as $relation) {
                $this->iterateAndFindNotifiables($relation);
            }
        }
    }

    /**
     * Verify if the base class is a notification
     * @return bool
     */
    public function isNotification()
    {
        return is_a($this, Notification::class);
    }

    /**
     * Fire notification
     * @return bool
     */
    private function sendAsNotification()
    {
        app(\Illuminate\Contracts\Notifications\Dispatcher::class)->sendNow($this->getNotifiable(), $this);
    }

    /**
     * Verify if the base class is a mailable
     * @return bool
     */
    private function isMailable()
    {
        return is_a($this, Mailable::class);
    }

    /**
     * Send mailable
     * @return bool
     */
    private function sendAsMailable()
    {
        Mail::to($this->getNotifiable())->send($this);
        // $this->to($this->getNotifiable())->send(app(\Illuminate\Contracts\Mail\Mailer::class));
    }

    /**
     * Throw unsupported exception
     * @return bool
     */
    private function unsupportedException()
    {
        throw new \Exception("This class (" . get_class($this) .") extends " . get_parent_class($this). " and does not support the ShouldListen trait");
    }

    /**
     * Return the notifiable
     */

    public function getNotifiable()
    {
        return $this->notifiables[0];
    }
}
