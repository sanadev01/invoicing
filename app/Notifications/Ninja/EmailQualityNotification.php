<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2025. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\Notifications\Ninja;

use App\Models\Company;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class EmailQualityNotification extends Notification
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */

    protected Company $company;

    protected string $spam_string;

    public function __construct(Company $company, string $spam_string)
    {
        $this->company = $company;
        $this->spam_string = $spam_string;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable)
    {
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

    public function toSlack($notifiable)
    {
        $content = "Email Quality notification for Company {$this->company->company_key} \n";

        $owner = $this->company->owner();

        $content .= "Owner {$owner->present()->name() } | {$owner->email} \n";
        $content .= "Spam trigger: {$this->spam_string}";

        return (new SlackMessage())
                ->success()
                ->from(ctrans('texts.notification_bot'))
                ->image('https://app.invoiceninja.com/favicon.png')
                ->content($content);
    }
}
