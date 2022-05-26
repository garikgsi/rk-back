<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use Carbon\Carbon;

class RegCode extends Notification
{
    use Queueable;

    public User $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $codeValidDate = Carbon::createFromFormat('Y-m-d H:i:s', $this->user->code_expired);
        $codeValidDate->tz('Europe/Moscow');
        return (new MailMessage)
            ->from('admin@test.com', 'Ваш личный почтальон')
            ->subject('Новый код для подтверждения регистрации')
            ->greeting('Здравствуйте!')
            ->line('Мы сгенерировали для Вас новый код подтверждения:')
            ->line($this->user->code)
            ->line('Используйте его для подтверждения регистрации на сайте или смене пароля.')
            ->line('Код действителен до '.$codeValidDate->format('d.m.Y H:i:s').' (MSK)')
            ->salutation('С уважением, Служба технической поддержки');
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
            //
        ];
    }
}
