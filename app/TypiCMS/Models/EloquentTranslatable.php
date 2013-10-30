<?php namespace TypiCMS\Models;

use Illuminate\Support\Facades\Config;

abstract class EloquentTranslatable extends Base {


    /**
     * Translations by $locale
     *
     * @param  string $locale
	 * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations($locale = '')
    {
		if ( !$locale ) $locale = Config::get('app.contentlocale');
		
        return $this->_translations()->where(static::$translatable['localeField'], '=', $locale);
    }

    /**
     * Translations
	 * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
	public function _translations()
	{
		return $this->hasMany(static::$translatable['translationModel'], static::$translatable['relationshipField']);
	}
	
    /**
     * Magic get
	 * 
	 * @param  string  $key
	 *
     * @return mixed
     */
	public function __get($key)
	{
		$locales = Config::get('app.locales');
		
		if ( in_array($key, $locales) ) 
		{
			return $this->translations($key)->first();
		}
		
		if ( in_array($key, static::$translatable['translatables']) )
		{
			return $this->translations->first()->{$key};
		}
		
		return parent::__get($key);
	}

    /**
     * Delete translations
	 * 
     * @return boolean|null
     */	
	public function delete()
	{
		$this->_translations()->delete();
		return parent::delete();
	}


	// joindre toutes les traductions sans where lang
	public function scopeJoinTranslations($query)
	{
		return $query->with('translations')->join(
			$this->table.'_translations',
			$this->table.'.id', '=', static::$translatable['relationshipField']
		);
	}


	/**
	 * Add translatable fields to model, so Former can populate
	 */
	public function setTranslatedFields()
	{
		$locales = Config::get('app.locales');
		foreach ($locales as $lang) {
			$translation = $this->$lang;
			$fields = array();
			foreach (static::$translatable['translatables'] as $field) {
				if (isset($translation->$field)) {
					$fields[$field] = $translation->$field;
				} else {
					$fields[$field] = '';
				}
			}
			$this->$lang = $fields;
		}
	}

}