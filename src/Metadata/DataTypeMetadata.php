<?php declare(strict_types = 1);

/**
 * This file is part of ScaleUpStack/Metadata.
 *
 * For the full copyright and license information, please view the README.md and LICENSE.md files that were distributed
 * with this source code.
 *
 * @copyright 2019 - present ScaleUpVentures GmbH, https://www.scaleupventures.com
 * @link      https://github.com/scaleupstack/metadata
 */

namespace ScaleUpStack\Metadata\Metadata;

use ScaleUpStack\Metadata\Assert;
use ScaleUpStack\Metadata\UnexpectedValueException;

class DataTypeMetadata
{
    private $declaration;

    public function __construct(?string $dataTypeDeclaration)
    {
        $this->declaration = $dataTypeDeclaration;
    }

    public function declaration() : ?string
    {
        return $this->declaration;
    }

    /**
     * @param mixed $variable
     * @param object|string $objectContext
     *        For comparison with declaration '$this' or 'self'. So needs to be the accurate object instance.
     *        If it is the string of a class name, '$this' is not supported.
     */
    public function validateVariable($variable, $objectContext) : bool
    {
        if (is_null($this->declaration)) {
            return true;
        }

        $dataTypeOfVariable = $this->determineDataTypeOfVariable($variable);
        return $this->isValidVariableType($this->declaration, $variable, $dataTypeOfVariable, $objectContext);
    }

    /**
     * @param mixed $variable
     */
    private function determineDataTypeOfVariable($variable) : string
    {
        $type = gettype($variable);

        // unifiy basic types
        $mappedTypes = [
            'boolean' => 'bool',
            'integer' => 'int',
            'double' => 'float',
            'NULL' => 'null',
        ];

        if (array_key_exists($type, $mappedTypes)) {
            $type = $mappedTypes[$type];
        }

        $knownTypes = [
            'bool',
            'int',
            'float',
            'string',
            'array',
            'object',
            'null',
            // 'resource',
            // 'resource (closed)',
            // 'unknown type',
        ];

        Assert::oneOf($type, $knownTypes, 'Unhandled type %1$s of variable.');

        return $type;
    }

    /**
     * @param mixed $variable
     * @param object|string $objectContext
     */
    private function isValidVariableType(
        string $dataTypeDeclaration,
        $variable,
        string $variableDataType,
        $objectContext
    ) : bool
    {
        $allowedDataTypes = explode('|', $dataTypeDeclaration);

        foreach ($allowedDataTypes as $allowedDataType) {
            if ('[]' === substr($allowedDataType, -2)) {
                $typeOfArrayItems = substr($allowedDataType, 0, -2);

                if ('' === $typeOfArrayItems) {
                    throw new UnexpectedValueException(
                        sprintf(
                            'Illegal array declaration "%s" without type.',
                            $allowedDataType
                        )
                    );
                    break;
                }

                if (is_array($variable)) {
                    $result = true;

                    foreach ($variable as $item) {
                        $itemResult = $this->isValidVariableType(
                            $typeOfArrayItems,
                            $item,
                            $this->determineDataTypeOfVariable($item),
                            $objectContext
                        );

                        if (false === $itemResult) {
                            $result = false;
                            break;
                        }
                    }

                    if ($result) {
                        return true;
                    }
                }
            } else {
                switch ($allowedDataType) {
                    case 'bool':
                    case 'int':
                    case 'string':
                    case 'array':
                    case 'object':
                        if ($allowedDataType === $variableDataType) {
                            return true;
                        }
                        break;

                    case 'float':
                        if (
                            in_array(
                                $variableDataType,
                                ['float', 'int'],
                                true
                            )
                        ) {
                            return true;
                        }
                        break;

                    case 'true':
                        if (true === $variable) {
                            return true;
                        }
                        break;

                    case 'false':
                        if (false === $variable) {
                            return true;
                        }
                        break;

                    case 'null':
                    case 'void':
                        if ('null' === $variableDataType) {
                            return true;
                        }
                        break;

                    case 'mixed':
                        return true;
                        // inherent break

                    case '$this':
                        if (! is_object($objectContext)) {
                            throw new \RuntimeException(
                                'Object context for $this is no object. Is it some static context?'
                            );
                        }

                        if ($objectContext === $variable) {
                            return true;
                        }
                        break;

                    case 'self':
                        if (is_string($objectContext)) {
                            $objectContext = '\\' . $objectContext;
                        }
                        return ($variable instanceof $objectContext);

                    case 'callable':
                    case 'callback': // should be transformed in annotation reader
                    case 'iterable':
                    case 'number':
                    case 'numeric':
                    case 'static':
                    case 'resource':
                    case 'resource (closed)':
                        throw new UnexpectedValueException(
                            sprintf('Data type declaration "%s" not handled yet.', $allowedDataType)
                        );
                        // inherent break

                    case '':
                        throw new UnexpectedValueException(
                            'Illegal empty data type declaration "". Perhaps was "", or invalid union type like "int|"?'
                        );
                        break;


                    default:
                        // should be some object or interface declaration
                        if ($variable instanceof $allowedDataType) {
                            return true;
                        }
                }
            }
        }

        return false;
    }
}
