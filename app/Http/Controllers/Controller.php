<?php

namespace App\Http\Controllers;

use App\Exceptions\NorthstarValidationException;
use App\Http\Controllers\Traits\FiltersRequests;
use App\Http\Controllers\Traits\TransformsResponses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use DispatchesJobs,
        AuthorizesRequests,
        ValidatesRequests,
        FiltersRequests,
        TransformsResponses;

    /**
     * Throw the failed validation exception with our custom formatting. Overrides the
     * `throwValidationException` method from the `ValidatesRequests` trait.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Contracts\Validation\Validator $validator
     * @throws NorthstarValidationException
     */
    protected function throwValidationException(Request $request, $validator)
    {
        throw new NorthstarValidationException(
            $this->formatValidationErrors($validator),
        );
    }
}
