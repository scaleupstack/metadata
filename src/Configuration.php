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

namespace ScaleUpStack\Metadata;

use ScaleUpStack\Metadata\Generator\FeatureAnalyzer;

final class Configuration
{
    private CONST KEY_FEATURE_ANALYZERS = 'featureAnalyzers';

    private static $configuration = [];

    public static function registerFeatureAnalyzer(string $name, FeatureAnalyzer $featureAnalyzer)
    {
        self::$configuration[self::KEY_FEATURE_ANALYZERS][$name] = $featureAnalyzer;
    }

    /**
     * @return FeatureAnalyzer[]
     */
    public static function featureAnalyzers() : array
    {
        return self::$configuration[self::KEY_FEATURE_ANALYZERS];
    }
}
