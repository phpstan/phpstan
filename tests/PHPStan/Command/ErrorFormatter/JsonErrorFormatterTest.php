<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

class JsonErrorFormatterTest extends TestBaseFormatter
{

    public function dataFormatterOutputProvider(): iterable
    {
        yield [
            'No errors',
            0,
            0,
            0,
            '
{
	"totals":{
		"errors":0,
		"file_errors":0
	},
	"files":[],
	"errors": []
}',
        ];

        yield [
            'One file error',
            1,
            1,
            0,
            '
{
	"totals":{
		"errors":0,
		"file_errors":1
	},
	"files":{
		"/data/folder/with space/and unicode 😃/project/folder with unicode 😃/file name with \"spaces\" and unicode 😃.php":{
			"errors":1,
			"messages":[
				{
					"message": "Foo",
					"line": 4,
					"ignorable": true
				}
			]
		}
	},
	"errors": []
}',
        ];

        yield [
            'One generic error',
            1,
            0,
            1,
            '
{
	"totals":{
		"errors":1,
		"file_errors":0
	},
	"files":[],
	"errors": [
		"first generic error"
	]
}',
        ];

        yield [
            'Multiple file errors',
            1,
            4,
            0,
            '
{
	"totals":{
		"errors":0,
		"file_errors":4
	},
	"files":{
		"/data/folder/with space/and unicode 😃/project/folder with unicode 😃/file name with \"spaces\" and unicode 😃.php":{
			"errors":2,
			"messages":[
				{
					"message": "Bar",
					"line": 2,
					"ignorable": true
				},
				{
					"message": "Foo",
					"line": 4,
					"ignorable": true
				}
			]
		},
		"/data/folder/with space/and unicode 😃/project/foo.php":{
			"errors":2,
			"messages":[
				{
					"message": "Foo",
					"line": 1,
					"ignorable": true
				},
				{
					"message": "Bar",
					"line": 5,
					"ignorable": true
				}
			]
		}
	},
	"errors": []
}',
        ];

        yield [
            'Multiple generic errors',
            1,
            0,
            2,
            '
{
	"totals":{
		"errors":2,
		"file_errors":0
	},
	"files":[],
	"errors": [
		"first generic error",
		"second generic error"
	]
}',
        ];

        yield [
            'Multiple file, multiple generic errors',
            1,
            4,
            2,
            '
{
	"totals":{
		"errors":2,
		"file_errors":4
	},
	"files":{
		"/data/folder/with space/and unicode 😃/project/folder with unicode 😃/file name with \"spaces\" and unicode 😃.php":{
			"errors":2,
			"messages":[
				{
					"message": "Bar",
					"line": 2,
					"ignorable": true
				},
				{
					"message": "Foo",
					"line": 4,
					"ignorable": true
				}
			]
		},
		"/data/folder/with space/and unicode 😃/project/foo.php":{
			"errors":2,
			"messages":[
				{
					"message": "Foo",
					"line": 1,
					"ignorable": true
				},
				{
					"message": "Bar",
					"line": 5,
					"ignorable": true
				}
			]
		}
	},
	"errors": [
		"first generic error",
		"second generic error"
	]
}',
        ];
    }

    /**
     * @dataProvider dataFormatterOutputProvider
     *
     * @param string $message
     * @param int    $exitCode
     * @param int    $numFileErrors
     * @param int    $numGenericErrors
     * @param string $expected
     */
    public function testPrettyFormatErrors(
        string $message,
        int $exitCode,
        int $numFileErrors,
        int $numGenericErrors,
        string $expected
    ): void {
        $formatter = new JsonErrorFormatter(true);

        $this->assertSame($exitCode, $formatter->formatErrors(
            $this->getAnalysisResult($numFileErrors, $numGenericErrors),
            $this->getErrorConsoleStyle()
        ), $message);

        $this->assertJsonStringEqualsJsonString($expected, $this->getOutputContent());
    }

    /**
     * @dataProvider dataFormatterOutputProvider
     *
     * @param string $message
     * @param int    $exitCode
     * @param int    $numFileErrors
     * @param int    $numGenericErrors
     * @param string $expected
     *
     */
    public function testFormatErrors(
        string $message,
        int $exitCode,
        int $numFileErrors,
        int $numGenericErrors,
        string $expected
    ): void {
        $formatter = new JsonErrorFormatter(false);

        $this->assertSame($exitCode, $formatter->formatErrors(
            $this->getAnalysisResult($numFileErrors, $numGenericErrors),
            $this->getErrorConsoleStyle()
        ), sprintf('%s: response code do not match', $message));

        $this->assertJsonStringEqualsJsonString($expected, $this->getOutputContent(), sprintf('%s: JSON do not match', $message));
    }
}
