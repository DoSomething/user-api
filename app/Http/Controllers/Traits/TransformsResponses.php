<?php

namespace Northstar\Http\Controllers\Traits;

use Illuminate\Pagination\Paginator;
use League\Fractal\Manager;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Serializer\DataArraySerializer;

trait TransformsResponses
{
    /**
     * The default transformer.
     *
     * @var \League\Fractal\TransformerAbstract
     */
    protected $transformer;

    /**
     * Transform the given item or collection.
     *
     * @param $resource
     * @return \Illuminate\Http\Response
     */
    public function transform($resource, $code)
    {
        $manager = new Manager();
        $manager->parseIncludes(request()->query('include', ''));
        $manager->setSerializer(new DataArraySerializer());
        $response = $manager->createData($resource)->toArray();

        return response()->json($response, $code, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Format & return a single item response.
     *
     * @param $item
     * @param int $code
     * @param array $meta
     * @param null $transformer
     * @return \Illuminate\Http\Response
     */
    public function item($item, $code = 200, $meta = [], $transformer = null)
    {
        if (is_null($transformer)) {
            $transformer = $this->transformer;
        }

        $resource = new FractalItem($item, $transformer, 'thing');
        $resource->setMeta($meta);

        return $this->transform($resource, $code);
    }

    /**
     * Format & return a collection response.
     *
     * @param $collection
     * @param int $code
     * @param array $meta
     * @param null $transformer
     * @return \Illuminate\Http\JsonResponse
     */
    public function collection(
        $collection,
        $code = 200,
        $meta = [],
        $transformer = null
    ) {
        if (is_null($transformer)) {
            $transformer = $this->transformer;
        }

        $resource = new FractalCollection($collection, $transformer, 'things');
        $resource->setMeta($meta);

        return $this->transform($resource, $code);
    }

    /**
     * Format & return a paginated collection response.
     *
     * @param $query - Eloquent query
     * @return \Illuminate\Http\Response
     */
    public function paginatedCollection(
        $query,
        $request,
        $code = 200,
        $meta = [],
        $transformer = null
    ) {
        if (is_null($transformer)) {
            $transformer = $this->transformer;
        }

        $pages = (int) $request->query('limit', 20);

        // Experimental: Should we go at warp speed?
        $fastMode = $request->query('pagination') === 'cursor';
        if ($fastMode) {
            $paginator = $query->simplePaginate(min($pages, 100));
        } else {
            $paginator = $query->paginate(min($pages, 100));
        }

        $queryParams = array_diff_key($request->query(), array_flip(['page']));
        $paginator->appends($queryParams);

        $resource = new FractalCollection(
            $paginator->getCollection(),
            $transformer,
        );

        $resource->setMeta($meta);

        // Attach the right paginator or cursor based on "speed".
        if ($fastMode) {
            $cursor = new Cursor(
                $paginator->currentPage(),
                $paginator->previousPageUrl(),
                $paginator->nextPageUrl(),
                $paginator->count(),
            );
            $resource->setCursor($cursor);
        } else {
            $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        }

        return $this->transform($resource, $code);
    }

    /**
     * Return a string as the API response.
     *
     * @param string $message - Message to send in the response
     * @param int $code - Status code
     * @param string $status - The name of the object enclosing the message
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function respond($message, $code = 200, $status = 'success')
    {
        $response = [
            $status => [
                'code' => $code,
                'message' => $message,
            ],
        ];

        return response()->json($response, $code, [], JSON_UNESCAPED_SLASHES);
    }
}
