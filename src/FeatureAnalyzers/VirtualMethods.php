<?php declare(strict_types = 1);

/**
 * This file is part of ScaleUpStack/Metadata
 *
 * For the full copyright and license information, please view the README.md and LICENSE.md files that were distributed
 * with this source code.
 *
 * @copyright 2019 - present ScaleUpVentures GmbH, https://www.scaleupventures.com
 * @link      https://github.com/scaleupstack/metadata
 */

namespace ScaleUpStack\Metadata\FeatureAnalyzers;

use ScaleUpStack\Annotations\Annotation\MethodAnnotation;
use ScaleUpStack\Metadata\Generator\FeatureAnalyzer;
use ScaleUpStack\Metadata\InvalidArgumentException;
use ScaleUpStack\Metadata\Metadata\ClassMetadata;
use ScaleUpStack\Metadata\Metadata\DataTypeMetadata;

final class VirtualMethods implements FeatureAnalyzer
{
    public function extractMetadata(ClassMetadata $classMetadata) : array
    {
        $virtualMethods = [];

        /** @var MethodAnnotation $methodAnnotation */
        foreach ($classMetadata->annotations->annotationsByTag('method') as $methodAnnotation) {
            $parameters = [];
            foreach ($methodAnnotation->parameters() as $parameterName => $parameterData) {
                if (false !== $parameterData['hasDefaultValue']) {
                    throw new InvalidArgumentException(
                        'Currently, default values are not supported in virtual methods.'
                    );
                }

                $parameters[$parameterName] = new DataTypeMetadata(
                    $classMetadata->fullyQualifiedDataTypeSpecification($parameterData['dataType'])
                );
            }

            $returnType = new DataTypeMetadata(
                $classMetadata->fullyQualifiedDataTypeSpecification($methodAnnotation->returnType())
            );

            $virtualMethods[$methodAnnotation->methodName()] = new VirtualMethodMetadata(
                $classMetadata->name,
                $methodAnnotation->methodName(),
                $parameters,
                $returnType,
                $methodAnnotation->isStatic()
            );
        }

        return $virtualMethods;
    }
}
