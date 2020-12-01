<?php

namespace App\Http\Controllers;

use App\Auth\Scope;

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
     *
     * @return array
     */
    public function index()
    {
        return Scope::all();
    }
}
