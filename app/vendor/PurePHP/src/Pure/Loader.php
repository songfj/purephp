<?php

class Pure_Loader {

    protected $prefixes = array();
    protected $fallbackDirs = array();
    protected $useIncludePath = false;
    protected $classMap = array();
    protected $history = array();

    /**
     *
     * @var Pure_Loader 
     */
    protected static $default;

    public function add($prefix, $paths, $prepend = false) {
        if (!$prefix) {
            if ($prepend) {
                $this->fallbackDirs = array_merge(
                        (array) $paths, $this->fallbackDirs
                );
            } else {
                $this->fallbackDirs = array_merge(
                        $this->fallbackDirs, (array) $paths
                );
            }

            return;
        }
        if (!isset($this->prefixes[$prefix])) {
            $this->prefixes[$prefix] = (array) $paths;

            return;
        }
        if ($prepend) {
            $this->prefixes[$prefix] = array_merge(
                    (array) $paths, $this->prefixes[$prefix]
            );
        } else {
            $this->prefixes[$prefix] = array_merge(
                    $this->prefixes[$prefix], (array) $paths
            );
        }
    }

    public function addClassMap(array $classMap) {
        if (!empty($this->classMap)) {
            $this->classMap = array_merge($this->classMap, $classMap);
        } else {
            $this->classMap = $classMap;
        }
    }

    public function findFile($class) {
        if ('\\' == $class[0]) {
            $class = substr($class, 1);
        }

        if (isset($this->classMap[$class])) {
            return $this->classMap[$class];
        }

        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $classPath = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 0, $pos)) . DIRECTORY_SEPARATOR;
            $className = substr($class, $pos + 1);
        } else {
            // PEAR-like class name
            $classPath = null;
            $className = $class;
        }

        $classPath .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        foreach ($this->prefixes as $prefix => $dirs) {
            if (0 === strpos($class, $prefix)) {
                foreach ($dirs as $dir) {
                    if (file_exists($dir . DIRECTORY_SEPARATOR . $classPath)) {
                        return $dir . DIRECTORY_SEPARATOR . $classPath;
                    }
                }
            }
        }

        foreach ($this->fallbackDirs as $dir) {
            if (file_exists($dir . DIRECTORY_SEPARATOR . $classPath)) {
                return $dir . DIRECTORY_SEPARATOR . $classPath;
            }
        }

        if ($this->useIncludePath && $file = stream_resolve_include_path($classPath)) {
            return $file;
        }

        return $this->classMap[$class] = false;
    }

    public function getClassMap() {
        return $this->classMap;
    }

    public function getFallbackDirs() {
        return $this->fallbackDirs;
    }

    public function getPrefixes() {
        return $this->prefixes;
    }

    public function getUseIncludePath() {
        return $this->useIncludePath;
    }

    public function loadClass($class) {
        $file = $this->findFile($class);
        if ($file !== false) {
            $this->history[] = $file;
            include $file;
            return true;
        }
        return false;
    }

    public function __invoke($class) {
        return $this->loadClass($class);
    }

    public function register($prepend = false) {
        if (function_exists('class_alias')) { // php 5.3 +
            spl_autoload_register(array($this, 'loadClass'), true, $prepend);
        } else {
            spl_autoload_register(array($this, 'loadClass'), true);
        }
    }

    public function set($prefix, $paths) {
        if (!$prefix) {
            $this->fallbackDirs = (array) $paths;

            return;
        }
        $this->prefixes[$prefix] = (array) $paths;
    }

    public function setUseIncludePath($useIncludePath) {
        $this->useIncludePath = $useIncludePath;
    }

    public function unregister() {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    public function getHistory() {
        return $this->history;
    }

    public static function getDefault() {
        if (!isset(self::$default)) {
            $loader = new self();

            $classmap = array(
                'Pure' => realpath(dirname(dirname(__FILE__))),
                'Pure_' => realpath(dirname(dirname(__FILE__))),
            );

            foreach ($classmap as $namespace => $path) {
                $loader->add($namespace, $path);
            }

            self::$default = $loader;
        }

        return self::$default;
    }

}