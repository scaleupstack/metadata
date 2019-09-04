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

use ScaleUpStack\Annotations\Annotation\VarAnnotation;
use ScaleUpStack\Metadata\Generator\FeatureAnalyzer;
use ScaleUpStack\Metadata\InvalidArgumentException;
use ScaleUpStack\Metadata\Metadata\ClassMetadata;
use ScaleUpStack\Metadata\Metadata\DataTypeMetadata;
use ScaleUpStack\Metadata\Metadata\PropertyMetadata;

final class TypedProperties implements FeatureAnalyzer
{
    const FEATURES_KEY = 'typedProperties';

    public function name() : string
    {
        return self::FEATURES_KEY;
    }

    public function extractMetadata(ClassMetadata $classMetadata) : array
    {
        $typedProperties = [];

        /** @var PropertyMetadata $propertyMetadata */
        foreach ($classMetadata->propertyMetadata as $propertyMetadata) {
            /** @var VarAnnotation[] $varAnnotations */
            $varAnnotations = $propertyMetadata->annotations->annotationsByTag('var');

            if (1 < count($varAnnotations)) {
                throw new InvalidArgumentException('Only one @var annotation is allowed per property.');
            }

            if (1 !== count($varAnnotations)) {
                $docBlockType = null;
            } else {
                $docBlockType = $classMetadata->fullyQualifiedDataTypeSpecification(
                    $varAnnotations[0]->arguments()
                );
            }

            $typedProperties[$propertyMetadata->name] = new TypedPropertyMetadata(
                $propertyMetadata->name,
                new DataTypeMetadata($docBlockType),
                new DataTypeMetadata(null)
            );
        }

        return $typedProperties;
    }
}
