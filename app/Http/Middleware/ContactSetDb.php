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

namespace App\Http\Middleware;

use App\Libraries\MultiDB;
use Closure;
use Illuminate\Http\Request;
use stdClass;

class ContactSetDb
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $error = [
            'message' => 'Invalid Token',
            'errors' => new stdClass(),
        ];

        if ($request->header('X-API-TOKEN') && config('ninja.db.multi_db_enabled')) {
            if (! MultiDB::contactFindAndSetDb($request->header('X-API-TOKEN'))) {
                return response()->json($error, 403);
            }
        } else {
            return response()->json($error, 403);
        }

        return $next($request);
    }
}
