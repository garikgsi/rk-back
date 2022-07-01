<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use Carbon\Carbon;

class InviteNotification extends Notification
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
        $user = $this->user;
        $inviter = User::find($user->invited_by);
        $codeValidDate = Carbon::createFromFormat('Y-m-d H:i:s', $this->user->code_expired);
        $codeValidDate->tz('Europe/Moscow');
        $urlRegister = config('school.appUrl').'/accept_invite/'.$user->email.'/'.$user->code;

        return (new MailMessage)
            // ->from(config('mail.from.address'), $inviter->name)
            ->from(config('mail.from.address'), $inviter->name)
            ->subject("$user->name, присоединяйтесь к родительскому комитету!")
            ->greeting("Здравствуйте, $user->name!")
            ->line("$inviter->name приглашает Вас зарегистрироваться на сервисе родительского комитета и получить актуальные сведения и полный доступ к ресурсам приложения")
            ->line('Теперь Вы можете зарегистрироваться на сайте '.config('school.appUrl').', используя в качестве логина адрес электронной почты '.$user->email)
            ->action('Зарегистрироваться', $urlRegister)
            ->line("Для завершения регистрации Вам также потребуется код подтверждения:")
            ->line($user->code)
            ->line('Код действителен до '.$codeValidDate->format('d.m.Y H:i:s').' (MSK)')
            ->line("Внимание! Регистрация на сайте только по приглашению участника проекта!")
            ->salutation("С уважением, $inviter->name");
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
