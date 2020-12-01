<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use Northstar\Models\Client;
use Northstar\Http\Transformers\ClientTransformer;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ClientController extends Controller
{
    /**
     * @var ClientTransformer
     */
    protected $transformer;

    public function __construct(ClientTransformer $transformer)
    {
        $this->transformer = $transformer;

        $this->middleware('role:admin');
        $this->middleware('scope:client');
        $this->middleware('scope:write', [
            'only' => ['store', 'update', 'destroy'],
        ]);
    }

    /**
     * Display a listing of the resource.
     * GET /v2/clients
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $clients = $this->newQuery(Client::class)->orderBy('client_id', 'asc');

        return $this->paginatedCollection($clients, $request);
    }

    /**
     * Store a newly created resource in storage.
     * POST /v2/clients
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws HttpException
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'client_id' => 'required|alpha_dash|unique:clients,client_id',
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

        $key = Client::create($request->except('client_secret'));

        return $this->item($key, 201);
    }

    /**
     * Display the specified resource.
     * GET /v2/clients/:client_id
     *
     * @param $client_id
     * @return \Illuminate\Http\Response
     */
    public function show($client_id)
    {
        $client = Client::findOrFail($client_id);

        return $this->item($client);
    }

    /**
     * Update the specified resource.
     * PUT /v2/clients/:client_id
     *
     * @param $client_id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update($client_id, Request $request)
    {
        $this->validate(
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

        $client = Client::findOrFail($client_id);
        $client->update($request->except('client_id', 'client_secret'));

        return $this->item($client);
    }

    /**
     * Delete an API key resource.
     * DELETE /v2/clients/:client_id
     *
     * @param $client_id
     * @return \Illuminate\Http\Response
     */
    public function destroy($client_id)
    {
        $client = Client::findOrFail($client_id);
        $client->delete();

        return $this->respond('Deleted client.', 200);
    }
}
