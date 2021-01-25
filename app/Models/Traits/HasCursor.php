<?php

namespace App\Models\Traits;

trait HasCursor
{
    /**
     * Create a cursor for this item.
     *
     * @return string
     */
    public function getCursor()
    {
        $sortCursor = '';

        // If we're ordering results, we need to include that column's
        // value in the cursor (so we can say "get me things after this"):
        if ($orderBy = request()->query('orderBy')) {
            [$column, $direction] = explode(',', $orderBy, 2);

            // Check that it's okay to expose this column in the cursor:
            if (in_array($column, self::$sortable)) {
                $sortCursor = '.' . $this->{$column};
            }
        }

        return base64_encode($this->id . $sortCursor);
    }

    /**
     * Scope a query to only include items after the given cursor.
     */
    public function scopeWhereAfterCursor(
        $query,
        $cursor,
        $defaultSort = 'id,desc'
    ) {
        $cursor = explode('.', base64_decode($cursor), 2);

        $id = $cursor[0];
        $sortCursor = isset($cursor[1]) ? $cursor[1] : null;

        $orderBy = request()->query('orderBy');

        /**
         * Because we may be joining tables, specify the base query table name
         * to avoid integrity constraint violations for ambiguous clauses.
         */
        $idField = $query->getModel()->getTable() . '.id';

        // If we're sorting by anything other than ID, things get a lil' tricky. First,
        // we'll extract the sorted column & direction from the `?orderBy` query string:
        if ($orderBy && $orderBy !== 'id,asc' && !is_null($sortCursor)) {
            [$column, $direction] = explode(',', $orderBy, 2);
            $operator = $direction === 'asc' ? '>' : '<';

            // Then, we'll check that we're allowed to sort by this column (to prevent
            // someone from using a cursor to sort by an unindexed or sensitive field):
            if (in_array($column, self::$sortable)) {
                // There are two ways an item might be found "after" this cursor:
                //   1. It has a value in the sorted column that is "after" the cursor.
                //   2. It has the same "sorted" value, but a higher ID (secondary sort).
                $query->where($column, $operator, $sortCursor);
                $query->orWhere([
                    [$column, '=', $sortCursor],
                    [$idField, '>', $id],
                ]);

                // @TODO: This gets a lot more complicated if the sorted column can have nulls...
            }
        } else {
            // Otherwise, treat as a plain ID cursor... easy!
            $query->where($idField, '>', $id);
        }
    }
}
