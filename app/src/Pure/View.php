<?php

use Illuminate\View;

/**
 * Blade templating engine wrapper for PurePHP
 */
class Pure_View extends Pure_Injectable {

    /**
     *
     * @var View\Environment
     */
    protected $environment;

    /**
     *
     * @var View\FileViewFinder 
     */
    protected $fileViewFinder;

    /**
     *
     * @var View\Engines\EngineResolver
     */
    protected $engineResolver;

    /**
     *
     * @var View\Compilers\BladeCompiler
     */
    protected $bladeCompiler;

    /**
     *
     * @var View\Engines\PhpEngine
     */
    protected $phpEngine;

    /**
     *
     * @var View\Engines\CompilerEngine
     */
    protected $bladeEngine;
    protected $viewsPath;
    protected $cachePath;

    /**
     * 
     * @param string $viewsPath Views path
     * @param string $cachePath Views cache path
     */
    public function __construct($viewsPath, $cachePath) {
        $this->viewsPath = $viewsPath;
        $this->cachePath = $cachePath;
    }

    public function setApp(\Pure_App $app) {
        parent::setApp($app);

        $this->engineResolver = new View\Engines\EngineResolver();
        $this->fileViewFinder = new View\FileViewFinder($this->app->filesystem(), array(rtrim($this->viewsPath, '/\\')), array('blade.php', 'php'));
        $this->environment = new Illuminate\View\Environment($this->engineResolver, $this->fileViewFinder, $this->app->dispatcher());
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
        $this->bladeCompiler = new View\Compilers\BladeCompiler($this->app->filesystem(), rtrim($this->cachePath, '/\\'));
        $phpEngine = new View\Engines\PhpEngine($this->bladeCompiler);
        $bladeEngine = new View\Engines\CompilerEngine($this->bladeCompiler);
        $this->engineResolver->register('php', function() use($phpEngine) {
            return $phpEngine;
        });
        $this->engineResolver->register('blade', function() use($bladeEngine) {
            return $bladeEngine;
        });
        $this->bladeEngine = $bladeEngine;
        $this->phpEngine = $phpEngine;
    }

    public function __get($key) {
        return $this->environment->shared($key, null);
    }

    public function __set($key, $value = null) {
        return $this->environment->share($key, $value);
    }

    public function get($key, $default = null) {
        return $this->environment->shared($key, $default);
    }

    public function set($key, $value = null) {
        return $this->environment->share($key, $value);
    }

    /**
     * 
     * @param string $view
     * @param array $locals
     * @return \Illuminate\View\View
     */
    public function make($view, array $locals = array()) {
        $viewFile = $this->fileViewFinder->find($view);
        $viewEngine = preg_match('/\.blade\.php$/', $viewFile) ? $this->bladeEngine : $this->phpEngine;
        $viewObj = new Illuminate\View\View(
                $this->environment, $viewEngine, $view, $viewFile, array_merge(array('viewname' => $view), $locals));
        return $viewObj;
    }

    /**
     * 
     * @param string $view
     * @param array $locals
     * @return string
     */
    public function load($view, array $locals = array()) {
        return $this->make($view, $locals)->render();
    }

    /**
     * 
     * @param string $view
     * @param array $locals
     */
    public function render($view, array $locals = array()) {
        echo $this->load($view, $locals);
    }

}
