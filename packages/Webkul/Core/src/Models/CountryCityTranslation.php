<?php

namespace Webkul\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Core\Contracts\CountryCityTranslation as CountryCityTranslationContract;

class CountryCityTranslation extends Model implements CountryCityTranslationContract
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'country_city_translations';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Fillable properties.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'locale',
    ];
}
