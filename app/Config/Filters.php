<?php
namespace Config;
use CodeIgniter\Config\Filters as BaseFilters;
class Filters extends BaseFilters
{
    public array $aliases = [
        'csrf'=>\CodeIgniter\Filters\CSRF::class,
        'secureheaders'=>\CodeIgniter\Filters\SecureHeaders::class,
        'auth'=>\App\Filters\AuthFilter::class,
    ];
    public array $required = ['before'=>[], 'after'=>[]];
    public array $globals = ['before'=>['csrf'], 'after'=>['secureheaders']];
    public array $methods = [];
    public array $filters = [];
}
