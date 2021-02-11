<?php

namespace App\Http\Controllers;

use App\Http\Transformers\PostTransformer;
use App\Models\Post;
use App\Services\Fastly;
use App\Services\ImageStorage;
use Illuminate\Http\Request;

class RotationController extends Controller
{
    /**
     * The Fastly API.
     *
     * @var Fastly
     */
    protected $fastly;

    /**
     * The image storage backend.
     *
     * @var ImageStorage
     */
    protected $storage;

    /**
     * @var PostTransformer;
     */
    protected $transformer;

    /**
     * Create a controller instance.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Fastly $fastly, ImageStorage $storage)
    {
        $this->fastly = $fastly;
        $this->storage = $storage;
        $this->transformer = new PostTransformer();

        $this->middleware('auth:api');
        $this->middleware('role:staff,admin');
    }

    /**
     * Edits and overwrites an image based on given request parameters.
     *
     * @param  Post $post
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Post $post, Request $request)
    {
        $validatedInput = $this->validate($request, [
            'degrees' => 'required|int',
        ]);

        $image = $this->storage->get($post);

        // We use clockwise degrees, but Intervention rotates images
        // counter-clockwise <http://image.intervention.io/api/rotate>
        $rotatedImage = $image->rotate(-1 * $validatedInput['degrees']);

        // Overwrite with edited image & purge cache:
        $this->storage->edit($post, $rotatedImage);
        $this->fastly->purge($post);

        return $this->item($post);
    }
}
