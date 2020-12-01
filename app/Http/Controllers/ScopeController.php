<?php

namespace Northstar\Http\Controllers;

use Northstar\Auth\Scope;

class ScopeController extends Controller
{
    /**
     * Make a new ScopeController, inject dependencies,
     * and set middleware for this controller's methods.
     */
    public function __construct()
    {
        // ...
    }

    /**
     * Return the list of available scopes.
     * GET /key.
     *
     * @return array
     */
    public function index()
    {
        return Scope::all();
    }
}
