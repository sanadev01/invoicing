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

namespace App\Http\Requests\Location;

use App\Http\Requests\Request;

class EditLocationRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->user()->can('edit', $this->location);
    }

    // public function prepareForValidation()
    // {
    //     $input = $this->all();

    //     //$input['id'] = $this->encodePrimaryKey($input['id']);

    //     $this->replace($input);

    // }
}
