<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\Memory\MemoryAdapter;
use League\Glide\Responses\LaravelResponseFactory;
use League\Glide\ServerFactory;

class ImagesController extends Controller
{
    /**
     * The Laravel filesystem adapter.
     *
     * @var \Illuminate\Filesystem\FilesystemAdapter
     */
    protected $filesystem;

    /**
     * Create a controller instance.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        $this->middleware('throttle:60');
    }

    /**
     * Display the specified resource.
     *
     * @param  Post $post
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function show(string $hash, Request $request)
    {
        $post = Post::findByHashOrFail($hash);

        if ($post->type !== 'photo') {
            throw new ModelNotFoundException();
        }

        $server = ServerFactory::create([
            'response' => new LaravelResponseFactory($request),
            'cache' => new Flysystem(new MemoryAdapter()),
            'source' => $this->filesystem->getDriver(),
            'base_url' => 'images',
            'defaults' => [
                'or' => 'auto',
                'w' => 400,
                'h' => 400,
                'fit' => 'crop',
            ],
        ]);

        $response = $server->getImageResponse(
            $post->getMediaPath(),
            $request->all(),
        );

        // We want these to be cached in Fastly to reduce load on our servers. We can then
        // use the 'Surrogate-Key' to easily clear this from Fastly's cache if modified:
        $response->headers->set('Surrogate-Control', 'max-age=31536000');
        $response->headers->set('Surrogate-Key', 'post-' . $post->id);

        // And we want browsers to cache but re-validate with us before returning cached content
        // on a subsequent load, so that image rotations/deletions are respected immediately:
        $response->headers->set(
            'Cache-Control',
            'max-age=31536000, must-revalidate, no-cache, public',
        );

        return $response;
    }
}
