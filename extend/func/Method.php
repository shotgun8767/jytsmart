<?php

namespace func;

use Exception;
use BadMethodCallException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use func\exception\InstanceException;

/**
 * Class Method
 * @package func
 * @since 2019/9/24
 * @author Shotgun8767
 */
class Method
{
    /**
     * class name
     * @var string
     */
    protected $class;

    /**
     * method name
     * @var string
     */
    protected $method;

    /**
     * is method static
     * @var bool
     */
    protected $isStatic = false;

    /**
     * @var ReflectionMethod
     */
    protected $reflect;

    /**
     * class instance object
     * @var object|null
     */
    protected $instance;

    /**
     * Method constructor.
     * @param string $class
     * @param string $method
     * @throws BadMethodCallException
     */
    public function __construct(string $class, string $method)
    {
        if (class_exists($class)) {
            $this->class = $class;
        } else {
            throw new BadMethodCallException('Class not exists: ' . $class);
        }

        try {
            $ref = new ReflectionClass($class);
            if ($ref->hasMethod($method)) {
                $this->method = $method;
                $this->reflect = $ref->getMethod($method);
                $this->isStatic = $this->reflect->isStatic();
            } else {
                throw new BadMethodCallException('Method not exists: ' . $method);
            }
        } catch (Exception $e) {
            throw new $e;
        }

    }

    /**
     * get parameters
     * @return array
     * @throws BadMethodCallException
     */
    public function getParameters() : array
    {
        return $this->reflect->getParameters();
    }

    /**
     * @param array|null $args
     * @return $this
     */
    public function instantiateClass(?array $args = []) : self
    {
        try {
            $refClass = new ReflectionClass($this->class);
            $constructor = $refClass->getConstructor();

            $args = $constructor ? $this->bindParam($constructor, $args): [];
            $this->instance = $refClass->newInstanceArgs($args);
        } catch (Exception $e) {
            throw new InstanceException('fail to instantiate class!');
        }

        return $this;
    }

    /**
     * execute method
     * @param array|null $args
     * @return mixed
     * @throws BadMethodCallException
     */
    public function exec(?array $args = [])
    {
        $param_arr = $this->bindParam($this->reflect, $args);

        if (!$this->isStatic && is_null($this->instance)) {
            $this->instantiateClass();
        }

        return call_user_func_array([$this->isStatic ? $this->class : $this->instance, $this->method], $param_arr);
    }

    /**
     * bind parameters
     * @access protected
     * @param ReflectionMethod $reflect
     * @param array $args arguments
     * @return array
     */
    protected function bindParam(ReflectionMethod $reflect, ?array $args) : array
    {
        if ($reflect->getNumberOfParameters() == 0) {
            return [];
        }

        reset($args);
        $assoc  = key($args) !== 0;
        $params = $reflect->getParameters();
        $_args  = [];

        foreach ($params as $param) {
            $name = $param->getName();
            if (!$assoc && !empty($args)) {
                $_args[] = array_shift($args);
            } elseif ($assoc && isset($args[$name])) {
                $_args[] = $args[$name];
            } elseif ($param->isDefaultValueAvailable()) {
                $_args[] = $param->getDefaultValue();
            } else {
                throw new InvalidArgumentException('method param miss:' . $name);
            }
        }

        return $_args;
    }
}