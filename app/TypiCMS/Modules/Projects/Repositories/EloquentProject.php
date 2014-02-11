<?php namespace TypiCMS\Modules\Projects\Repositories;

use Config;
use App;
use Request;

use TypiCMS\Repositories\RepositoriesAbstract;
use TypiCMS\Services\Cache\CacheInterface;
use Illuminate\Database\Eloquent\Model;

class EloquentProject extends RepositoriesAbstract implements ProjectInterface {

	// Class expects an Eloquent model and a cache interface
	public function __construct(Model $model, CacheInterface $cache)
	{
		$this->model = $model;
		$this->cache = $cache;

		$this->select = array(
			'projects.id AS id',
			'slug',
			'title',
			'category_id',
			'status',
		);

	}


	/**
	 * Get all models with categories
	 *
	 * @param boolean $all Show published or all
     * @return StdClass Object with $items
	 */
	public function getAll($all = false, $relid = null)
	{
		// Build our cache item key, unique per model number,
		// limit and if we're showing all
		$allkey = ($all) ? '.all' : '';
		$key = md5(App::getLocale().'all'.$allkey);

		if ( Request::segment(1) != 'admin' and $this->cache->active('public') and $this->cache->has($key) ) {
			return $this->cache->get($key);
		}

		// Item not cached, retrieve it
		$query = $this->model->with('translations');

		if ( ! $all ) {
			// Take only translated items that are online
			$query->whereHas('translations', function($query)
				{
					$query->where('status', 1);
					$query->where('locale', '=', App::getLocale());
					$query->where('slug', '!=', '');
				}
			);
		}

		$query->with('category')->with('category.translations');

		$relid and $query->where('category_id', $relid);
		
		// Files
		$this->model->files and $query->with('files');

		$models = $query->get();

		// Sorting
		$desc = ($this->model->direction == 'desc') ? true : false ;
		$models = $models->sortBy(function($model)
		{
			return $model->{$this->model->order};
		}, null, $desc);

		// Store in cache for next request
		$this->cache->put($key, $models);

		return $models;
	}


}