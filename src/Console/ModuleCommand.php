<?php namespace delubyo\L5modules\Console;

use Illuminate\Console\GeneratorCommand as GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ModuleCommand extends GeneratorCommand {


	protected $name = 'make:module';

	protected $description = 'Create a new module (folder structure)';

	protected $type = 'Module';

	protected $currentStub;


	public function fire()
	{
		// check if module exists
		if($this->files->exists(app_path().'/Modules/'.$this->getNameInput())) 
			return $this->error($this->type.' already exists!');
		// Create Controller
		//$this->generate('controller');
		// Create Model
		$this->generate('model');
		// Create Views folder
		$this->generate('view');
		
		// Create Config folder
		$this->generate('config');

		// Create Database folder
		$this->generate('database');

		// Create Http folder
		$this->generate('http');		

		//Flag for no translation
		if ( ! $this->option('no-translation')) // Create Translations folder
			$this->generate('translation');
		// Create Routes file
		$this->generate('routes');
		if ( ! $this->option('no-migration'))
		{
			//$table = str_plural(snake_case(class_basename($this->argument('name'))));
			$table = snake_case(class_basename($this->argument('name')));
			$this->call('make:migration', [
				'name' => "create_{$table}_table", 
				'--create' => $table, 
				'--path' => '/app/Modules/'.$this->getNameInput() . '/Database/Migrations'
				]);
		}
		$this->info($this->type.' created successfully.');
	}


	protected function generate($type) {

		if ($type=='database') {
			$path = app_path().'/Modules/'.$this->getNameInput() . '/Database';
			mkdir($path);
			
			mkdir($path . '/Migrations');
			$this->files->put($path . '/Migrations/.gitkeep', "");

			mkdir($path . '/Seeds');
			$this->files->put($path . '/Seeds/.gitkeep', "");
			return;
		}

		if ($type=='http') {
			$path = app_path().'/Modules/'.$this->getNameInput() . '/Http';
			mkdir($path);
			mkdir($path . '/Controllers');
			$this->files->put($path . '/Controllers/.gitkeep', "");
			mkdir($path . '/Middleware');
			$this->files->put($path . '/Middleware/.gitkeep', "");
			mkdir($path . '/Requests');
			$this->files->put($path . '/Requests/.gitkeep', "");

			$this->currentStub = __DIR__.'/stubs/controller.stub';

			$filename = studly_case(class_basename($this->getNameInput()).'Controller');
			$name = $this->parseName('Modules/'.$this->getNameInput().'/Http/Controllers/'.$filename);
			$path = $this->getPath($name);

			$this->files->put($path, $this->buildClass($name));
			return;
		}		


		switch ($type) {
			case 'controller':
				$filename = studly_case(class_basename($this->getNameInput()).ucfirst($type));
				break;
			case 'model':
				$filename = studly_case(class_basename($this->getNameInput()));
				break;
			case 'view':
				$filename = 'index.blade';
				break;
				
			case 'translation':
				$filename = 'example';
				break;

			case 'config':
				$filename = 'config';
				break;							
			
			case 'routes':
				$filename = 'routes';
				break;
		}

		$folder = ($type != 'routes') ? ucfirst($type).'s\\'. ($type === 'translation' ? 'en\\':'') : 'Http\\';

		if ($type=='config') $folder = 'Config\\';		

		$name = $this->parseName('Modules\\'.$this->getNameInput().'\\'.$folder.$filename);

		if ($this->files->exists($path = $this->getPath($name))) 
			return $this->error($this->type.' already exists!');
		
		$this->currentStub = __DIR__.'/stubs/'.$type.'.stub';
		
		$this->makeDirectory($path);
		$this->files->put($path, $this->buildClass($name));
	}	


	protected function getNamespace($name)
	{
		return trim(implode('\\', array_map('ucfirst', array_slice(explode('\\', $name), 0, -1))), '\\');
	}

	protected function buildClass($name)
	{
		$stub = $this->files->get($this->getStub());
		return $this->replaceName($stub, $this->getNameInput())->replaceNamespace($stub, $name)->replaceClass($stub, $name);
	}

	protected function replaceName(&$stub, $name)
	{
		$stub = str_replace('SampleTitle', $name, $stub);
		$stub = str_replace('SampleUCtitle', ucfirst($name), $stub);
		return $this;
	}

	protected function replaceClass($stub, $name)
	{
		$class = str_ireplace($this->getNamespace($name).'\\', '', $name);
		return str_replace('SampleClass', $class, $stub);
	}		

	protected function getStub()
	{
		return $this->currentStub;
	}

	protected function getArguments()
	{
		return array(
			['name', InputArgument::REQUIRED, 'Module name.'],
		);
	}	

	protected function getOptions()
	{
		return array(
			['no-migration', null, InputOption::VALUE_NONE, 'Do not create new migration files.'],
			['no-translation', null, InputOption::VALUE_NONE, 'Do not create module translation filesystem.'],
		);
	}	

}