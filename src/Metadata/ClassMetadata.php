<?php

/**
 * This file is part of ScaleUpStack/Metadata
 *
 * For the full copyright and license information, please view the README.md and LICENSE.md files that were distributed
 * with this source code.
 *
 * @copyright 2019 - present ScaleUpVentures GmbH, https://www.scaleupventures.com
 * @link      https://github.com/scaleupstack/metadata
 */

namespace ScaleUpStack\Metadata\Metadata;

use ScaleUpStack\Annotations\Annotation\MethodAnnotation;
use ScaleUpStack\Annotations\Annotations;
use ScaleUpStack\Metadata\InvalidArgumentException;

class ClassMetadata extends \Metadata\ClassMetadata
{
    /**
     * @var string
     */
    public $namespace;

    /**
     * @var string[]
     *      <short class names> => <fully-qualified class name>
     */
    public $useStatements = [];

    /**
     * @var Annotations
     */
    public $annotations;

    /**
     * @var VirtualMethodMetadata[]
     */
    public $virtualMethods = [];

    /**
     * @param string[] $useStatements
     */
    public function __construct(string $name, array $useStatements, Annotations $annotations)
    {
        parent::__construct($name);
        $this->setNamespace($name);
        $this->setUseStatements($useStatements);
        $this->annotations = $annotations;
        $this->setVirtualMethods($annotations);
    }

    private function setNamespace(string $className)
    {
        $parts = explode('\\', $className);
        array_pop($parts);
        $this->namespace = implode('\\', $parts);
    }

    private function setUseStatements(array $useStatements)
    {
        foreach ($useStatements as $useStatement) {
            $parts = explode(' ', $useStatement);

            if (1 === count($parts)) {
                $parts = explode('\\', $useStatement);
                $className = array_pop($parts);
                $this->useStatements[$className] = $useStatement;
            } else if (3 === count($parts)) {
                if ('as' !== $parts[1]) {
                    // TODO: throw
                }

                $this->useStatements[$parts[2]] = $parts[0];
            } else {
                // TODO: throw
            }
        }
    }

    private function setVirtualMethods(Annotations $annotations)
    {
        /** @var MethodAnnotation $methodAnnotation */
        foreach ($annotations->annotationsByTag('method') as $methodAnnotation) {
            $parameters = [];
            foreach ($methodAnnotation->parameters() as $parameterName => $parameterData) {
                if (false !== $parameterData['hasDefaultValue']) {
                    throw new InvalidArgumentException(
                        'Currently, default values are not supported in virtual methods.'
                    );
                }

                $parameters[$parameterName] = new DataTypeMetadata(
                    $this->fullyQualifiedDataTypeSpecification($parameterData['dataType'])
                );
            }

            $returnType = new DataTypeMetadata(
                $this->fullyQualifiedDataTypeSpecification($methodAnnotation->returnType())
            );

            $this->virtualMethods[$methodAnnotation->methodName()] = new VirtualMethodMetadata(
                $this->name,
                $methodAnnotation->methodName(),
                $parameters,
                $returnType
            );
        }
    }

    public function fullyQualifiedDataTypeSpecification(?string $originalSpecification) : ?string
    {
        if (is_null($originalSpecification)) {
            return null;
        }

        $specifications = explode('|', $originalSpecification);

        foreach ($specifications as $key => $specification) {
            $isTypedArray = false;
            if ('[]' === substr($specification, -2)) {
                $isTypedArray = true;
                $specification = substr($specification, 0, -2);
            }

            if ('\\' === substr($specification, 0, 1)) {
                // data type is provided as absolute namespace
                $specification = substr($specification, 1);
            } else {
                if (array_key_exists($specification, $this->useStatements)) {
                    // from use statements
                    $specification = $this->useStatements[$specification];
                } else {
                    $inNamespaceClassName = sprintf(
                        '%s\\%s',
                        $this->namespace,
                        $specification
                    );
                    if (class_exists($inNamespaceClassName)) {
                        // data type is class in current namespace
                        $specification = $inNamespaceClassName;
                    }
                }
            }

            if ($isTypedArray) {
                $specification = $specification . '[]';
            }

            $specifications[$key] = $specification;
        }

        return implode('|', $specifications);
    }

    public function serialize() : string
    {
        return serialize(
            [
                parent::serialize(),
                $this->useStatements,
                $this->annotations,
                $this->virtualMethods,
            ]
        );
    }

    /**
     * @return void
     */
    public function unserialize($str)
    {
        list(
            $parent,
            $useStatements,
            $annotations,
            $virtualMethods
        ) = unserialize($str);

        parent::unserialize($parent);

        $this->setNamespace($this->name);
        $this->useStatements = $useStatements;
        $this->annotations = $annotations;
        $this->virtualMethods = $virtualMethods;
    }
}
