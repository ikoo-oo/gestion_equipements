<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestStatusUpdated extends Notification
{
    use Queueable;

    protected $request;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Mise à jour de votre demande #' . $this->request->id)
                    ->greeting('Bonjour ' . $notifiable->name . ',')
                    ->line('Le statut de votre demande pour "' . $this->request->equipment_description . '" a été mis à jour.')
                    ->line('Nouveau statut : ' . ucfirst(str_replace('_', ' ', $this->request->status)))
                    ->action('Voir la demande', url('/requests/' . $this->request->id))
                    ->line('Merci d\'utiliser notre application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'request_id' => $this->request->id,
            'status' => $this->request->status,
            'message' => 'Le statut de votre demande #' . $this->request->id . ' est maintenant : ' . ucfirst(str_replace('_', ' ', $this->request->status)),
            'link' => url('/requests/' . $this->request->id),
        ];
    }
}
