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

namespace ScaleUpStack\Metadata\Tests\PhpUnit;

use ScaleUpStack\Metadata\Configuration;
use ScaleUpStack\Metadata\Generator\FeatureAnalyzer;
use ScaleUpStack\Metadata\Tests\Resources\TestCase;
use ScaleUpStack\Reflection\Reflection;

/**
 * @coversDefaultClass \ScaleUpStack\Metadata\Configuration
 */
final class ConfigurationTest extends TestCase
{
    private $oldConfiguration;

    public function setUp()
    {
        $this->oldConfiguration = Reflection::getStaticPropertyValue(Configuration::class, 'configuration');
        Reflection::setStaticPropertyValue(Configuration::class, 'configuration', []);
    }

    /**
     * @test
     * @covers ::featureAnalyzers()
     */
    public function it_returns_an_empty_array_if_no_feature_analyzers_have_been_registered()
    {
        // given an unconfigured Configuration as reset in setUp()

        // when fetching the feature analyzers from the configuration
        $configuredAnalyzers = Configuration::featureAnalyzers();

        // then an empty array is returned
        $this->assertSame(
            [],
            $configuredAnalyzers
        );
    }
    /**
     * @test
     * @covers ::registerFeatureAnalyzer()
     * @covers ::featureAnalyzers()
     */
    public function it_registers_features_and_allows_to_retrieve_them()
    {
        // given a feature analyzer and a short name
        $shortName = 'mocked';
        /** @var FeatureAnalyzer $featureAnalyzer */
        $featureAnalyzer = $this->getMockForAbstractClass(FeatureAnalyzer::class);

        // when registering the feature analyzer on the short name in the Configuration
        Configuration::registerFeatureAnalyzer($shortName, $featureAnalyzer);

        // then the feature analyzer is stored in the Configuration
        $this->assertSame(
            [
                $shortName => $featureAnalyzer,
            ],
            Configuration::featureAnalyzers()
        );
    }

    public function tearDown()
    {
        Reflection::setStaticPropertyValue(Configuration::class, 'configuration', $this->oldConfiguration);
    }
}
