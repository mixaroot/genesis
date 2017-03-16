<?php

namespace lib;

/**
 * TODO константы можно вынести в конфиг
 * TODO для болиее тонкой настройки роутинга
 * TODO также методы setConsoleClass, setConsoleMethod, setConsoleParameters можно вынести в отдельные классы
 * TODO это позволит более точно настроить парсинг параметров(к примеру несколько вариантов как можно передать имя класса)
 * Class for routing console scripts
 * Class RouteConsole
 * @package lib
 */
class RouteConsole
{
    const PREFIX_NAMESPACE = '\console\\';
    const REGEXP_FOR_CLASS = '/^(.+)Console$/';
    const REGEXP_FOR_METHOD = '/^(.+)Method$/';
    const REGEXP_FOR_PARAMETERS = '/^--(.+)=(.+)$/';
    const PARENT_CLASS = "lib\Console";

    /**
     * Class for run
     * @var string
     */
    private $consoleClassForRun = '';
    /**
     * Method for run
     * @var string
     */
    private $consoleMethodForRun = '';
    /**
     * Parameters for running class
     * @var array
     */
    private $consoleParameters = [];

    /**
     * Init routing
     * @param array $argvConsole
     * @throws \Exception
     */
    public function init(array $argvConsole)
    {
        try {
            $this->startRoute($argvConsole);
        } catch (\Throwable $ex) {
            $this->workWithException($ex);
        }
    }

    /**
     * Method for work with error
     * Something like logs
     * @param \Throwable $ex
     * @throws \Exception
     */
    private function workWithException(\Throwable $ex)
    {
        throw new \Exception($ex);
    }

    /**
     * Method for control routing process
     * @param $argvConsole
     * @throws \Exception
     */
    private function startRoute($argvConsole)
    {
        // Set class, method, parameters for run
        foreach ($argvConsole as $arg) {
            $this->setConsoleClass($arg);
            $this->setConsoleMethod($arg);
            $this->setConsoleParameters($arg);
        }
        // Check is correct class, method, parameters for run
        $this->checkSetParameters();
        // Run console class
        $this->run();
    }

    /**
     * Set class for run
     * @param $arg
     * @throws \Exception
     */
    private function setConsoleClass(string $arg)
    {
        if (preg_match(static::REGEXP_FOR_CLASS, $arg, $match)) {
            if (!empty($this->consoleClassForRun)) {
                throw new \Exception('Please set only one class for run');
            }
            $this->consoleClassForRun = $match[1];
        }
    }

    /**
     * Set method for run
     * @param $arg
     * @throws \Exception
     */
    private function setConsoleMethod(string $arg)
    {
        if (preg_match(static::REGEXP_FOR_METHOD, $arg, $match)) {
            if (!empty($this->consoleMethodForRun)) {
                throw new \Exception('Please set only one method for run');
            }
            $this->consoleMethodForRun = $match[1];
        }
    }

    /**
     * Set parameters for running class
     * @param $arg
     * @throws \Exception
     */
    private function setConsoleParameters(string $arg)
    {
        if (preg_match(static::REGEXP_FOR_PARAMETERS, $arg, $match)) {
            if (isset($this->consoleParameters[$match[1]])) {
                throw new \Exception('Two parameters with the same name: ' . $match[1]);
            }
            $this->consoleParameters[$match[1]] = $match[2];
        }
    }

    /**
     * Check parameters for run console class
     * TODO Нужно также проверять на допустимость символов в названии класса, метода, но для тестового задания достаточно
     * @throws \Exception
     */
    private function checkSetParameters()
    {
        if (empty($this->consoleClassForRun)) {
            throw new \Exception('Please set run class name, something like TestConsole');
        }
        if (empty($this->consoleMethodForRun)) {
            throw new \Exception('Please set run method name, something like TestMethod');
        }
    }

    /**
     * Run console class
     * @throws \Exception
     */
    private function run()
    {
        $class = static::PREFIX_NAMESPACE . $this->consoleClassForRun;
        $method = $this->consoleMethodForRun;
        $oClass = new $class();
        $this->checkParentClass($oClass);
        $this->checkExistMethod($oClass, $method);
        $oClass->consoleParameters = $this->consoleParameters;
        $oClass->$method();
    }

    /**
     * Check parent class
     * @param $oClass
     * @throws \Exception
     */
    private function checkParentClass($oClass)
    {
        if (!in_array(static::PARENT_CLASS, class_parents($oClass))) {
            throw new \Exception('Class must extend: ' . static::PARENT_CLASS);
        }
    }

    /**
     * Check method in class
     * @param $oClass
     * @param $method
     * @throws \Exception
     */
    private function checkExistMethod($oClass, $method)
    {
        if (!method_exists($oClass, $method)) {
            throw new \Exception('Have not method: ' . $method);
        }
    }
}