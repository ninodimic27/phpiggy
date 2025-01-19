<?php

declare(strict_types=1);

namespace Framework;

use ReflectionClass, ReflectionNamedType;
use Framework\Exceptions\ContainerException;

class Container
{
    private array $definitions = [];
    private array $resolved = []; // koristi se za singleton

    public function addDefinitions(array $newDefinitions)
    {
        $this->definitions = array_merge($this->definitions, $newDefinitions); // merging arrays
    }

    public function resolve(string $className)
    {
        $reflectionClass = new ReflectionClass($className);
        if (!$reflectionClass->isInstantiable()) {
            throw new ContainerException("Class {$className} is not instantiable");
        }

        $constructor = $reflectionClass->getConstructor(); // Proverava da li postoje argumenti neophodni za definiciju klase, kao sto je TemplateEngine u slucaju recimo HomeController-a

        if (!$constructor) {
            return new $className;
        }

        $params = $constructor->getParameters(); // uzimamo parametre constructora

        if (count($params) === 0) {              // ako nema parametra instanciraj klasu - kreiraj objekat
            return new $className;
        }

        $dependencies = [];

        foreach ($params as $param) {
            $name = $param->getName();           // proveravacemo da nije neka kljucna rec parametar
            $type = $param->getType();           // proveravacemo da parametar nije string ili boolean, onda ne moze da se instancira klasa

            if (!$type) {                        // ako se ne stavi type parametra necemo moci da instanciramo klasu 
                throw new ContainerException("Failed to resolve class {$className} because param {$param} is missing a type hint!");
            }

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                throw new ContainerException("Failed to resolve class {$className} because invalid param name");
            }

            $dependencies[] = $this->get($type->getName());
        }

        return $reflectionClass->newInstanceArgs($dependencies); // instancira klasu na osnovu dependecies-a
    }

    public function get(string $id)
    {
        if (!array_key_exists($id, $this->definitions)) {
            throw new ContainerException("Class {$id} does not exist in container.");
        }

        if (array_key_exists($id, $this->resolved)) {
            return $this->resolved[$id];
        }

        $factory = $this->definitions[$id];
        $dependency = $factory();

        $this->resolved[$id] = $dependency; // classname je id

        return $dependency;
    }
}
