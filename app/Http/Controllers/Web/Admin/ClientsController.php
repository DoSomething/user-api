<?php

namespace App\Http\Controllers\Web\Admin;

use App\Auth\Scope;
use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');

        $this->middleware('role:admin');
    }

    /**
     * Display a listing of the resource.
     * GET /clients.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $clients = Client::simplePaginate();

        return view('admin.clients.index', ['clients' => $clients]);
    }

    /**
     * Show client details.
     * GET /clients/:client_id.
     *
     * @param Client $client
     * @return \Illuminate\Http\Response
     */
    public function show(Client $client)
    {
        return view('admin.clients.show', ['client' => $client]);
    }

    /**
     * Create a new client.
     * GET /clients/create.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $scopes = Scope::all();

        return view('admin.clients.create', ['scopes' => $scopes]);
    }

    /**
     * Store a new client.
     * POST /clients.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Transform 'redirect_uri' from a CSV into an array of strings.
        $request['redirect_uri'] = csv_to_array($request['redirect_uri']);

        // The Client Credentials grant does not use Redirect URIs:
        if ($request['allowed_grant'] === 'client_credentials') {
            unset($request['redirect_uri']);
        }

        $parameters = $this->validate(
            $request,
            [
                'client_id' =>
                    'required|alpha_dash|unique:mongodb.clients,client_id',
                'title' => 'required|string',
                'description' => 'string',
                'scope' => 'array|scope', // @see Scope::validateScopes
                'allowed_grant' =>
                    'string|in:authorization_code,password,client_credentials',
                'redirect_uri' =>
                    'array|required_if:allowed_grant,authorization_code',
                'redirect_uri.*' => 'url',
            ],
            [
                'redirect_uri.*.url' =>
                    'The :attribute field is not a valid URL.',
            ],
        );

        $client = Client::create($parameters);

        return redirect()
            ->route('admin.clients.show', ['client' => $client->client_id])
            ->with('flash', 'Cool, new app added!');
    }

    /**
     * Edit client details.
     * GET /clients/:client_id/edit.
     *
     * @param NorthstarClient $client
     * @return \Illuminate\Http\Response
     */
    public function edit(Client $client)
    {
        $scopes = Scope::all();

        return view('admin.clients.edit', [
            'client' => $client,
            'scopes' => $scopes,
        ]);
    }

    /**
     * Update an existing client's details.
     * PUT /clients/:client_id.
     *
     * @param Client $client
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Client $client, Request $request)
    {
        // Transform 'redirect_uri' from a CSV into an array of strings.
        $request['redirect_uri'] = csv_to_array($request['redirect_uri']);

        // The Client Credentials grant does not use Redirect URIs:
        if ($request['allowed_grant'] === 'client_credentials') {
            unset($request['redirect_uri']);
        }

        $parameters = $this->validate(
            $request,
            [
                'title' => 'string',
                'description' => 'string',
                'scope' => 'array|scope', // @see Scope::validateScopes
                'allowed_grant' =>
                    'string|in:authorization_code,password,client_credentials',
                'redirect_uri' =>
                    'array|required_if:allowed_grant,authorization_code',
                'redirect_uri.*' => 'url',
            ],
            [
                'redirect_uri.*.url' =>
                    'The :attribute field is not a valid URL.',
            ],
        );

        // Ensure that all scopes can be removed from a client.
        $parameters['scope'] = !empty($parameters['scope'])
            ? $parameters['scope']
            : [];

        $client->update($parameters);

        return redirect()
            ->route('admin.clients.show', ['client' => $client->client_id])
            ->with('flash', 'Cool, saved those changes!');
    }

    /**
     * Destroy an existing client.
     * DELETE /clients/:client_id.
     *
     * @param Client $client
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()
            ->route('admin.clients.index')
            ->with('flash', 'BAM! Deleted.');
    }
}
