<?php

namespace App\Http\Controllers;

use App\Exceptions\NorthstarValidationException;
use App\Http\Controllers\Traits\FiltersActivityRequests;
use App\Http\Controllers\Traits\TransformsResponses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

abstract class ActivityApiController extends BaseController
{
    use DispatchesJobs,
        AuthorizesRequests,
        ValidatesRequests,
        FiltersActivityRequests,
        TransformsResponses;
}
