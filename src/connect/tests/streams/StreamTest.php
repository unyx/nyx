<?php namespace nyx\connect\tests\streams;

// Classes being tested
use nyx\connect\streams\Stream;

/**
 * Stream Test
 */
class StreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    public $tmpName;

    /**
     * @var Stream
     */
    protected $stream;

    public function setUp()
    {
        $this->tmpName = null;
        $this->stream = new Stream('php://memory', 'wb+');
    }

    public function tearDown()
    {
        if ($this->tmpName && file_exists($this->tmpName)) {
            unlink($this->tmpName);
        }
    }

    public function testCanInstantiateWithStreamIdentifier()
    {
        $this->assertInstanceOf(Stream::class, new Stream('php://memory', 'r'));
    }

    public function testCanInstantiteWithStreamResource()
    {
        $this->assertInstanceOf(Stream::class, new Stream(fopen('php://memory', 'r')));
    }

    public function testPassingInvalidTypeToConstructorRaisesException()
    {
        $this->setExpectedException('InvalidArgumentException');
        new Stream(['Invalid type']);
    }

    public function testIsReadableReturnsFalseIfStreamIsNotReadable()
    {
        $this->assertFalse($this->createInstanceFile('w')->isReadable());
    }

    public function testIsWritableReturnsFalseIfStreamIsNotWritable()
    {
        $this->assertFalse($this->createInstanceFile('r')->isWritable());
    }

    public function testToStringReturnsFullContentsOfStream()
    {
        $content = 'test data';
        $this->stream->write($content);
        $this->assertEquals($content, (string) $this->stream);
    }

    public function testConvertsToString()
    {
        $data   = 'test data';
        $stream = $this->createInstanceMemory('w');

        $stream->write($data);

        // Check twice to ensure the pointer is properly placed at the beginning
        // each time.
        $this->assertEquals($data, (string) $stream);
        $this->assertEquals($data, (string) $stream);

        $stream->close();
    }

    public function testStreamClosesHandleOnDestruct()
    {
        $resource = fopen('php://temp', 'r');
        $stream = new Stream($resource);
        unset($stream);
        $this->assertFalse(is_resource($resource));
    }

    public function testDetachReturnsResource()
    {
        $resource = fopen('php://temp', 'wb+');
        $stream   = new Stream($resource);
        $this->assertSame($resource, $stream->detach());
    }

    public function testCanDetachStream()
    {
        $resource = fopen('php://temp', 'w+');
        $stream = new Stream($resource);
        $stream->write('foo');
        $this->assertTrue($stream->isReadable());
        $this->assertSame($resource, $stream->detach());
        $stream->detach();

        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isSeekable());

        $testThrows = function (callable $method) use ($stream) {
            try {
                $method($stream);
                $this->fail();
            } catch (\Exception $e) {

            }
        };

        $testThrows(function ($stream) { $stream->tell(); });
        $testThrows(function ($stream) { $stream->eof(); });
        $testThrows(function ($stream) { $stream->read(100); });
        $testThrows(function ($stream) { $stream->write('TestData'); });
        $testThrows(function ($stream) { $stream->seek(100); });
        $testThrows(function ($stream) { $stream->getSize(); });
        $testThrows(function ($stream) { $stream->getContents(); });

        $this->assertSame('', (string) $stream);

        $stream->close();
    }

    public function testStringSerializationReturnsEmptyStringWhenStreamIsNotReadable()
    {
        $this->createSetTempName();

        file_put_contents($this->tmpName, 'test data');
        $stream = new Stream($this->tmpName, 'w');

        $this->assertEquals('', $stream->__toString());
    }

    public function testCloseClosesResource()
    {
        $this->createSetTempName();

        $resource = fopen($this->tmpName, 'wb+');
        $stream = new Stream($resource);
        $stream->close();
        $this->assertFalse(is_resource($resource));
    }

    public function testCloseClearProperties()
    {
        $stream = $this->createInstanceFile();
        $stream->close();

        $this->assertFalse($stream->isSeekable());
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertNull($stream->getSize());
        $this->assertEmpty($stream->getMetadata());
    }

    public function testCloseUnsetsResource()
    {
        $this->createSetTempName();

        $resource = fopen($this->tmpName, 'wb+');
        $stream = new Stream($resource);
        $stream->close();

        $this->assertNull($stream->detach());
    }

    public function testCloseDoesNothingAfterDetach()
    {
        $this->createSetTempName();

        $resource = fopen($this->tmpName, 'wb+');
        $stream = new Stream($resource);
        $detached = $stream->detach();

        $stream->close();
        $this->assertTrue(is_resource($detached));
        $this->assertSame($resource, $detached);
    }

    public function testSizeReportsNullWhenNoResourcePresent()
    {
        $this->stream->detach();
        $this->assertNull($this->stream->getSize());
    }

    public function testEnsuresSizeIsConsistent()
    {
        $resource = fopen('php://temp', 'w+');
        $this->assertEquals(3, fwrite($resource, 'foo'));

        $stream = new Stream($resource);
        $this->assertEquals(3, $stream->getSize());
        $this->assertEquals(3, $stream->write('bar'));
        $this->assertEquals(6, $stream->getSize());
        $this->assertEquals(6, $stream->getSize());
        $stream->close();
    }

    public function testGetSizeReturnsStreamSize()
    {
        $resource = fopen(__FILE__, 'r');
        $expected = fstat($resource);

        $this->assertEquals($expected['size'], (new Stream($resource))->getSize());
    }

    public function testTellReportsCurrentPositionInResource()
    {
        $this->createSetTempName();

        file_put_contents($this->tmpName, 'test data');
        $resource = fopen($this->tmpName, 'wb+');
        $stream = new Stream($resource);

        fseek($resource, 2);

        $this->assertEquals(2, $stream->tell());
    }

    public function testTellRaisesExceptionIfResourceIsDetached()
    {
        $this->createSetTempName();

        file_put_contents($this->tmpName, 'test data');
        $resource = fopen($this->tmpName, 'wb+');
        $stream = new Stream($resource);

        fseek($resource, 2);
        $stream->detach();
        $this->setExpectedException('RuntimeException', 'No stream resource');
        $stream->tell();
    }

    public function testEofReportsFalseWhenNotAtEndOfStream()
    {
        $this->createSetTempName();

        file_put_contents($this->tmpName, 'test data');
        $resource = fopen($this->tmpName, 'wb+');
        $stream = new Stream($resource);

        fseek($resource, 2);
        $this->assertFalse($stream->eof());
    }

    public function testEofReportsTrueWhenAtEndOfStream()
    {
        $this->createSetTempName();

        file_put_contents($this->tmpName, 'test data');
        $resource = fopen($this->tmpName, 'wb+');
        $stream = new Stream($resource);

        // Continue reading until we're at EOF.
        while (!feof($resource)) {
            fread($resource, 4096);
        }

        $this->assertTrue($stream->eof());
    }

    public function testEofReportsTrueWhenStreamIsDetached()
    {
        $this->createSetTempName();

        file_put_contents($this->tmpName, 'test data');
        $resource = fopen($this->tmpName, 'wb+');
        $stream = new Stream($resource);

        fseek($resource, 2);
        $stream->detach();
        $this->assertTrue($stream->eof());
    }

    public function testIsSeekableReturnsTrueForReadableStreams()
    {
        $this->createSetTempName();

        file_put_contents($this->tmpName, 'test data');
        $resource = fopen($this->tmpName, 'wb+');
        $stream = new Stream($resource);
        $this->assertTrue($stream->isSeekable());
    }

    public function testIsSeekableReturnsFalseForDetachedStreams()
    {
        $this->createSetTempName();

        file_put_contents($this->tmpName, 'test data');
        $resource = fopen($this->tmpName, 'wb+');
        $stream = new Stream($resource);
        $stream->detach();
        $this->assertFalse($stream->isSeekable());
    }

    public function testSeekAdvancesToGivenOffset()
    {
        $this->createSetTempName();

        file_put_contents($this->tmpName, 'test data');

        $stream = new Stream(fopen($this->tmpName, 'wb+'));

        $this->assertNull($stream->seek(2));
        $this->assertEquals(2, $stream->tell());
    }

    public function testRewindResetsToStartOfStream()
    {
        $this->createSetTempName();

        file_put_contents($this->tmpName, 'test data');
        $resource = fopen($this->tmpName, 'wb+');
        $stream = new Stream($resource);
        $this->assertNull($stream->seek(2));
        $stream->rewind();
        $this->assertEquals(0, $stream->tell());
    }

    public function testSeekRaisesExceptionWhenStreamIsDetached()
    {
        $this->createSetTempName();

        file_put_contents($this->tmpName, 'test data');
        $resource = fopen($this->tmpName, 'wb+');
        $stream = new Stream($resource);
        $stream->detach();
        $this->setExpectedException('RuntimeException', 'No stream resource');
        $stream->seek(2);
    }

    public function testIsWritableReturnsFalseWhenStreamIsDetached()
    {
        $this->createSetTempName();

        file_put_contents($this->tmpName, 'test data');
        $resource = fopen($this->tmpName, 'wb+');
        $stream = new Stream($resource);
        $stream->detach();
        $this->assertFalse($stream->isWritable());
    }

    public function testIsWritableReturnsTrueForWritableMemoryStream()
    {
        $stream = new Stream("php://temp", "r+b");
        $this->assertTrue($stream->isWritable());
    }

    private function findNonExistentTempName()
    {
        while (true) {
            $tmpnam = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'nyx' . uniqid();
            if (!file_exists(sys_get_temp_dir() . $tmpnam)) {
                break;
            }
        }
        return $tmpnam;
    }

    public function provideDataForIsWritable()
    {
        return [
            ['a',   true,  true],
            ['a+',  true,  true],
            ['a+b', true,  true],
            ['ab',  true,  true],
            ['c',   true,  true],
            ['c+',  true,  true],
            ['c+b', true,  true],
            ['cb',  true,  true],
            ['r',   true,  false],
            ['r+',  true,  true],
            ['r+b', true,  true],
            ['rb',  true,  false],
            ['rw',  true,  true],
            ['w',   true,  true],
            ['w+',  true,  true],
            ['w+b', true,  true],
            ['wb',  true,  true],
            ['x',   false, true],
            ['x+',  false, true],
            ['x+b', false, true],
            ['xb',  false, true],
        ];
    }

    /**
     * @dataProvider provideDataForIsWritable
     */
    public function testIsWritableReturnsCorrectFlagForMode($mode, $fileShouldExist, $flag)
    {
        if ($fileShouldExist) {
            $this->tmpName = tempnam(sys_get_temp_dir(), 'nyx');
            file_put_contents($this->tmpName, 'test data');
        } else {
            // "x" modes REQUIRE that file doesn't exist, so we need to find random file name
            $this->tmpName = $this->findNonExistentTempName();
        }
        $resource = fopen($this->tmpName, $mode);
        $stream = new Stream($resource);
        $this->assertEquals($flag, $stream->isWritable());
    }

    public function provideDataForIsReadable()
    {
        return [
            ['a',   true,  false],
            ['a+',  true,  true],
            ['a+b', true,  true],
            ['ab',  true,  false],
            ['c',   true,  false],
            ['c+',  true,  true],
            ['c+b', true,  true],
            ['cb',  true,  false],
            ['r',   true,  true],
            ['r+',  true,  true],
            ['r+b', true,  true],
            ['rb',  true,  true],
            ['rw',  true,  true],
            ['w',   true,  false],
            ['w+',  true,  true],
            ['w+b', true,  true],
            ['wb',  true,  false],
            ['x',   false, false],
            ['x+',  false, true],
            ['x+b', false, true],
            ['xb',  false, false],
        ];
    }

    /**
     * @dataProvider provideDataForIsReadable
     */
    public function testIsReadableReturnsCorrectFlagForMode($mode, $fileShouldExist, $flag)
    {
        if ($fileShouldExist) {
            $this->tmpName = tempnam(sys_get_temp_dir(), 'nyx');
            file_put_contents($this->tmpName, 'test data');
        } else {
            // "x" modes REQUIRE that file doesn't exist, so we need to find random file name
            $this->tmpName = $this->findNonExistentTempName();
        }
        $resource = fopen($this->tmpName, $mode);
        $stream = new Stream($resource);
        $this->assertEquals($flag, $stream->isReadable());
    }

    public function testWriteRaisesExceptionWhenStreamIsDetached()
    {
        $this->createSetTempName();

        file_put_contents($this->tmpName, 'test data');
        $resource = fopen($this->tmpName, 'wb+');
        $stream = new Stream($resource);
        $stream->detach();
        $this->setExpectedException('RuntimeException', 'No stream resource');
        $stream->write('bar');
    }

    public function testWriteRaisesExceptionWhenStreamIsNotWritable()
    {
        $stream = new Stream('php://memory', 'r');
        $this->setExpectedException('RuntimeException', 'Cannot write to non-writable stream');
        $stream->write('bar');
    }

    public function testIsReadableReturnsFalseWhenStreamIsDetached()
    {
        $this->createSetTempName();

        file_put_contents($this->tmpName, 'test data');
        $resource = fopen($this->tmpName, 'wb+');
        $stream = new Stream($resource);
        $stream->detach();
        $this->assertFalse($stream->isReadable());
    }

    public function testReadRaisesExceptionWhenStreamIsDetached()
    {
        $this->createSetTempName();

        file_put_contents($this->tmpName, 'test data');
        $resource = fopen($this->tmpName, 'r');
        $stream = new Stream($resource);
        $stream->detach();
        $this->setExpectedException('RuntimeException', 'No stream resource');
        $stream->read(4096);
    }

    public function testReadReturnsEmptyStringWhenAtEndOfFile()
    {
        $this->createSetTempName();

        file_put_contents($this->tmpName, 'test data');
        $resource = fopen($this->tmpName, 'r');
        $stream = new Stream($resource);

        while (!feof($resource)) {
            fread($resource, 4096);
        }

        $this->assertEquals('', $stream->read(4096));
    }

    public function testGetContentsRisesExceptionIfStreamIsNotReadable()
    {
        $this->createSetTempName();

        file_put_contents($this->tmpName, 'test data');
        $resource = fopen($this->tmpName, 'w');
        $stream = new Stream($resource);
        $this->setExpectedException('RuntimeException');
        $stream->getContents();
    }

    public function testGetContentsShouldGetFullStreamContents()
    {
        $this->createSetTempName();

        $resource = fopen($this->tmpName, 'r+');
        $stream = new Stream($resource);

        fwrite($resource, 'TestData');

        $stream->rewind();
        $test = $stream->getContents();
        $this->assertEquals('TestData', $test);
    }

    public function testGetContentsShouldReturnStreamContentsFromCurrentPointer()
    {
        $this->createSetTempName();

        $resource = fopen($this->tmpName, 'r+');
        $stream = new Stream($resource);

        fwrite($resource, 'TestData');

        $stream->seek(4);
        $test = $stream->getContents();
        $this->assertEquals('Data', $test);
    }

    public function testGetMetadataReturnsAllMetadataWhenNoKeyPresent()
    {
        $this->createSetTempName();

        $resource = fopen($this->tmpName, 'r+w');
        $stream = new Stream($resource);

        $expected = stream_get_meta_data($resource);
        $test     = $stream->getMetadata();

        $this->assertEquals($expected, $test);
    }

    public function testGetMetadataReturnsDataForSpecifiedKey()
    {
        $this->createSetTempName();

        $resource = fopen($this->tmpName, 'r');
        $stream = new Stream($resource);

        $metadata = stream_get_meta_data($resource);
        $expected = $metadata['uri'];

        $test     = $stream->getMetadata('uri');

        $this->assertEquals($expected, $test);
    }

    public function testGetMetadataReturnsNullIfNoDataExistsForKey()
    {
        $this->assertNull($this->createInstanceFile()->getMetadata('NON-EXISTENT-KEY'));
    }

    protected function createInstanceMemory($mode = 'wb+')
    {
        return new Stream('php://memory', $mode);
    }

    protected function createInstanceFile($mode = 'wb+')
    {
        $this->tmpName = tempnam(sys_get_temp_dir(), 'nyx');

        return new Stream($this->tmpName, $mode);
    }

    protected function createSetTempName()
    {
        $this->tmpName = tempnam(sys_get_temp_dir(), 'nyx');
    }
}
