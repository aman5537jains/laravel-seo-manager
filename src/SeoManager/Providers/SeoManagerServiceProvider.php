<?php

namespace Laravel\SeoManager\Providers;

use App\Http\Controllers\Controller;
use Artesaos\SEOTools\Facades\SEOTools;
use function config;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Schema;
use Intervention\Image\Image;
use Laravel\SeoManager\Facades;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Laravel\SeoManager\SeoManager;
use Laravel\SeoManager\Models\LaravelSeoManager;
use Laravel\SeoManager\Services\SeoService;

class SeoManagerServiceProvider extends ServiceProvider
{


    /**
     * @param Request $request
     */
    public function boot(Request $request)
    {
        if(Schema::hasTable('laravel_seo_managers') &&   config('LaravelSeoManager.date-send-type') == 'provider'){

            $this->seoService($request);
        }
        $this->loadViewsFrom(__DIR__ . '/../resource/views', 'seo-manager');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $configFile = __DIR__ . '/../../../config/LaravelSeoManager.php';

        $this->publishes([
            $configFile => config_path('LaravelSeoManager.php'),
        ]);
        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/seo-manager'),
        ], 'public');


        $this->mergeConfigFrom($configFile, 'LaravelSeoManager');
        if(config('LaravelSeoManager.date-send-type') == 'provider'){

        $viewArray = [
            'meta_title' => '',
            'meta_keywords' => '',
            'meta_description' => '',
            'seo_image' => '',
            'locale' => $request->getRequestUri(),
        ];

        view()->composer('*', function ($view) use ($request, $viewArray) {
            $seo = null;
            if(Schema::hasTable('laravel_seo_managers')){
                $seo = LaravelSeoManager::where('url', $request->getRequestUri())->first();
            }


            if ($seo != null) {
                $viewArray['meta_title'] = $seo->title;
                $viewArray['meta_keywords'] = $seo->meta_keywords;
                $viewArray['meta_description'] = $seo->meta_description;
                $viewArray['seo_image'] = $seo->image;

            }
            $view->with($viewArray);
        });
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerProvider('Artesaos\SEOTools\Providers\SEOToolsServiceProvider');
        $this->registerProvider('Intervention\Image\ImageServiceProvider');

        $this->app->singleton('seomanager', function () {
            return new SeoManager();
        });

        $this->makeAlias('SEO', SEOTools::class);
        $this->makeAlias('SeoManager', SeoManager::class);
        $this->makeAlias('Image', Image::class);

    }

    /**
     * make aliases
     * @param $class
     * @param $alias
     */
    public function makeAlias($class, $alias)
    {
        $this->app->booting(function () use ($class, $alias) {
            $loader = AliasLoader::getInstance();
            $loader->alias($class, $alias);
        });
    }

    /**
     * make provider
     * @param $provider
     */
    public function registerProvider($provider)
    {
        $this->app->register($provider);
    }

    /**
     * make seo for request page
     * @param $request
     */
    public function seoService($request)
    {
        SeoService::seoForAllPages($request->getRequestUri());
    }
}