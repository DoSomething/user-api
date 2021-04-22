<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportFile extends Model
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'filepath',
        'import_count',
        'import_type',
        'options',
        'row_count',
        'skip_count',
        'user_id',
    ];

    /**
     * Attributes that can be queried when filtering.
     *
     * This array is manually maintained. It does not necessarily mean that
     * any of these are actual indexes on the database... but they should be!
     *
     * @var array
     */
    public static $indexes = ['import_type'];

    /**
     * Increment and save the number of rows imported.
     *
     * @return void
     */
    public function incrementImportCount()
    {
        $this->increment('import_count');
        $this->save();
    }

    /**
     * Increment and save the number of rows skipped.
     *
     * @return void
     */
    public function incrementSkipCount()
    {
        $this->increment('skip_count');
        $this->save();
    }
}
