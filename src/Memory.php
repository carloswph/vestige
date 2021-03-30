<?php

namespace Vestige;

class Memory
{
    public $memory = [];
    public $objects = [];
    public $vestige = [];
    
    public function __construct(array $objects)
    {
    	foreach($objects as $key => $class) {
    		
    		$name = get_class($class);

    		if(!$this->memory[$key]) {
    			$this->memory[$key] = $this->register($name);
    		}

    	}

    	$this->objects = $objects;

         $this->hook();
    }

    public function push(array $objects)
    {

        foreach ($objects as $key => $object) {
            $this->objects[$key] = $object;
        }

         $this->hook();
    }

    public function register(string $name)
    {


    	$reflection = new \ReflectionClass($name);
    	$methods = $reflection->getMethods();

    	$data = [];

    	foreach ($methods as $method) {

    		$data[] = $method->getName();
    	}

    	$structure[$name] = [
    		'class' => $name,
    		'methods' => $data
    	];

    	return $structure[$name];

    }

    public function record($instance, $method, $args = null)
    {
            $this->vestige[] = array(
                'instance' => $instance,
                'method' => $method,
                'parameters' => $args
            );

    }

    public function drop(string $instance)
    {
        unset($this->objects[$instance]);
    }

    public function instances()
    {
        return $this->memory;
    }

    public function hook()
    {
        foreach ($this->vestige as $item) {

            if(array_key_exists($item['instance'], $this->objects)) {

                $key = array_search($item, $this->vestige);

                $class = new \ReflectionClass($this->objects[$item['instance']]);
                $method = $class->getMethod($item['method']);
                if(is_null($item['parameters'])) {
                    $this->vestige[$key]['returns'] = $method->invoke($this->objects[$item['instance']]);
                } else {
                    $this->vestige[$key]['returns'] = $method->invoke($this->objects[$item['instance']], $item['parameters']);
                }
            }
        }
    }

    public function return(string $instance, string $method)
    {
        foreach ($this->vestige as $key => $value) {
            if($value['instance'] == $instance) {
                if($value['method'] == $method) {
                    return $value['returns'];
                }
            }
        }
    }

    public function returnAll()
    {
        return $this->vestige;
    }

}