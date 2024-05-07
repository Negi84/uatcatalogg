<?php
/**
 * AbanteCart, Ideal Open Source Ecommerce Solution
 * http://www.abantecart.com
 *
 * Copyright 2011-2023 Belavier Commerce LLC
 *
 * This source file is subject to Open Software License (OSL 3.0)
 * License details is bundled with this package in the file LICENSE.txt.
 * It is also available at this URL:
 * <http://www.opensource.org/licenses/OSL-3.0>
 *
 * UPGRADE NOTE:
 * Do not edit or add to this file if you wish to upgrade AbanteCart to newer
 * versions in the future. If you wish to customize AbanteCart for your
 * needs please refer to http://www.abantecart.com for more information.
 *
 */

namespace abc\models\catalog;

use abc\core\ABC;
use abc\core\engine\HtmlElementFactory;
use abc\core\engine\Registry;
use abc\core\lib\AException;
use abc\core\lib\AResourceManager;
use abc\core\lib\AttributeManager;
use abc\models\BaseModel;
use abc\models\casts\Serialized;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * Class ProductOption
 *
 * @property int $product_option_id
 * @property int $attribute_id
 * @property int $product_id
 * @property int $group_id
 * @property int $sort_order
 * @property int $status
 * @property string $element_type
 * @property int $required
 * @property string $regexp_pattern
 * @property array $settings
 * @property Carbon $date_added
 * @property Carbon $date_modified
 *
 * @property ProductOptionValue $values
 * @property Product $product
 * @property ProductOptionDescription $description
 * @property ProductOptionDescription $descriptions
 *
 * @package abc\models
 */
class ProductOption extends BaseModel
{
    protected $cascadeDeletes = ['descriptions', 'values'];

    protected $mainClassName = Product::class;
    protected $mainClassKey = 'product_id';
    protected $primaryKey = 'product_option_id';
    protected $touches = ['product'];

    protected $casts = [
        'attribute_id'  => 'int',
        'product_id'    => 'int',
        'group_id'      => 'int',
        'sort_order'    => 'int',
        'status'        => 'int',
        'required'      => 'int',
        'settings'      => Serialized::class,
        'date_added'    => 'datetime',
        'date_modified' => 'datetime'
    ];

    protected $fillable = [
        'product_id',
        'attribute_id',
        'group_id',
        'sort_order',
        'status',
        'element_type',
        'required',
        'regexp_pattern',
        'settings',
    ];

    protected $rules = [
        /** @see validate() */
        'product_id' => [
            'checks' => [
                'integer',
                'required',
                'exists:products',
                'max:2147483647',
                'min:0',
            ],
            'messages' => [
                'integer' => ['default_text' => 'Product ID is not Integer!'],
                'exists' => ['default_text' => 'Product ID absent in products table!'],
                'max' => ['default_text' => 'Product ID must be less than 2147483647'],
                'min' => ['default_text' => 'Product ID value must be greater than zero'],
                'required' => ['default_text' => 'Product ID required']
            ],
        ],
        'attribute_id' => [
            'checks' => [
                'integer',
                'nullable',
                'exists:global_attributes',
                'max:2147483647',
                'min:0',
            ],
            'messages' => [
                'integer' => ['default_text' => 'Attribute ID is not Integer'],
                'exists' => ['default_text' => 'Attribute ID not presents in global_attributes table!'],
                'max' => ['default_text' => 'Attribute ID must be less than 2147483647'],
                'min' => ['default_text' => 'Attribute ID value must be greater than zero'],
                'required' => ['default_text' => 'Attribute ID required']
            ],
        ],
        'group_id' => [
            'checks' => [
                'integer',
                'nullable',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => 'Group ID is not integer!'],
                'max' => ['default_text' => 'Group ID must be less than 2147483647'],
                'min' => ['default_text' => 'Group ID value must be greater than zero'],
            ],
        ],
        'sort_order' => [
            'checks' => [
                'integer',
                'min:0',
                'max:2147483647'
            ],
            'messages' => [
                'integer' => ['default_text' => ':attribute is not integer!'],
                'min' => ['default_text' => ':attribute value must be greater than zero'],
                'max' => ['default_text' => ':attribute must be less than 2147483647'],
            ],
        ],
        'status' => [
            'checks' => [
                'boolean',
            ],
            'messages' => [
                '*' => [
                    'default_text' => ':attribute is not boolean!',
                ],
            ],
        ],

        'element_type' => [
            'checks' => [
                'string',
                'size:1',
                /** @see __construct() method */
            ],
            'messages' => [
                '*' => [
                    'default_text' => ':attribute must be 1 character length and presents in element_types list of AHtml class!',
                ],
            ],
        ],

        'required' => [
            'checks' => [
                'boolean',
            ],
            'messages' => [
                '*' => [
                    'default_text' => ':attribute is not boolean!',
                ],
            ],
        ],

        'regexp_pattern' => [
            'checks' => [
                'string',
                'nullable',
            ],
            'messages' => [
                '*' => [
                    'default_text' => 'Blurb must be less than 1500 characters!',
                ],
            ],
        ],
    ];

    public function __construct(array $attributes = [])
    {
        $letters = array_keys(HtmlElementFactory::getAvailableElements());
        $this->rules['element_type']['checks'][] = Rule::in($letters);
        parent::__construct($attributes);
    }

    /**
     * @return BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * @return HasMany
     */
    public function descriptions()
    {
        return $this->hasMany(ProductOptionDescription::class, 'product_option_id');
    }

    /**
     * @return HasOne
     */
    public function description()
    {
        return $this->hasOne(ProductOptionDescription::class, 'product_option_id')
                    ->where('language_id', '=', static::$current_language_id);
    }

    /**
     * @return HasMany
     */
    public function values()
    {
        return $this->hasMany(ProductOptionValue::class, 'product_option_id');
    }

    /**
     * @return false|array
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws AException
     */
    public function getAllData()
    {
        if (!$this->getKey()) {
            return false;
        }
        // eagerLoading!
        $toLoad = $nested = [];
        $rels = $this->getRelationships('HasMany', 'HasOne', 'belongsToMany');
        foreach ($rels as $relName => $rel) {
            if ($rel['getAllData']) {
                $nested[] = $relName;
            } else {
                $toLoad[] = $relName;
            }
        }
        $this->load($toLoad);
        $data = $this->toArray();
        foreach ($nested as $prop) {
            foreach ($this->{$prop} as $option) {
                /** @var ProductOptionValue $option */
                $data[$prop][] = $option->getAllData();
            }
        }
        return $data;
    }

    public function delete()
    {
        $this->load('values');
        if ($this->values) {
            /**
             * @var AResourceManager $rm
             */
            $rm = ABC::getObjectByAlias('AResourceManager');
            $rm->setType('image');
            foreach ($this->values as $option_value) {
                //Remove previous resources of object
                $rm->unmapAndDeleteResources('product_option_value', $option_value->product_option_value_id);
            }
        }
        parent::delete();
    }

    /**
     * @param array $po_ids
     *
     * @return bool|Collection
     */
    public static function getProductOptionsByIds($po_ids)
    {

        if (!$po_ids || !is_array($po_ids)) {
            return false;
        }
        $query = static::select(
            [
                'product_options.*',
                'product_option_values.*',
                'product_option_descriptions.name as option_name',
                'product_option_value_descriptions.name as option_value_name',

            ]
        );
        $query->whereIn('product_options.product_option_id', $po_ids)
              ->leftJoin(
                  'product_option_descriptions',
                  function ($join) {
                      /** @var JoinClause $join */
                      $join->on('product_option_descriptions.product_option_id', '=',
                          'product_options.product_option_id')
                           ->where('product_option_descriptions.language_id', '=',
                               static::$current_language_id);
                  }
              )->leftJoin(
                'product_option_values',
                'product_options.product_option_id',
                '=',
                'product_option_values.product_option_id'
            )->leftJoin(
                'product_option_value_descriptions',
                function ($join) {
                    /** @var JoinClause $join */
                    $join->on(
                        'product_option_value_descriptions.product_option_value_id',
                        '=',
                        'product_option_values.product_option_value_id'
                    )->where(
                        'product_option_value_descriptions.language_id',
                        '=',
                        static::$current_language_id
                    );
                }
            )->orderBy('product_options.product_option_id');

        Registry::extensions()->hk_extendQuery(new static, __FUNCTION__, $query, func_get_args());
        return $query->useCache('product')->get();
    }

    /**
     * @param array $inData - must contains product_id, product_option_value_id
     *
     * @return int|null
     * @throws Exception
     */
    public static function addProductOptionValueAndDescription(array $inData)
    {
        if (empty($inData) || !$inData['product_id'] || !$inData['product_option_id']) {
            return false;
        }

        $optionData = $inData;
        if (is_array($inData['attribute_value_id'])) {
            unset($optionData['attribute_value_id']);
        } else {
            $optionData['attribute_value_id'] = (int)$optionData['attribute_value_id'];
            if (!$optionData['attribute_value_id']) {
                unset($optionData['attribute_value_id']);
            } else {
                $inData['attribute_value_id'] = [$optionData['attribute_value_id']];
            }

        }

        /**
         * @var AttributeManager $am
         */
        $am = ABC::getObjectByAlias('AttributeManager');
        //build grouped attributes if this is a parent attribute
        if (is_array($inData['attribute_value_id'])) {
            //add children option values from global attributes
            $groupData = [];
            foreach ($inData['attribute_value_id'] as $child_option_id => $attribute_value_id) {
                #special data for grouped options. will be serialized in model mutator
                $groupData[] = [
                    'attr_id'   => $child_option_id,
                    'attr_v_id' => $attribute_value_id,
                ];
            }
            $optionData['grouped_attribute_data'] = $groupData;
        }

        $optionValue = ProductOptionValue::create($optionData);
        $optionValueId = $optionValue->getKey();

        //Build options value descriptions
        if (is_array($inData['attribute_value_id'])) {
            //add children option values description from global attributes
            $group_description = [];
            $descriptionNames = [];
            foreach ($inData['attribute_value_id'] as $attribute_value_id) {
                #special insert for grouped options
                foreach ($am->getAttributeValueDescriptions($attribute_value_id) as $language_id => $name) {
                    $group_description[$language_id][] = [
                        'attr_v_id' => $attribute_value_id,
                        'name'      => $name,
                    ];
                    $descriptionNames[$language_id][] = $name;
                }
            }

            // Insert generic merged name
            foreach ($descriptionNames as $language_id => $name) {
                ProductOptionValueDescription::create(
                    [
                        'product_id'              => $optionValue->product_id,
                        'product_option_value_id' => $optionValueId,
                        'language_id'             => $language_id,
                        'name'                    => implode(' / ', $name),
                        //note: serialized data (array)
                        'grouped_attribute_names' => $group_description[$language_id],
                    ]
                );
            }

        } else {
            if (!$inData['attribute_value_id']) {
                //We save custom option value for current language
                if (isset($inData['descriptions'])) {
                    $valueDescriptions = $inData['descriptions'];
                } elseif (isset($inData['description'])) {
                    $valueDescriptions = [
                        static::$current_language_id => [
                            'name' => ($inData['description']['name'] ?? 'Unknown'),
                        ],
                    ];
                } else {
                    $valueDescriptions = [
                        static::$current_language_id =>
                            [
                                'name' => $inData['name'] ?? 'Unknown',
                            ],
                    ];
                }
            } else {
                //We have global attributes, copy option value text from there.
                $valueDescriptions = $am->getAttributeValueDescriptions((int)$inData['attribute_value_id']);
            }

            foreach ($valueDescriptions as $language_id => $description) {
                $language_id = (int)$language_id;
                if (!$language_id) {
                    throw new Exception('Wrong format of input data! Description of value must have the language ID as a key!');
                }

                $desc = $description;
                $desc['product_id'] = $optionValue->product_id;
                $desc['product_option_value_id'] = $optionValueId;
                $desc['language_id'] = $language_id;

                ProductOptionValueDescription::create($desc);
            }
        }
        Registry::cache()->flush('product');
        return $optionValueId;
    }

    /**
     * @param array $inData - must contains product_id
     *
     * @return bool|int
     * @throws Exception
     */
    public static function addProductOption($inData)
    {
        if (empty($inData) || !$inData['product_id']) {
            return false;
        }
        /** @var AttributeManager $am */
        $am = ABC::getObjectByAlias('AttributeManager');
        $attribute = $am->getAttribute($inData['attribute_id']);

        if ($attribute) {
            $inData['element_type'] = $attribute['element_type'];
            $inData['required'] = $attribute['required'];
            $inData['regexp_pattern'] = $attribute['regexp_pattern'];
            $inData['placeholder'] = $attribute['placeholder'];
            $inData['sort_order'] = $attribute['sort_order'];
            $inData['settings'] = $attribute['settings'];
        } else {
            $inData['placeholder'] = $inData['option_placeholder'];
        }

        $option = ProductOption::create($inData);
        $product_option_id = $option->getKey();

        if ($inData['option_name']) {
            $inData['name'] = $inData['option_name'];
        }
        $inData['product_option_id'] = $product_option_id;

        if (!empty($inData['option_name'])) {
            $attributeDescriptions = [
                static::$current_language_id => $inData,
            ];
        } else {
            $attributeDescriptions = $am->getAttributeDescriptions($inData['attribute_id']);
        }

        foreach ($attributeDescriptions as $language_id => $description) {
            $description['product_id'] = $inData['product_id'];
            $description['product_option_id'] = $inData['product_option_id'];
            $description['language_id'] = $language_id;
            $description['option_placeholder'] = $inData['placeholder'];
            ProductOptionDescription::create($description);
        }

        //add empty option value for single value attributes
        $elements_with_options = HtmlElementFactory::getElementsWithOptions();
        if (!in_array($inData['element_type'], $elements_with_options)) {
            ProductOptionValue::create($inData);
        }

        return $product_option_id;
    }

    /**
     * @param array $inData
     *
     * @return bool
     * @throws Exception
     */
    public static function updateProductOptionValues(array $inData)
    {
        if (!is_array($inData['product_option_value_id']) || !$inData['product_option_id'] || !$inData['product_id']) {
            return false;
        }

        $rowIndexes = array_keys($inData['product_option_value_id']);
        $dataKeys = array_keys($inData);

        foreach ($rowIndexes as $rowIndex) {
            $rowIndex = is_numeric($rowIndex) ? (int)$rowIndex : $rowIndex;

            $status = $inData['product_option_value_id'][$rowIndex];
            $option_value_data = [];
            foreach ($dataKeys as $key) {
                if (!is_array($inData[$key])) {
                    $option_value_data[$key] = $inData[$key];
                } elseif (isset($inData[$key][$rowIndex])) {
                    $option_value_data[$key] = $inData[$key][$rowIndex];
                }
            }

            $option_value_data['default'] = ($inData['default_value'] == $rowIndex);

            //Check if new, delete or update
            if ($status == 'delete' && !str_contains($rowIndex, 'new')) {
                //delete this option value for all languages
                /** @var ProductOptionValue $value */
                $value = ProductOptionValue::find($rowIndex);
                $value?->forceDelete();
            } else {
                if ($status == 'new') {
                    // Need to create new option value
                    ProductOption::addProductOptionValueAndDescription($option_value_data);
                } else {
                    //Existing need to update
                    static::updateProductOptionValueAndDescription(
                        $rowIndex,
                        $option_value_data);
                }
            }
        }
        return true;
    }

    /**
     * @param int $productOptionValueId
     * @param array $inData
     *
     * @return void
     * @throws Exception
     */
    public static function updateProductOptionValueAndDescription($productOptionValueId, $inData)
    {
        $data = $inData;
        $currentLanguageId = $data['language_id'] ?? static::$current_language_id;
        $productId = $data['product_id'];
        if (is_array($data['attribute_value_id']) || !$data['attribute_value_id']) {
            unset($data['attribute_value_id']);
        } else {
            $data['attribute_value_id'] = (int)$data['attribute_value_id'];
            if (!$data['attribute_value_id']) {
                unset($data['attribute_value_id']);
            }
        }

        /**
         * @var AttributeManager $am
         */
        $am = ABC::getObjectByAlias('AttributeManager');
        //build grouped attributes if this is a parent attribute
        if (is_array($inData['attribute_value_id'])) {
            //update children option values from global attributes
            $groupData = [];
            foreach ($inData['attribute_value_id'] as $childOptionId => $attributeValueId) {
                #special serialized data for grouped options
                $groupData[$childOptionId] = [
                    'attr_id'   => (int)$childOptionId,
                    'attr_v_id' => (int)$attributeValueId,
                ];
            }
            $data['grouped_attribute_data'] = $groupData;
        }

        $optionValue = ProductOptionValue::find($productOptionValueId);
        $optionValue?->update($data);

        if (is_array($inData['attribute_value_id'])) {
            //update children option values description from global attributes
            $groupDescription = [];
            $descriptionNames = [];
            foreach ($data['attribute_value_id'] as $attributeValueId) {
                #special insert for grouped options
                foreach ($am->getAttributeValueDescriptions($attributeValueId) as $languageId => $name) {
                    if ($currentLanguageId == $languageId) {
                        $groupDescription[$currentLanguageId][] = [
                            'attr_v_id' => $attributeValueId,
                            'name'      => $name,
                        ];
                        $descriptionNames[$currentLanguageId][] = $name;
                    }
                }
            }
            // update generic merged name
            foreach ($descriptionNames as $languageId => $name) {
                if ($currentLanguageId == $languageId && count($groupDescription[$currentLanguageId])) {
                    $groupDescription[$currentLanguageId][] = $name;

                    $upd = ['name' => implode(' / ', $name)];
                    if ($groupDescription[$currentLanguageId]) {
                        //note: serialized data (array)
                        $upd['grouped_attribute_names'] = $groupDescription[$currentLanguageId];
                    }
                    ProductOptionValueDescription::where(
                        [
                            'product_id'              => $productId,
                            'product_option_value_id' => $productOptionValueId,
                            'language_id'             => $currentLanguageId,
                        ]
                    )->update($upd);
                }
            }
        } else {
            if (!$inData['attribute_value_id']) {
                $exist = ProductOptionValueDescription::where(
                    [
                        'product_id'              => $productId,
                        'product_option_value_id' => $productOptionValueId,
                        'language_id'             => $currentLanguageId,
                    ]
                )->first();
                if ($exist) {
                    $exist->update(['name' => $data['name']]);
                } else {
                    ProductOptionValueDescription::create(
                        [
                            'product_id'              => $productId,
                            'product_option_value_id' => $productOptionValueId,
                            'name'                    => $data['name'],
                            'language_id'             => $currentLanguageId,
                        ]
                    );
                }
            } else {
                $valueDescriptions = $am->getAttributeValueDescriptions((int)$inData['attribute_value_id']);
                foreach ($valueDescriptions as $languageId => $name) {
                    if ($currentLanguageId == $languageId) {
                        //Update only language that we currently work with
                        ProductOptionValueDescription::where(
                            [
                                'product_id'              => $productId,
                                'product_option_value_id' => $productOptionValueId,
                                'language_id'             => $currentLanguageId,
                            ]
                        )->update(['name' => $name]);
                    }
                }
            }
        }
        Registry::cache()->flush('product');
    }

    /**
     * check attribute before add to product options
     * can't add attribute that is already in group attribute that assigned to product
     *
     * @param $product_id
     * @param $attribute_id
     *
     * @return int
     * @throws Exception
     */
    public static function isProductGroupOption($product_id, $attribute_id = null)
    {
        return ProductOption::where(
            [
                'product_id'   => $product_id,
                'attribute_id' => $attribute_id,
            ]
        )->whereNotNull('group_id')
            ->useCache('product')->count();
    }

    //????? unfinished work? SEE ACart for usage!!!!
    public function getProductOptionValueArray($option_value_id)
    {
        /** @var ProductOptionValue $product_option_value */
        $product_option_value = ProductOptionValue::with('description')
                                                  ->whereNull('group_id')
                                                  ->find($option_value_id);
        if (!$product_option_value) {
            return [];
        }
        //when asking value of another product - throw exception
        if ($this->product_id != $product_option_value->product_id) {
            throw new Exception('Option value not found for productID '.$this->product_id);
        }

        $result = $product_option_value->toArray();

//        $option_value = $product_option_value->row;
//        $value_description_data = [];
//        $value_description = $this->db->query(
//            "SELECT *
//            FROM ".$this->db->table_name("product_option_value_descriptions")."
//            WHERE product_option_value_id = '".(int)$option_value['product_option_value_id']."'");
//
//        foreach ($value_description->rows as $description) {
//            //regular option value name
//            $value_description_data[$description['language_id']]['name'] = $description['name'];
//            //get children (grouped options) individual names array
//            if ($description['grouped_attribute_names']) {
//                $value_description_data[$description['language_id']]['children_options_names'] =
//                    unserialize($description['grouped_attribute_names']);
//            }
//        }

        $result = [
            'product_option_value_id' => $option_value['product_option_value_id'],
            'language'                => $value_description_data,
            'sku'                     => $option_value['sku'],
            'quantity'                => $option_value['quantity'],
            'subtract'                => $option_value['subtract'],
            'price'                   => $option_value['price'],
            'prefix'                  => $option_value['prefix'],
            'weight'                  => $option_value['weight'],
            'weight_type'             => $option_value['weight_type'],
            'attribute_value_id'      => $option_value['attribute_value_id'],
            'grouped_attribute_data'  => $option_value['grouped_attribute_data'],
            'sort_order'              => $option_value['sort_order'],
            'default'                 => $option_value['default'],
        ];

        //get children (grouped options) data
        $child_option_values = unserialize($result['grouped_attribute_data']);
        if (is_array($child_option_values) && sizeof($child_option_values)) {
            $result['children_options'] = [];
            foreach ($child_option_values as $child_value) {
                $result['children_options'][$child_value['attr_id']] = (int)$child_value['attr_v_id'];
            }
        }

        return $result;
    }
}

