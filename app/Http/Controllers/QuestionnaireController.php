<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Http\Transformers\PostTransformer;
use App\Managers\PostManager;
use App\Managers\SignupManager;
use App\Models\Campaign;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class QuestionnaireController extends ActivityApiController
{
    /**
     * The post manager instance.
     *
     * @var \App\Managers\PostManager
     */
    protected $posts;

    /**
     * The SignupManager instance.
     *
     * @var \App\Managers\SignupManager
     */
    protected $signups;

    /**
     * @var \App\Http\Transformers\PostTransformer;
     */
    protected $transformer;

    /**
     * Create a controller instance.
     *
     * @param PostManager $posts
     * @param SignupManager $signups
     * @param PostTransformer $transformer
     */
    public function __construct(
        PostManager $posts,
        SignupManager $signups,
        PostTransformer $transformer
    ) {
        $this->posts = $posts;
        $this->signups = $signups;
        $this->transformer = $transformer;

        $this->middleware('auth:api');

        $this->middleware('scope:activity');
        $this->middleware('scope:write');

        $this->rules = array_merge(
            Arr::except((new PostRequest())->rules(), [
                'action', 'action_id', 'campaign_id', 'type',
            ]),
            [
                'questions' => 'required|array',
                'questions.*.action_id' => 'required|integer|exists:mysql.actions,id',
                'questions.*.title' => 'required|string',
                'questions.*.answer' => 'required|string|max:500',
                'contentful_id' => 'required|string',
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->rules);

        $northstarId = getNorthstarId($request);

        $questions = $request->input('questions');

        $posts = [];

        $requestDetails = json_decode($request->input('details'), true) ?: [];

        foreach ($questions as $index => $question) {
            $actionId = $question['action_id'];

            // Get the campaign id from the request by action_id.
            $campaignId = Campaign::fromActionId($actionId)->id;
            $signup = $this->signups->get($northstarId, $campaignId);

            if (!$signup) {
                $signup = $this->signups->create(
                    $request->all(),
                    $northstarId,
                    $campaignId,
                );
            }

            $postDetails = array_merge($requestDetails, [
                'questionnaire' => true,
                'question' => $question['title'],
                'contentful_id' => $request->input('contentful_id'),
            ]);

            $shouldTrackToCustomerIo = $index === 0;

            $post = $this->posts->create(array_merge($request->all(), [
                'action_id' => $actionId,
                'text' => $question['answer'],
                'details' => json_encode($postDetails),

            ]), $signup->id, $shouldTrackToCustomerIo);

            array_push($posts, $post);
        }

        return $this->collection($posts, 201);
    }
}
