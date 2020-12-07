<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\Fastly;
use App\Services\Resources\Redirect;
use Illuminate\Http\Request;

class RedirectsController extends Controller
{
    /**
     * The Fastly API client.
     * @var Fastly
     */
    protected $fastly;

    /**
     * Create a RedirectsController.
     *
     * @param Fastly $fastly
     */
    public function __construct(Fastly $fastly)
    {
        $this->fastly = $fastly;

        $this->middleware('auth:web');
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $redirects = $this->fastly->getAllRedirects();

        return view('admin.redirects.index', ['redirects' => $redirects]);
    }

    /**
     * Show redirect details.
     *
     * @param Redirect $redirect
     * @return \Illuminate\Http\Response
     */
    public function show(Redirect $redirect)
    {
        return view('admin.redirects.show', ['redirect' => $redirect]);
    }

    /**
     * Create a new redirect.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.redirects.create');
    }

    /**
     * Store a new redirect.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'path' => 'required|string|regex:/^[^?]+$/',
                'target' => 'required|url',
            ],
            [
                'path.regex' => 'Paths cannot contain query strings.',
            ],
        );

        $redirect = $this->fastly->createRedirect(
            $request->path,
            $request->target,
        );

        return redirect()->route('admin.redirects.show', $redirect->id);
    }

    /**
     * Edit redirect details.
     *
     * @param Redirect $redirect
     * @return \Illuminate\Http\Response
     */
    public function edit(Redirect $redirect)
    {
        return view('admin.redirects.edit', ['redirect' => $redirect]);
    }

    /**
     * Update an existing redirect's details.
     *
     * @param Redirect $redirect
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Redirect $redirect, Request $request)
    {
        $this->validate($request, [
            'target' => 'required|url',
        ]);

        $this->fastly->updateRedirect($redirect->path, $request->target);

        return redirect()->route('admin.redirects.show', $redirect->id);
    }

    /**
     * Destroy an existing redirect.
     *
     * @param Redirect $redirect
     * @return \Illuminate\Http\Response
     */
    public function destroy(Redirect $redirect)
    {
        $successful = $this->fastly->deleteRedirect($redirect->id);

        if (!$successful) {
            return redirect()
                ->route('admin.redirects.index')
                ->with('flash', 'Could not delete redirect.');
        }

        return redirect()
            ->route('admin.redirects.index')
            ->with('flash', 'BAM! Deleted.');
    }
}
