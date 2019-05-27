<?php

namespace MyParcelCom\ApiSdk\Tests\Unit;

use MyParcelCom\ApiSdk\Exceptions\LabelCombinerException;
use MyParcelCom\ApiSdk\LabelCombiner;
use MyParcelCom\ApiSdk\LabelCombinerInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\FileInterface;
use PHPUnit\Framework\TestCase;

class LabelCombinerTest extends TestCase
{
    /** @var FileInterface */
    private $fileA;
    /** @var FileInterface */
    private $fileB;
    /** @var FileInterface */
    private $fileC;

    protected function setUp()
    {
        parent::setUp();

        $this->fileA = $this->getMockBuilder(FileInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $this->fileA->method('getTemporaryFilePath')
            ->willReturn(dirname(__DIR__) . '/Stubs/files/label-a.pdf');
        $this->fileA->method('getFormats')
            ->willReturn([
                [
                    FileInterface::FORMAT_MIME_TYPE => FileInterface::MIME_TYPE_PDF,
                    FileInterface::FORMAT_EXTENSION => 'pdf',
                ],
            ]);

        $this->fileB = $this->getMockBuilder(FileInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $this->fileB->method('getTemporaryFilePath')
            ->willReturn(dirname(__DIR__) . '/Stubs/files/label-b.pdf');
        $this->fileB->method('getFormats')
            ->willReturn([
                [
                    FileInterface::FORMAT_MIME_TYPE => FileInterface::MIME_TYPE_PDF,
                    FileInterface::FORMAT_EXTENSION => 'pdf',
                ],
            ]);

        $this->fileC = $this->getMockBuilder(FileInterface::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        $this->fileC->method('getTemporaryFilePath')
            ->willReturn(dirname(__DIR__) . '/Stubs/files/label-c.jpg');
        $this->fileC->method('getFormats')
            ->willReturn([
                [
                    FileInterface::FORMAT_MIME_TYPE => FileInterface::MIME_TYPE_JPG,
                    FileInterface::FORMAT_EXTENSION => 'jpg',
                ],
            ]);
    }

    /** @test */
    public function testCombineLabels()
    {
        $combiner = new LabelCombiner();
        $file = $combiner->combineLabels(
            [
                $this->fileA,
                $this->fileB,
                $this->fileC,
            ]
        );

        $this->assertNotEmpty($file->getTemporaryFilePath());
        $this->assertNotEmpty($file->getBase64Data());
        $this->assertNotEmpty($file->getFormats());
        $this->assertEquals([
            [
                FileInterface::FORMAT_MIME_TYPE => FileInterface::MIME_TYPE_PDF,
                FileInterface::FORMAT_EXTENSION => 'pdf',
            ],
        ], $file->getFormats());
    }

    /** @test */
    public function testCombineLabelsA5()
    {
        $combiner = new LabelCombiner();
        $file = $combiner->combineLabels(
            [
                $this->fileA,
                $this->fileB,
                $this->fileC,
            ],
            LabelCombinerInterface::PAGE_SIZE_A5
        );

        $this->assertNotEmpty($file->getTemporaryFilePath());
        $this->assertNotEmpty($file->getBase64Data());
        $this->assertNotEmpty($file->getFormats());
        $this->assertEquals([
            [
                FileInterface::FORMAT_MIME_TYPE => FileInterface::MIME_TYPE_PDF,
                FileInterface::FORMAT_EXTENSION => 'pdf',
            ],
        ], $file->getFormats());
    }

    /** @test */
    public function testCombineLabelsA6()
    {
        $combiner = new LabelCombiner();
        $file = $combiner->combineLabels(
            [
                $this->fileA,
                $this->fileB,
                $this->fileC,
            ],
            LabelCombinerInterface::PAGE_SIZE_A6
        );

        $this->assertNotEmpty($file->getTemporaryFilePath());
        $this->assertNotEmpty($file->getBase64Data());
        $this->assertNotEmpty($file->getFormats());
        $this->assertEquals([
            [
                FileInterface::FORMAT_MIME_TYPE => FileInterface::MIME_TYPE_PDF,
                FileInterface::FORMAT_EXTENSION => 'pdf',
            ],
        ], $file->getFormats());
    }

    /** @test */
    public function testCombineLabelsStartBottomRight()
    {
        $combiner = new LabelCombiner();
        $file = $combiner->combineLabels(
            [
                $this->fileA,
                $this->fileB,
                $this->fileC,
            ],
            LabelCombinerInterface::PAGE_SIZE_A4,
            LabelCombinerInterface::LOCATION_BOTTOM_RIGHT
        );

        $this->assertNotEmpty($file->getTemporaryFilePath());
        $this->assertNotEmpty($file->getBase64Data());
        $this->assertNotEmpty($file->getFormats());
        $this->assertEquals([
            [
                FileInterface::FORMAT_MIME_TYPE => FileInterface::MIME_TYPE_PDF,
                FileInterface::FORMAT_EXTENSION => 'pdf',
            ],
        ], $file->getFormats());
    }

    /** @test */
    public function testCombineLabelsWithMargin()
    {
        $combiner = new LabelCombiner();
        $file = $combiner->combineLabels(
            [
                $this->fileA,
                $this->fileB,
                $this->fileC,
            ],
            LabelCombinerInterface::PAGE_SIZE_A5,
            LabelCombinerInterface::LOCATION_BOTTOM,
            30
        );

        $this->assertNotEmpty($file->getTemporaryFilePath());
        $this->assertNotEmpty($file->getBase64Data());
        $this->assertNotEmpty($file->getFormats());
        $this->assertEquals([
            [
                FileInterface::FORMAT_MIME_TYPE => FileInterface::MIME_TYPE_PDF,
                FileInterface::FORMAT_EXTENSION => 'pdf',
            ],
        ], $file->getFormats());
    }

    /** @test */
    public function testLabelsPerPageException()
    {
        $combiner = new LabelCombiner();

        $this->expectException(LabelCombinerException::class);
        $combiner->combineLabels([], null);
    }
}
