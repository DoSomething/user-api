<?php

namespace Northstar\Http\Controllers;

use Northstar\Exceptions\NorthstarValidationException;
use Northstar\Models\User;
use Illuminate\Http\Request;
use Northstar\Http\Transformers\UserTransformer;
use Northstar\Merge\Merger;

class MergeController extends Controller
{
    /**
     * @var UserTransformer
     */
    protected $transformer;

    /**
     * @var UserTransformer
     */
    protected $merger;

    /**
     * Make a new MergeController, inject dependencies,
     * and set middleware for this controller's methods.
     *
     * @param UserTransformer $transformer
     */
    public function __construct(UserTransformer $transformer, Merger $merger)
    {
        $this->transformer = $transformer;
        $this->merger = $merger;

        $this->middleware('role:admin,staff');
    }

    /**
     * EXPERIMENTAL: Merge two user accounts into one.
     * POST /users/:id/merge
     *
     * @param string $id - the "destination" account
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     * @throws NorthstarValidationException
     */
    public function store($id, Request $request)
    {
        $this->validate($request, [
            'id' => ['required', 'exists:users,_id', 'not_in:'.$id],
        ]);

        /** @var \Northstar\Models\User $target */
        $target = User::findOrFail($id);

        /** @var \Northstar\Models\User $duplicate */
        $duplicate = User::findOrFail($request->input('id'));

        // Get all profile fields from the duplicate (except metadata like ID or source).
        $metadata = ['_id', 'updated_at', 'created_at', 'drupal_id', 'source', 'source_detail', 'role', 'audit'];
        $duplicateFields = array_except($duplicate->toArray(), $metadata);
        $duplicateFieldNames = array_keys($duplicateFields);

        // Find out which fields we need to handle merging
        $intersectedFields = array_intersect_key($target->toArray(), array_flip($duplicateFieldNames));

        // Fields that we can automatically merge
        $fieldsToMerge = array_except($duplicateFields, array_keys($intersectedFields));
        // Are there fields we can't automatically merge? Throw an error.
        if ($intersectedFields) {
            $unmergedFields = [];

            // Call merge on intersecting fields
            foreach ($intersectedFields as $field => $value) {
                $merged = $this->merger->merge($field, $target, $duplicate);
                if (! $merged) {
                    array_push($unmergedFields, $field);
                } else {
                    $fieldsToMerge[$field] = $merged;
                }
            }

            if ($unmergedFields) {
                $errors = collect($unmergedFields)->map(function ($fieldName) {
                    return 'Cannot merge "'.$fieldName.'" into non-null field on target.';
                });

                throw new NorthstarValidationException($errors, ['target' => $target, 'duplicate' => $duplicate]);
            }
        }

        // Copy the "duplicate" account's fields to the target & unset on the dupe account.
        foreach ($fieldsToMerge as $field => $value) {
            $target->$field = $fieldsToMerge[$field];
        }

        foreach ($duplicateFieldNames as $field) {
            $duplicate->$field = null;
        }

        if (empty($duplicate->email) && empty($duplicate->mobile)) {
            $duplicate->email = 'merged-account-'.$target->id.'@dosomething.invalid';
        }

        // Copy over created_at & source information if it's earlier than the target's timestamp.
        $duplicateUserHasEarlierCreatedTimestamp = $duplicate->created_at->lt($target->created_at);
        if ($duplicateUserHasEarlierCreatedTimestamp) {
            $target->created_at = $duplicate->created_at;
            $target->source = $duplicate->source;
            $target->source_detail = $duplicate->source_detail;
        }

        // Are we "pretending" for this request? If so, short-circuit and display the (unsaved) result.
        if ($request->query('pretend', false)) {
            return $this->item($target, 200, [
                'pretending' => true,
                'updated' => array_keys($duplicateFields),
                'duplicate' => $duplicate->toArray(),
            ]);
        }

        // Save the changes to the two accounts.
        $duplicate->save();
        $target->save();

        return $this->item($target, 200, [
            'updated' => array_keys($duplicateFields),
            'duplicate' => $duplicate->toArray(),
        ]);
    }
}
