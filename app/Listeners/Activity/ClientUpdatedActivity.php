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

namespace App\Listeners\Activity;

use App\Libraries\MultiDB;
use App\Models\Activity;
use App\Repositories\ActivityRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use stdClass;

class ClientUpdatedActivity implements ShouldQueue
{
    protected $activity_repo;

    /**
     * Create the event listener.
     *
     * @param ActivityRepository $activity_repo
     */
    public function __construct(ActivityRepository $activity_repo)
    {
        $this->activity_repo = $activity_repo;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        MultiDB::setDb($event->company->db);

        $client = $event->client;

        $fields = new stdClass();

        $user_id = array_key_exists('user_id', $event->event_vars)
            ? $event->event_vars['user_id']
            : $event->client->user_id;

        $fields->client_id = $client->id;
        $fields->user_id = $user_id;
        $fields->company_id = $client->company_id;
        $fields->activity_type_id = Activity::UPDATE_CLIENT;

        $this->activity_repo->save($fields, $client, $event->event_vars);
    }
}
