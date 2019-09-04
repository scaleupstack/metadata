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

namespace ScaleUpStack\Metadata\Generator;

use ScaleUpStack\Metadata\Metadata\ClassMetadata;

interface FeatureAnalyzer
{
    /**
     * NOTE: Do not manipulate the $classMetadata yourself but just return the extracted metadata.
     */
    public function extractMetadata(ClassMetadata $classMetadata) : array;
}
