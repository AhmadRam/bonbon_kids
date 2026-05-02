<?php

namespace Webkul\Core\Models;

use Webkul\Core\Eloquent\TranslatableModel;
use Webkul\Core\Contracts\CountryCity as CountryCityContract;

class CountryCity extends TranslatableModel implements CountryCityContract
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'country_state_cities';

    public $translatedAttributes = [
        'name',
    ];

    protected $fillable = [
        'country_id',
        'country_code',
        'country_state_id',
        'state_code',
        'code',
        'default_name',
        'status',
    ];

    public $timestamps = false;
    
    /**
     * Get the country that owns the city.
     */
    public function country()
    {
        return $this->belongsTo(CountryProxy::modelClass());
    }

    /**
     * Get the state that owns the city.
     */
    public function state()
    {
        return $this->belongsTo(CountryStateProxy::modelClass(), 'country_state_id');
    }
}
