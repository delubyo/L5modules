<?php namespace delubyo\L5modules;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider as ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    protected $files;

    protected $commands = [
        'delubyo\L5modules\Console\ModuleCommand'
    ];    

    public function boot()
    {
        $modules = (config("modules.list")) ?: array_map('class_basename', $this->files->directories(app_path().'/Modules/'));

            foreach($modules as $module)  {
                
                $routes = app_path().'/Modules/'.$module.'/Http/routes.php';
                $views  = app_path().'/Modules/'.$module.'/Views';
                $trans  = app_path().'/Modules/'.$module.'/Translations';

                if($this->files->exists($routes)) include $routes;
                if($this->files->isDirectory($views)) $this->loadViewsFrom($views, $module);
                if($this->files->isDirectory($trans)) $this->loadTranslationsFrom($trans, $module);

                $config = app_path().'/Modules/'.$module.'/config/config.php';
                if($this->files->exists($config))  
                {
                    //add config files
                    $this->mergeConfigFrom(
                        app_path().'/Modules/'.$module.'/config/config.php', 'mod-'.$module
                    );
                }

            }
    }

    public function register()
    {
        $this->files = new Filesystem;
        $this->commands($this->commands);
    }
}