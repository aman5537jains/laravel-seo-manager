<?php

namespace Laravel\SeoManager\Services;


use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Laravel\SeoManager\Contracts\SeoContact;
use Laravel\SeoManager\Models\LaravelSeoManager;
use function storage_path;


class SeoService implements SeoContact {

    /**
     * @param $keyword
     * @param $title
     * @param $description
     * @param null $image
     * @return mixed|void
     */
    public static function SeoManager($keyword, $title, $description, $image = null)
    {
        if (is_array($keyword)) {
            $keyword = implode(',', $keyword);
        }
        SEOMeta::addKeyword($keyword);
        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        OpenGraph::setDescription($description);
        OpenGraph::addProperty('type', 'website');
        OpenGraph::addProperty('locale', 'en');
        OpenGraph::setTitle($title);
        OpenGraph::addImage(storage_path($image));

    }

    /**
     * @param $uri
     * @return mixed|void
     */
    public static function seoForAllPages($uri)
    {
        $seoFildes = LaravelSeoManager::where('url', $uri)->first();
        if(!empty($seoFildes))
        self::SeoManager($seoFildes->meta_keywords, $seoFildes->title, $seoFildes->meta_description, $seoFildes->image);

    }

}