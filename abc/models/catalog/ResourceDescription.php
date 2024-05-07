<?php

namespace abc\models\catalog;

use abc\models\BaseModel;
use abc\models\locale\Language;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ResourceDescription
 *
 * @property int $resource_id
 * @property int $language_id
 * @property string $name
 * @property string $title
 * @property string $description
 * @property string $resource_path
 * @property string $resource_code
 * @property Carbon $date_added
 * @property Carbon $date_modified
 *
 * @property ResourceLibrary $resource_library
 * @property Language $language
 *
 * @package abc\models
 */
class ResourceDescription extends BaseModel
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    protected $primaryKeySet = [
        'resource_id',
        'language_id',
    ];

    public $timestamps = false;

    protected $casts = [
        'resource_id'   => 'int',
        'language_id'   => 'int',
        'date_added'    => 'datetime',
        'date_modified' => 'datetime'
    ];

    protected $rules = [
        /** @see validate() */
        'resource_id' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Resource ID is not Integer!'],
                'min' => ['default_text' => 'Resource ID value must be greater than zero'],
                'max' => ['default_text' => 'Resource ID must be less than 2147483647'],
            ],
        ],
        'language_id' => [
            'checks' => [
                'integer',
                'required',
                'exists:languages',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Language ID is not Integer!'],
                'exists' => ['default_text' => 'Language ID absent in languages table!'],
                'max' => ['default_text' => 'Language ID must be less than 2147483647'],
                'min' => ['default_text' => 'Language ID value must be greater than zero'],
                'required' => ['default_text' => 'Language ID required']
            ],
        ],
    ];

    protected $fillable = [
        'name',
        'title',
        'description',
        'resource_path',
        'resource_code',
        'date_added',
        'date_modified',
    ];

    public function resource_library()
    {
        return $this->belongsTo(ResourceLibrary::class, 'resource_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}
