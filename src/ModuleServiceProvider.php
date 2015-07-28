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
        if (!$this->files->exists(app_path().'/Modules/')) mkdir(app_path().'/Modules/');

        $modules = (config("modules.list")) ?: array_map('class_basename', $this->files->directories(app_path().'/Modules/'));

            foreach($modules as $module)  {
                
                $routes = app_path().'/Modules/'.$module.'/Http/routes.php';
                $views  = app_path().'/Modules/'.$module.'/Views';
                $trans  = app_path().'/Modules/'.$module.'/Translations';

                if($this->files->exists($routes)) include $routes;
                if($this->files->isDirectory($views)) $this->loadViewsFrom($views, $module);
                if($this->files->isDirectory($trans)) $this->loadTranslationsFrom($trans, $module);

                //load multiple config files inside config folder
                $config_files = array_map('class_basename', $this->files->files(app_path().'/Modules/'.$module.'/config/'));
                foreach($config_files as $config_file)  {
                    $fname = basename($config_file, ".php");
                    $this->mergeConfigFrom(
                        app_path().'/Modules/'.$module.'/config/'.$config_file, 'mod-'.$fname
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