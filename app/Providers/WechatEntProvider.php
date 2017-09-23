<?php

namespace App\Providers;

use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Stoneworld\Wechat\User;

class WechatEntProvider extends LaravelServiceProvider
{
    /**
     * 延迟加载.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Boot the provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();
    }

    /**
     * Setup the config.
     *
     * @return void
     */
    protected function setupConfig()
    {

        if ($this->app instanceof LaravelApplication) {
            /* if ($this->app->runningInConsole()) {
                $this->publishes([
                    $source => config_path('wechat.php'),
                ]);
            } */

            // 创建模拟授权
            $this->setUpMockAuthUser();
        }

       // $this->mergeConfigFrom($source, 'wechat');
    }

    /**
     * Register the provider.
     *
     * @return void
     */
    public function register()
    {
        /* $this->app->singleton(['EasyWeChat\\Foundation\\Application' => 'wechat'], function ($app) {
            $app = new Application(config('wechat'));

            if (config('wechat.use_laravel_cache')) {
                $app->cache = new CacheBridge();
            }

            return $app;
        }); */
    }

    /**
     * 提供的服务
     *
     * @return array
     */
   /*  public function provides()
    {
        return ['wechat', 'EasyWeChat\\Foundation\\Application'];
    } */

    /**
     * 创建模拟登录.
     */
    protected function setUpMockAuthUser()
    {
        $user = config('wechat.mock_user');

        if (is_array($user) && !empty($user['openid']) && config('wechat.enable_mock')) {
            $user = new User([
                'id'       => array_get($user, 'openid'),
                'name'     => array_get($user, 'nickname'),
                'nickname' => array_get($user, 'nickname'),
                'avatar'   => array_get($user, 'headimgurl'),
                'email'    => null,
            ]);

            session(['wechat.oauth_user' => $user]);
        }
    }
}
