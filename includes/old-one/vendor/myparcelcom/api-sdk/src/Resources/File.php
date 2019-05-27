<?php

namespace MyParcelCom\ApiSdk\Resources;

use MyParcelCom\ApiSdk\Resources\Interfaces\FileInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;
use Psr\Http\Message\StreamInterface;
use function GuzzleHttp\Psr7\stream_for;

class File implements FileInterface
{
    use JsonSerializable;

    const ATTRIBUTE_DOCUMENT_TYPE = 'document_type';
    const ATTRIBUTE_FORMATS = 'formats';

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_FILE;

    /** @var array */
    private $attributes = [
        self::ATTRIBUTE_FORMATS       => [],
        self::ATTRIBUTE_DOCUMENT_TYPE => null,
    ];

    /** @var StreamInterface[] */
    private $streams = [];

    /** @var string[] */
    private $base64Data = [];

    /** @var string[] */
    private $paths = [];

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setDocumentType($documentType)
    {
        $this->attributes[self::ATTRIBUTE_DOCUMENT_TYPE] = $documentType;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentType()
    {
        return $this->attributes[self::ATTRIBUTE_DOCUMENT_TYPE];
    }

    /**
     * {@inheritdoc}
     */
    public function setFormats(array $formats)
    {
        $this->attributes[self::ATTRIBUTE_FORMATS] = [];
        array_walk($formats, function ($format) {
            $this->addFormat($format[self::FORMAT_MIME_TYPE], $format[self::FORMAT_EXTENSION]);
        });

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFormat($mimeType, $extension)
    {
        $this->attributes[self::ATTRIBUTE_FORMATS][] = [
            self::FORMAT_MIME_TYPE => $mimeType,
            self::FORMAT_EXTENSION => $extension,
        ];

        usort($this->attributes[self::ATTRIBUTE_FORMATS], function ($formatA, $formatB) {
            $mimeTypeOrder = [self::MIME_TYPE_PDF => -3, self::MIME_TYPE_PNG => -2, self::MIME_TYPE_JPG => -1];

            return $mimeTypeOrder[$formatA[self::FORMAT_MIME_TYPE]] - $mimeTypeOrder[$formatB[self::FORMAT_MIME_TYPE]];
        });

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormats()
    {
        return $this->attributes[self::ATTRIBUTE_FORMATS];
    }

    /**
     * {@inheritdoc}
     */
    public function setStream(StreamInterface $stream, $mimeType)
    {
        $this->streams[$mimeType] = $stream;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStream($mimeType = null)
    {
        if ($mimeType === null) {
            foreach ($this->getFormats() as $format) {
                $stream = $this->getStream($format[self::FORMAT_MIME_TYPE]);

                if ($stream !== null) {
                    return $stream;
                }
            };

            return null;
        }

        if (isset($this->streams[$mimeType])) {
            return $this->streams[$mimeType];
        }
        if (isset($this->base64Data[$mimeType])) {
            return stream_for(base64_decode($this->base64Data[$mimeType]));
        }
        if (isset($this->paths[$mimeType])) {
            return stream_for(fopen($this->paths[$mimeType], 'r'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setBase64Data($data, $mimeType)
    {
        $this->base64Data[$mimeType] = $data;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBase64Data($mimeType = null)
    {
        if ($mimeType === null) {
            foreach ($this->getFormats() as $format) {
                $data = $this->getBase64Data($format[self::FORMAT_MIME_TYPE]);

                if ($data !== null) {
                    return $data;
                }
            };

            return null;
        }

        if (isset($this->base64Data[$mimeType])) {
            return $this->base64Data[$mimeType];
        }
        if (isset($this->paths[$mimeType])) {
            return $this->base64Data[$mimeType] = base64_encode(file_get_contents($this->paths[$mimeType]));
        }
        if (isset($this->streams[$mimeType])) {
            // Rewind the stream to ensure that we're getting all of the contents.
            $this->streams[$mimeType]->rewind();

            return $this->base64Data[$mimeType] = base64_encode($this->streams[$mimeType]->getContents());
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setTemporaryFilePath($path, $mimeType)
    {
        $this->paths[$mimeType] = $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemporaryFilePath($mimeType = null)
    {
        $extension = null;
        foreach ($this->getFormats() as $format) {
            if ($mimeType === null || $mimeType === $format[self::FORMAT_MIME_TYPE]) {
                $mimeType = $format[self::FORMAT_MIME_TYPE];
                $extension = $format[self::FORMAT_EXTENSION];
                break;
            }
        };

        if (isset($this->paths[$mimeType])) {
            return $this->paths[$mimeType];
        }
        if (isset($this->base64Data[$mimeType])) {
            $path = tempnam(sys_get_temp_dir(), 'myparcelcom_file') . '.' . $extension;
            file_put_contents($path, base64_decode($this->base64Data[$mimeType]));

            return $this->paths[$mimeType] = $path;
        }
        if (isset($this->streams[$mimeType])) {
            $path = tempnam(sys_get_temp_dir(), 'myparcelcom_file') . '.' . $extension;
            file_put_contents($path, $this->streams[$mimeType]->getContents());

            return $this->paths[$mimeType] = $path;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $values = get_object_vars($this);
        unset($values['streams']);
        unset($values['base64Data']);
        unset($values['paths']);

        $json = $this->arrayValuesToArray($values);

        if (isset($json['attributes']) && $this->isEmpty($json['attributes'])) {
            unset($json['attributes']);
        }

        return $json;
    }
}
