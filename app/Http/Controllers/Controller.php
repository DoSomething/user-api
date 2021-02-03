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
use Illuminate\Validation\ValidationException;

abstract class Controller extends BaseController
{
    use DispatchesJobs,
        AuthorizesRequests,
        FiltersRequests,
        TransformsResponses;
    use ValidatesRequests {
        validate as traitValidate;
    }

    /**
     * Validate the given request with the given rules.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return array
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate(
        Request $request,
        array $rules,
        array $messages = [],
        array $customAttributes = []
    ) {
        try {
            return $this->traitValidate(
                $request,
                $rules,
                $messages,
                $customAttributes,
            );
        } catch (ValidationException $exception) {
            // This tells our handler to use Northstar's custom response formatting.
            throw new NorthstarValidationException($exception->errors());
        }
    }
}
