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

namespace ScaleUpStack\Metadata\Tests\PhpUnit\Metadata;

use ScaleUpStack\Metadata\Metadata\DataTypeMetadata;
use ScaleUpStack\Metadata\Tests\Resources\TestCase;
use ScaleUpStack\Metadata\UnexpectedValueException;

/**
 * @coversDefaultClass \ScaleUpStack\Metadata\Metadata\DataTypeMetadata
 */
class DataTypeMetadataTest extends TestCase
{
    /**
     * @test
     * @covers ::__construct()
     * @covers ::declaration()
     */
    public function it_holds_datatype_information()
    {
        // given a data type declaration
        $declaration = 'int';

        // when creating a DataTypeMetadata object
        $metadata = new DataTypeMetadata($declaration);

        // then the metadata is available
        $this->assertSame($declaration, $metadata->declaration());
    }

    /**
     * @test
     */
    public function it_allows_null_for_data_type_declaration()
    {
        // given the data type declaration is NULL
        $declaration = null;

        // when creating a DataTypeMetadata object
        $metadata = new DataTypeMetadata($declaration);

        // then the held metadata is null
        $this->assertNull($metadata->declaration());
    }

    public function data_provider_with_valid_values_for_types()
    {
        return [
            // primitive datatypes
            ['bool',                                 true,],
            ['int',                                  42],
            ['float',                                4.2],
            ['float',                                4], // allow ints for float
            ['string',                               'some string'],
            ['array',                                [1, 2]],
            ['object',                               new \stdClass()],
            ['object',                               new \DateTime()],
            ['null',                                 null],
            ['true',                                 true],
            ['false',                                false],
            ['void',                                 null],

            // no declaration given or mixed
            [null,                                  'any value allowed'],
            ['mixed',                               'any value allowed'],

            // union types
            ['string|int',                          42],
            ['string|int',                          'some string'],

            // class
            [\DateTimeInterface::class,             new \DateTimeImmutable()],

            // array of type
            ['int[]',                               [1, 2]],
            ['stdClass[]',                          [new \stdClass(), new \stdClass()]],
            [\DateTimeInterface::class . '[]',      [new \DateTimeImmutable(), new \DateTime()]],

            // union type with array of type
            ['int[]|string[]',                       [1, 2]],
            ['int[]|string[]',                       ['value1', 'value2']],

            ['int[]|string',                         [1, 2]],
            ['int[]|string',                         'string value'],
        ];
    }

    /**
     * @test
     * @dataProvider data_provider_with_valid_values_for_types
     * @covers ::validateVariable()
     * @covers ::determineDataTypeOfVariable()
     * @covers ::isValidVariableType()
     */
    public function it_validates_valid_values($dataTypeDeclaration, $validValue)
    {
        // given a DataTypeMetadata of the data type declaration as provided by data provider
        $dataType = new DataTypeMetadata($dataTypeDeclaration);

        // when checking a valid value as provided by the data provider
        $result = $dataType->validateVariable($validValue, $this);

        // then the result is true
        $this->assertTrue($result);
    }

    /**
     * @test
     * @covers ::isValidVariableType()
     */
    public function it_validates_valid_value_for_this()
    {
        // given a DataTypeMetadata of type $this
        $dataType = new DataTypeMetadata('$this');

        // when checking with $this
        $variable = $objectContext = $this;
        $result = $dataType->validateVariable($variable, $objectContext);

        // then the result is true
        $this->assertTrue($result);
    }

    public function provides_variables_and_if_they_are_valid_self_according_to_corresponding_object_context() : array
    {
        return [
            [new \Exception(), new \Exception(), true],
            [new \RuntimeException(), new \Exception(), true],
            [new \Exception(), \Exception::class, true],
            [new \RuntimeException(), \Exception::class, true],
            [new \Exception(), \Throwable::class, true],

            [new \Exception(), new \RuntimeException(), false],
            [new \Exception(), \RuntimeException::class, false],
            [new \Exception(), new \DateTime(), false],
        ];
    }

    /**
     * @test
     * @dataProvider provides_variables_and_if_they_are_valid_self_according_to_corresponding_object_context
     * @covers ::isValidVariableType()
     */
    public function it_validates_variables_as_self($variable, $objectContext, $expectedValidationResult)
    {
        // given a DataTypeMetadata of type self, and a variable and object context as provided by the test parameters
        $dataType = new DataTypeMetadata('self');

        // when checking with the variable against the object context
        $result = $dataType->validateVariable($variable, $objectContext);

        // then the result is as expected (as provided by the test parameter)
        $this->assertSame($expectedValidationResult, $result);
    }

    public function data_provider_with_invalid_values_for_types()
    {
        return [
            // primitive datatypes
            ['bool',                        'true'],
            ['int',                         '42'],
            ['float',                       '4.2'],
            ['string',                      null],
            ['array',                       4],
            ['object',                      false],
            ['null',                        'null'],
            ['true',                        false],
            ['true',                        'lazy PHP validates to true'],
            ['false',                       0],
            ['void',                        true],

            // union types
            ['string|int',                  0.07],
            ['string|int',                  null],

            // class
            [\DateTimeInterface::class,     new \stdClass()],
            [\DateTimeImmutable::class,     new \DateTime()],

            // array of type
            ['string[]',                    'no array'],
            ['string[]',                    [1, 2]],
            ['int[]',                       ['array', 'of', 'strings']],
            ['int[]',                       [0, 1, 'no int']],

            // union type with non-matching type declaration
            ['string[]|int',                'some string'],
        ];
    }

    /**
     * @test
     * @dataProvider data_provider_with_invalid_values_for_types
     * @covers ::isValidVariableType()
     */
    public function it_invalidates_invalid_values($dataTypeDeclaration, $invalidValue)
    {
        // given a DataTypeMetadata of the data type declaration as provided by data provider
        $dataType = new DataTypeMetadata($dataTypeDeclaration);

        // when checking an invalid value as provided by the data provider
        $result = $dataType->validateVariable($invalidValue, $this);

        // then the result is false
        $this->assertFalse($result);
    }

    /**
     * @test
     * @covers ::isValidVariableType()
     */
    public function it_invalidates_invalid_values_for_this()
    {
        // given a DataTypeMetadata of type $this
        $dataType = new DataTypeMetadata('$this');

        // when checking with $this
        $variable = new \stdClass();
        $objectContext = $this;
        $result = $dataType->validateVariable($variable, $objectContext);

        // then the result is false
        $this->assertFalse($result);
    }

    /**
     * @test
     * @covers ::isValidVariableType()
     */
    public function it_throws_an_exception_when_validating_a_static_object_context_for_this()
    {
        // given a DataTypeMetadata of type $this
        $dataType = new DataTypeMetadata('$this');

        // when checking with $this with a static object context (class name)
        // then an exception is thrown
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Object context for $this is no object. Is it some static context?');

        $variable = new \stdClass();
        $objectContext = \Exception::class;
        $result = $dataType->validateVariable($variable, $objectContext);
    }

    public function data_provider_with_empty_datatype_declarations()
    {
        return [
            [''],
            ['int|'],
            ['|int'],
        ];
    }

    /**
     * @test
     * @dataProvider data_provider_with_empty_datatype_declarations
     * @covers ::isValidVariableType()
     */
    public function it_throws_an_exception_on_empty_declarations($invalidDeclaration)
    {
        // given a DataTypeMetadata with a (partially) empty type
        $dataType = new DataTypeMetadata($invalidDeclaration);

        // when checking some value
        // then an exception is thrown
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'Illegal empty data type declaration "". Perhaps was "", or invalid union type like "int|"?'
        );

        $dataType->validateVariable('some value', $this);
    }

    public function data_provider_with_invalid_datatype_declarations()
    {
        return [
            ['[]'],
            ['int|[]'],
        ];
    }

    /**
     * @test
     * @dataProvider data_provider_with_invalid_datatype_declarations
     * @covers ::isValidVariableType()
     */
    public function it_throws_an_exception_on_array_declarations_without_type($invalidDeclaration)
    {
        // given a DataTypeMetadata with an array type declaration but without type
        $dataType = new DataTypeMetadata($invalidDeclaration);

        // when checking some value
        // then an exception is thrown
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'Illegal array declaration "[]" without type.'
        );

        $dataType->validateVariable('some value', $this);
    }

    /**
     * @test
     * @covers ::isValidVariableType()
     */
    public function it_throws_an_exception_on_unhandled_declarations()
    {
        // given a DataTypeMetadata with an unhandled type declaration
        $dataType = new DataTypeMetadata('resource');

        // when checking some value
        // then an exception is thrown
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(
            'Data type declaration "resource" not handled yet.'
        );

        $dataType->validateVariable('some value', $this);
    }
}
