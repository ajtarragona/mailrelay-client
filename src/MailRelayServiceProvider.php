<?php

namespace Ajtarragona\MailRelay;

use Illuminate\Support\ServiceProvider;
//use Illuminate\Support\Facades\Blade;
//use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Ajtarragona\MailRelay\Mail\MailRelayTransport;

class MailRelayServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {



        //cargo rutas
        $this->loadRoutesFrom(__DIR__ . '/routes.php');


        //publico configuracion de la api

        $this->publishes([
            __DIR__ . '/Config/mailrelay-api.php' => config_path('mailrelay.php'),
        ], 'ajtarragona-mailrelay-config');



        Mail::extend('mailrelay', function (array $config) {
            return new MailRelayTransport($config);
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        //defino facades
        $this->app->bind('mailrelay', function () {
            return new \Ajtarragona\MailRelay\MailRelayService;
        });


        //helpers
        foreach (glob(__DIR__ . '/Helpers/*.php') as $filename) {
            require_once($filename);
        }



        if (file_exists(config_path('mailrelay.php'))) {
            $this->mergeConfigFrom(config_path('mailrelay.php'), 'mailrelay');
        } else {
            $this->mergeConfigFrom(__DIR__ . '/Config/mailrelay-api.php', 'mailrelay');
        }
    }
}
