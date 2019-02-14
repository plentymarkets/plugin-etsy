<?php

namespace Etsy\Validators;

use Plenty\Validation\Validator;

/**
 * Class EtsyListingValidator
 */
class EtsyListingValidator extends Validator
{
    /**
     * Allowed enum values for who_made
     */
    const WHO_MADE = [
        'i_did', 'collective', 'someone_else'
    ];

    /**
     * Allowed enum values for when_made
     */
    const WHEN_MADE = [
      'made_to_order', '2010_2019', '2000_2009', 'before_2000', '1990s', '1980s', '1970s', '1960s', '1950s', '1940s',
        '1930s', '1920s', '1910s', '1900s', '1800s', '1700s', 'before_1700'
    ];

    /**
     * Allowed enum values for occassion
     */
    const OCCASIONS = [
        null, 'jubilum', 'taufe', 'bar_oder_bat_mizwa', 'geburtstag', 'canada_day', 'chinesisches_neujahr', 'cinco_de_mayo',
        'konfirmation', 'weihnachten', 'day_of_the_dead', 'ostern', 'eid', 'verlobung', 'vatertag', 'gute_besserung',
        'abschluss', 'halloween', 'chanukka', 'hauseinweihung', 'kwanzaa', 'prom', 'der_4_juli', 'muttertag',
        'neugeborenes', 'neujahr', 'quinceanera', 'ruhestand', 'st_patricks_day', 'sweet_16', 'anteilnahme',
        'thanksgiving', 'valentinstag', 'hochzeit'
    ];

    /**
     * Allowed enum values for recipient
     */
    const RECIPIENTS = [
        null, 'mnner', 'frauen', 'unisex_erwachsene', 'teenager__jungen', 'teenager__mdchen', 'jugendliche', 'jungs',
        'mdchen', 'kinder', 'babys__jungen', 'babys__mdchen', 'babys', 'vgel', 'katzen', 'hunde', 'haustiere',
        'not_specified'
    ];

    public function __construct()
    {
        \Validator::extend('is_bool', function ($attribute, $value, $parameters, $validator) {
            return $this->isBool($attribute, $value, $parameters, $validator);
        }, 'Has to be a bool convertible string');

        \Validator::extend('values_in_array', function ($attribute, $value, $parameters, $validator) {
            return $this->valuesInArray($attribute, $value, $parameters, $validator);
        }, "Array has missing or wrong entries");
    }

    /**
     * @return void
     */
    protected function defineAttributes()
    {
        $this->addInt('variationId')->required();
        $this->addInt('itemId')->required();
        $this->addBool('isActive')->required();
        $this->addBool('isMain')->required();
        $this->addString('who_made')->in(static::WHO_MADE)->required();
        $this->addString('when_made')->in(static::WHEN_MADE)->required();
        $this->add('is_supply')->required()->customRule('is_bool', ['required']);
        $this->add('categories')->required();//todo
        $this->add('shipping_profiles')->required();//todo
        $this->add('images')->required();//todo
        $this->add('salesPrices')->required();//todo
        $this->add('texts')->required();//todo

        $this->add('materials');//todo
        $this->add('occasion')->in(static::OCCASIONS);
        $this->add('recipient')->in(static::RECIPIENTS);
        $this->add('attributes');//todo
        $this->add('shop_section_id');//todo
        $this->add('is_customizable')->customRule('is_bool', []);
        $this->add('non_taxable')->customRule('is_bool', []);
        $this->add('processing_min');//todo
        $this->add('processing_max');//todo
        $this->add('style');//todo
    }

    /**
     * Validates fields that are provided as a string and need to be converted into a boolean
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return bool
     */
    protected function isBool($attribute, $value, $parameters, $validator) {
        $allowed = [
            '0', '1', 'true', 'false', 'y', 'n'
        ];

        //if the field is not required we can accept null
        if (!in_array('required', $parameters) && $value === null) {
            return true;
        }

        if (gettype($value) != 'string') {
            return false;
        }

        if (in_array(strtolower($value), $allowed)) {
            return true;
        }

        return false;
    }

    protected function valuesInArray($attribute, $value, $parameters, $validator) {

    }
}
