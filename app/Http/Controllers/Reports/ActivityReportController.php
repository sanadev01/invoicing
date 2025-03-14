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

namespace App\Http\Controllers\Reports;

use App\Export\CSV\ActivityExport;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Report\GenericReportRequest;
use App\Jobs\Report\PreviewReport;
use App\Jobs\Report\SendToAdmin;
use App\Utils\Traits\MakesHash;

class ActivityReportController extends BaseController
{
    use MakesHash;

    private string $filename = 'activities.csv';

    public function __construct()
    {
        parent::__construct();
    }


    public function __invoke(GenericReportRequest $request)
    {

        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($request->has('send_email') && $request->get('send_email') && $request->missing('output')) {
            SendToAdmin::dispatch($user->company(), $request->all(), ActivityExport::class, $this->filename);

            return response()->json(['message' => 'working...'], 200);
        }

        $hash = \Illuminate\Support\Str::uuid();

        PreviewReport::dispatch($user->company(), $request->all(), ActivityExport::class, $hash);

        return response()->json(['message' => $hash], 200);

    }
}
