<?php

namespace Northstar\Http\Controllers\Traits;

trait FiltersRequests
{
    /**
     * Create a new query builder from the given Eloquent class, which can then be
     * filtered, searched, and/or paginated.
     *
     * @param string $class - Eloquent model class name
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQuery($class)
    {
        return (new $class)->newQuery();
    }

    /**
     * Limit results to records exactly matching a set of filters.
     *
     * @param $query
     * @param $filters
     * @param $indexes - Indexed fields (whitelisted for filtering)
     * @return mixed
     */
    public function filter($query, $filters, $indexes, $operator = '=')
    {
        if (! $filters) {
            return $query;
        }

        // Requests may be filtered by indexed fields.
        $filters = array_intersect_key($filters, array_flip($indexes));

        // You can filter by multiple values, e.g. `filter[source]=agg,cgg`
        // to get records that have a source value of either `agg` or `cgg`.
        foreach ($filters as $filter => $values) {
            $values = is_string($values) ? explode(',', $values) : [$values];

            // For the first `where` query, we want to limit results... from then on,
            // we want to append (e.g. `SELECT * WHERE _ OR WHERE _ OR WHERE _`)
            $firstWhere = true;
            foreach ($values as $value) {
                $query->where($filter, $operator, $value, ($firstWhere ? 'and' : 'or'));
                $firstWhere = false;
            }
        }

        return $query;
    }

    /**
     * Limit results to records matching a set of search terms.
     *
     * @param $query - Query to apply search to
     * @param array $searches - Key/value array of fields and search terms
     * @param array $indexes - Indexed fields (whitelisted for search)
     * @return mixed
     */
    public function search($query, $searches, $indexes)
    {
        if (! $searches) {
            return $query;
        }

        if (is_string($searches)) {
            multipleValueQuery($query, $searches, $indexes);
        } else {
            // Searches may only be performed on indexed fields.
            $searches = array_intersect_key($searches, array_flip($indexes));
            // For the first `where` query, we want to limit results... from then on,
            // we want to append (e.g. `SELECT * WHERE _ OR WHERE _ OR WHERE _`)
            $firstWhere = true;
            foreach ($searches as $term => $value) {
                $query->where($term, '=', $value, ($firstWhere ? 'and' : 'or'));
                $firstWhere = false;
            }
        }

        return $query;
    }
}
