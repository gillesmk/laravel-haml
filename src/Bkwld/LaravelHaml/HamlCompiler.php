<?php namespace Bkwld\LaravelHaml;

// Dependencies
use Illuminate\View\Compilers\Compiler;
use Illuminate\View\Compilers\CompilerInterface;
use Illuminate\Filesystem\Filesystem;
use MtHaml\Environment;

class HamlCompiler extends Compiler implements CompilerInterface {

	/**
	 * The MtHaml instance.
	 *
	 * @var \MtHaml\Environment
	 */
	protected $mthaml;
	protected $footer;


	/**
	 * Create a new compiler instance.
	 *
	 * @param  \MtHaml\Environment $mthaml
	 * @param  \Illuminate\Filesystem\Filesystem  $files
	 * @param  string  $cachePath
	 * @return void
	 */
	public function __construct(Environment $mthaml, Filesystem $files, $cachePath)
	{
		$this->mthaml = $mthaml;
		parent::__construct($files, $cachePath);
	}

	/**
	 * Compile the view at the given path.
	 *
	 * @param  string  $path
	 * @return void
	 */
	public function compile($path) {
		
		$contents = $this->mthaml->compileString($this->files->get($path), $path);
		
		
		
		if (!is_null($this->cachePath)) {
			$contents = $this->compileStatements($contents);
			if (count($this->footer) > 0)
			{
				$contents = ltrim($contents, PHP_EOL)
						.PHP_EOL.implode(PHP_EOL, array_reverse($this->footer));
			}
			$this->files->put($this->getCompiledPath($path), $contents);
		}
	}

	protected function compileStatements($value)
	{
		
		$callback = function($match)
		{
			if (method_exists($this, $method = 'compile'.ucfirst($match[1])))
			{
				
				$match[0] = $this->$method(array_get($match, 2));
			}

			return $match[0];
		};

		return preg_replace_callback('/\<\?php view_([a-zA-Z0-9-_]+)(\(.*\)); \?\>/', $callback, $value);
	}

	/**
	 * Compile the yield statements into valid PHP.
	 *
	 * @param string  $expression
	 * @return string
	 */
	protected function compileYield($expression)
	{
		return "<?php echo \$__env->yieldContent{$expression}; ?>";
	}

	/**
	 * Compile the show statements into valid PHP.
	 *
	 * @param string  $expression
	 * @return string
	 */
	protected function compileShow($expression)
	{
		return "<?php echo \$__env->yieldSection(); ?>";
	}

	/**
	 * Compile the section statements into valid PHP.
	 *
	 * @param string  $expression
	 * @return string
	 */
	protected function compileSection($expression)
	{
		return "<?php \$__env->startSection{$expression}; ?>";
	}

	/**
	 * Compile the append statements into valid PHP.
	 *
	 * @param string  $expression
	 * @return string
	 */
	protected function compileAppend($expression)
	{
		return "<?php \$__env->appendSection(); ?>";
	}

	/**
	 * Compile the end-section statements into valid PHP.
	 *
	 * @param string  $expression
	 * @return string
	 */
	protected function compileEndsection($expression)
	{
		return "<?php \$__env->stopSection(); ?>";
	}

	/**
	 * Compile the stop statements into valid PHP.
	 *
	 * @param string  $expression
	 * @return string
	 */
	protected function compileStop($expression)
	{
		return "<?php \$__env->stopSection(); ?>";
	}

	/**
	 * Compile the overwrite statements into valid PHP.
	 *
	 * @param string  $expression
	 * @return string
	 */
	protected function compileOverwrite($expression)
	{
		return "<?php \$__env->stopSection(true); ?>";
	}

	protected function compileExtends($expression)
	{
		if (starts_with($expression, '('))
		{
			$expression = substr($expression, 1, -1);
		}

		$data = "<?php echo \$__env->make($expression, array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>";

		$this->footer[] = $data;

		return '';
	}

	/**
	 * Compile the include statements into valid PHP.
	 *
	 * @param string  $expression
	 * @return string
	 */
	protected function compileInclude($expression)
	{
		if (starts_with($expression, '('))
		{
			$expression = substr($expression, 1, -1);
		}

		return "<?php echo \$__env->make($expression, array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>";
	}

	/**
	 * Compile the stack statements into the content
	 *
	 * @param  string $expression
	 * @return string
	 */
	protected function compileStack($expression)
	{
		return "<?php echo \$__env->yieldContent{$expression}; ?>";
	}

	/**
	 * Compile the push statements into valid PHP.
	 *
	 * @param $expression
	 * @return string
	 */
	protected function compilePush($expression)
	{
		return "<?php \$__env->startSection{$expression}; ?>";
	}

	/**
	 * Compile the endpush statements into valid PHP.
	 *
	 * @param $expression
	 * @return string
	 */
	protected function compileEndpush($expression)
	{
		return "<?php \$__env->appendSection(); ?>";
	}

}