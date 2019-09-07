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

use ScaleUpStack\Annotations\Annotations;

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
     * @var array
     */
    public $features = [];

    /**
     * @param string[] $useStatements
     */
    public function __construct(string $name, array $useStatements, Annotations $annotations)
    {
        parent::__construct($name);
        $this->setNamespace($name);
        $this->setUseStatements($useStatements);
        $this->annotations = $annotations;
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
                    $this->throwOnInvalidUseStatement($useStatement);
                }

                $this->useStatements[$parts[2]] = $parts[0];
            } else {
                $this->throwOnInvalidUseStatement($useStatement);
            }
        }
    }

    private function throwOnInvalidUseStatement(string $useStatement)
    {
        throw new \RuntimeException(
            sprintf(
                "Invalid use statement '%s' in class %s",
                $useStatement,
                $this->name
            )
        );
    }

    /**
     * NOTE: Does not resolve "$this" or "self" to a fully qualified class name, but keeps them.
     */
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

    /**
     * Returns if the data type specification corresponds to an object.
     *
     * NOTE: Union types and interfaces are not supported.
     */
    public function isDataTypeSpecificationAnObject(?string $specification) : bool
    {
        $fullyQualified = $this->fullyQualifiedDataTypeSpecification($specification);

        if ('[]' === substr($fullyQualified, -2)) {
            $fullyQualified = substr($fullyQualified, 0, -2);
        }

        if (
            'self' === $fullyQualified ||
            '$this' === $fullyQualified
        ) {
            return true;
        }

        return class_exists($fullyQualified);
    }

    /**
     * NOTES:
     *
     * - Throws an exception if the specification is not an object. (Check using self::isDataTypeSpecificationAnObject()
     *   before).
     *
     * - Union types and interfaces are not supported.
     */
    public function fullyQualifiedClassNameOfDataTypeSpecification(string $specification) : string
    {
        if (! $this->isDataTypeSpecificationAnObject($specification))
        {
            throw new \RuntimeException(
                sprintf(
                    "Data type specification '%s' is not an object. (Interfaces and union types are not supported.)",
                    $specification
                )
            );
        }

        if (
            'self' === $specification ||
            '$this' === $specification
        ) {
            return $this->name;
        }

        return $this->fullyQualifiedDataTypeSpecification($specification);
    }

    public function serialize() : string
    {
        return serialize(
            [
                parent::serialize(),
                $this->useStatements,
                $this->annotations,
                $this->features,
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
            $features,
        ) = unserialize($str);

        parent::unserialize($parent);

        $this->setNamespace($this->name);
        $this->useStatements = $useStatements;
        $this->annotations = $annotations;
        $this->features = $features;
    }
}
