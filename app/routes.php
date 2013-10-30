<?php
/**
 * Login / logout.
 */
Route::get('users/login', array('as' => 'users.login', 'uses' => 'App\Controllers\Admin\UsersController@getLogin'));
Route::post('users/login', 'App\Controllers\Admin\UsersController@postLogin');

Route::get('users/logout', array('as' => 'users.logout', 'uses' => 'App\Controllers\Admin\UsersController@getLogout'));

Route::get('users/register', array('as' => 'users.register', 'uses' => 'App\Controllers\Admin\UsersController@getRegister'));
Route::post('users/register', 'App\Controllers\Admin\UsersController@postRegister');

Route::get('users/activate', array('as' => 'users.activate', 'uses' => 'App\Controllers\Admin\UsersController@getActivate'));

Route::get('users/resetpassword', array('as' => 'users.resetpassword', 'uses' => 'App\Controllers\Admin\UsersController@getResetpassword'));
Route::post('users/resetpassword', 'App\Controllers\Admin\UsersController@postResetpassword');

Route::get('users/changepassword/{userid}/{resetcode}', array('as' => 'users.changepassword', 'uses' => 'App\Controllers\Admin\UsersController@getChangepassword'));
Route::post('users/changepassword/{userid}/{resetcode}', 'App\Controllers\Admin\UsersController@postChangepassword');

/**
 * Model binding.
 */
Route::model('pages', 'TypiCMS\Models\Page');
Route::model('menus', 'TypiCMS\Models\Menu');
Route::model('menulinks', 'TypiCMS\Models\Menulink');
Route::model('events', 'TypiCMS\Models\Event');
Route::model('projects', 'TypiCMS\Models\Project');
Route::model('categories', 'TypiCMS\Models\Category');
Route::model('files', 'TypiCMS\Models\File');

if (Request::segment(1) != 'admin') {
	Route::bind('categories', function($value, $route){
		return TypiCMS\Models\Category::select('categories.id AS id', 'slug', 'status')->where('slug', $value)->where('status', 1)->joinTranslations()->firstOrFail();
	});
}

/**
 * Admin side.
 */
Route::group(array('prefix' => 'admin', 'before' => 'auth.admin|cache.clear'), function()
{
	Route::get('/', array('as' => 'dashboard', 'uses' => 'App\Controllers\Admin\DashboardController@index'));
	Route::get('backup', array('as' => 'backup', 'uses' => 'App\Controllers\Admin\DashboardController@backup'));

	Route::resource('configuration', 'App\Controllers\Admin\ConfigurationController');

	Route::resource('pages', 'App\Controllers\Admin\PagesController');
	Route::post('pages/sort', array('as' => 'admin.pages.sort', 'uses' => 'App\Controllers\Admin\PagesController@sort'));

	Route::resource('events', 'App\Controllers\Admin\EventsController');
	Route::resource('categories', 'App\Controllers\Admin\CategoriesController');
	Route::resource('categories.projects', 'App\Controllers\Admin\ProjectsController');
	Route::resource('news', 'App\Controllers\Admin\NewsController');
	Route::resource('addresses', 'App\Controllers\Admin\AddressesController');
	Route::resource('partners', 'App\Controllers\Admin\PartnersController');
	
	Route::resource('users', 'App\Controllers\Admin\UsersController');

	Route::resource('files', 'App\Controllers\Admin\FilesController');
	Route::post('files/upload', array('as' => 'admin.files.upload', 'uses' => 'App\Controllers\Admin\FilesController@upload'));

	Route::resource('menus', 'App\Controllers\Admin\MenusController');
	Route::resource('menus.menulinks', 'App\Controllers\Admin\MenuLinksController');
	Route::post('menus/{menus}/menulinks/sort', array('as' => 'admin.menus.menulinks.sort', 'uses' => 'App\Controllers\Admin\MenuLinksController@sort'));

});

/**
 * Public side.
 */

Route::group(array('before' => 'cache', 'after' => 'cache'), function()
{

	// Lang chooser
	Route::get('/', array('as' => 'root', 'uses' => 'App\Controllers\PublicController@root'));

	if ( ! App::runningInConsole()) {
		
		// Build routes from Table Pages
		$pages = DB::table('pages')
			->select('pages.id', 'page_id', 'uri', 'lang')
			->join('pages_translations', 'pages.id', '=', 'pages_translations.page_id')
			->where('uri', '!=', '')
			->where('is_home', '!=', 1)
			->where('status', '=', 1)
			->orderBy('lang')
			->get();

		foreach ($pages as $page) {
			Route::get($page->uri, array('as' => $page->lang.'.pages.'.$page->id, 'uses' => 'App\Controllers\PagesController@uri'));
		}

		// Build routes from menulinks (modules)
		$menulinks = DB::table('menulinks')
			->select('menulinks.id', 'menulink_id', 'uri', 'lang', 'module_name')
			->join('menulinks_translations', 'menulinks.id', '=', 'menulinks_translations.menulink_id')
			->where('uri', '!=', '')
			->where('module_name', '!=', '')
			->where('status', '=', 1)
			->orderBy('module_name')
			->get();

		$menulinksArray = array();
		foreach ($menulinks as $menulink) {
			$menulinksArray[$menulink->module_name][$menulink->lang] = $menulink->uri;
		}

		// events routes
		if (isset($menulinksArray['events'])) {
			foreach ($menulinksArray['events'] as $lang => $uri) {
				Route::get($uri, array('as' => $lang.'.events', 'uses' => 'App\Controllers\EventsController@index'));
				Route::get($uri.'/{slug}', array('as' => $lang.'.events'.'.slug', 'uses' => 'App\Controllers\EventsController@show'));
			}
		}

		// projects routes
		if (isset($menulinksArray['projects'])) {
			foreach ($menulinksArray['projects'] as $lang => $uri) {
				Route::get($uri, array('as' => $lang.'.projects', 'uses' => 'App\Controllers\ProjectsController@index'));
				Route::get($uri.'/{categories}', array('as' => $lang.'.projects.categories', 'uses' => 'App\Controllers\ProjectsController@index'));
				Route::get($uri.'/{categories}/{slug}', array('as' => $lang.'.projects.categories.slug', 'uses' => 'App\Controllers\ProjectsController@show'));
			}
		}

	}

	// Homepages (for each language)
	foreach (Config::get('app.locales') as $locale) {
		Route::get($locale, array('as' => $locale, 'uses' => 'App\Controllers\PagesController@homepage'));
	}

});