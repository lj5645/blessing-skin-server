<?php

namespace App\Http\View\Composers;

use App\Services\Webpack;
use Blessing\Filter;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HeadComposer
{
    /** @var Webpack */
    protected $webpack;

    /** @var Dispatcher */
    protected $dispatcher;

    /** @var Request */
    protected $request;

    /** @var Filter */
    protected $filter;

    public function __construct(
        Webpack $webpack,
        Dispatcher $dispatcher,
        Request $request,
        Filter $filter
    ) {
        $this->webpack = $webpack;
        $this->dispatcher = $dispatcher;
        $this->request = $request;
        $this->filter = $filter;
    }

    public function compose(View $view)
    {
        $this->addFavicon($view);
        $this->applyThemeColor($view);
        $this->seo($view);
        $this->injectStyles($view);
        $this->addExtra($view);
        $this->serializeGlobals($view);
    }

    public function addFavicon(View $view)
    {
        $url = option('favicon_url', config('options.favicon_url'));
        $url = Str::startsWith($url, 'http') ? $url : url($url);
        $view->with('favicon', $url);
    }

    public function applyThemeColor(View $view)
    {
        $colors = [
            'primary' => '#007bff',
            'secondary' => '#6c757d',
            'success' => '#28a745',
            'warning' => '#ffc107',
            'danger' => '#dc3545',
            'navy' => '#001f3f',
            'olive' => '#3d9970',
            'lime' => '#01ff70',
            'fuchsia' => '#f012be',
            'maroon' => '#d81b60',
            'indigo' => '#6610f2',
            'purple' => '#6f42c1',
            'pink' => '#e83e8c',
            'orange' => '#fd7e14',
            'teal' => '#20c997',
            'cyan' => '#17a2b8',
            'gray' => '#6c757d',
        ];
        $view->with('theme_color', Arr::get($colors, option('navbar_color')));
    }

    public function seo(View $view)
    {
        $view->with('seo', [
            'keywords' => option('meta_keywords'),
            'description' => option('meta_description'),
            'extra' => option('meta_extras'),
        ]);
    }

    public function injectStyles(View $view)
    {
        $links = [];
        $links[] = [
            'rel' => 'stylesheet',
            'href' => 'https://cdn.jsdelivr.net/npm/@blessing-skin/admin-lte@3.0.5/dist/admin-lte.min.css',
            'integrity' => 'sha256-zG+BobiRcnWbKPGtQ++LoogyH9qSHjhBwhbFFP8HbDM=',
            'crossorigin' => 'anonymous',
        ];
        $links[] = [
            'rel' => 'preload',
            'as' => 'font',
            'href' => 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.13.0/webfonts/fa-solid-900.woff2',
            'crossorigin' => 'anonymous',
        ];
        $links[] = [
            'rel' => 'preload',
            'as' => 'font',
            'href' => 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.13.0/webfonts/fa-regular-400.woff2',
            'crossorigin' => 'anonymous',
        ];
        $links[] = [
            'rel' => 'stylesheet',
            'href' => 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.13.0/css/all.min.css',
            'integrity' => 'sha256-h20CPZ0QyXlBuAw7A+KluUYx/3pK+c7lYEpqLTlxjYQ=',
            'crossorigin' => 'anonymous',
        ];
        if (!$this->request->is('/') && config('app.asset.env') !== 'development') {
            $links[] = [
                'rel' => 'stylesheet',
                'href' => $this->webpack->url('style.css'),
                'crossorigin' => 'anonymous',
            ];
        }
        $links = $this->filter->apply('head_links', $links);
        $view->with('links', $links);
        $view->with('inline_css', option('custom_css'));
        $view->with('custom_cdn_host', option('cdn_address'));
    }

    public function addExtra(View $view)
    {
        $content = [];
        $this->dispatcher->dispatch(new \App\Events\RenderingHeader($content));
        $view->with('extra_head', $content);
    }

    public function serializeGlobals(View $view)
    {
        $blessing = [
            'version' => config('app.version'),
            'locale' => config('app.locale'),
            'base_url' => url('/'),
            'site_name' => option_localized('site_name'),
            'route' => request()->path(),
        ];
        $view->with('blessing', $blessing);
    }
}
