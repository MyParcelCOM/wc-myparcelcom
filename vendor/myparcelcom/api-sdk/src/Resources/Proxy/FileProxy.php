<?php

namespace MyParcelCom\ApiSdk\Resources\Proxy;

use MyParcelCom\ApiSdk\Resources\Interfaces\FileInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceInterface;
use MyParcelCom\ApiSdk\Resources\Interfaces\ResourceProxyInterface;
use MyParcelCom\ApiSdk\Resources\Traits\JsonSerializable;
use MyParcelCom\ApiSdk\Resources\Traits\ProxiesResource;
use Psr\Http\Message\StreamInterface;

class FileProxy implements FileInterface, ResourceProxyInterface
{
    use JsonSerializable;
    use ProxiesResource;

    /** @var string */
    private $id;

    /** @var string */
    private $type = ResourceInterface::TYPE_FILE;

    /**
     * Set the identifier for this file.
     *
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set the type of file. See constants for possible options.
     *
     * @param string $documentType
     * @return $this
     */
    public function setDocumentType($documentType)
    {
        $this->getResource()->setDocumentType($documentType);

        return $this;
    }

    /**
     * Get the type of file. See constants for possible options.
     *
     * @return string
     */
    public function getDocumentType()
    {
        return $this->getResource()->getDocumentType();
    }

    /**
     * Set the formats that this file has available.
     *
     * @param array $formats
     * @return $this
     */
    public function setFormats(array $formats)
    {
        $this->getResource()->setFormats($formats);

        return $this;
    }

    /**
     * Add a format this file is available as.
     *
     * @param string $mimeType
     * @param string $extension
     * @return $this
     */
    public function addFormat($mimeType, $extension)
    {
        $this->getResource()->addFormat($mimeType, $extension);

        return $this;
    }

    /**
     * Get an array of the available formats for this file. Each format is an
     * associative array with keys 'mime_type' and 'extension'.
     *
     * @return array
     */
    public function getFormats()
    {
        return $this->getResource()->getFormats();
    }

    /**
     * Set the stream of this file for given mime type.
     *
     * @param StreamInterface $stream
     * @param string          $mimeType
     * @return $this
     */
    public function setStream(StreamInterface $stream, $mimeType)
    {
        $this->getResource()->setStream($stream, $mimeType);

        return $this;
    }

    /**
     * Get the a stream for this file. If no mime type supplied one is chosen in
     * the following order from the available formats:
     * application/pdf > image/png > image/jpeg > other
     *
     * @param string|null $mimeType
     * @return StreamInterface
     */
    public function getStream($mimeType = null)
    {
        return $this->getResource()->getStream($mimeType);
    }

    /**
     * Set the base64 encoded data of this file for given mime type.
     *
     * @param string $data
     * @param string $mimeType
     * @return $this
     */
    public function setBase64Data($data, $mimeType)
    {
        $this->getResource()->setBase64Data($data, $mimeType);

        return $this;
    }

    /**
     * Get the file data as a base64 encoded string. If no mime type supplied
     * one is chosen in the following order from the available formats:
     * application/pdf > image/png > image/jpeg > other
     *
     * @param string|null $mimeType
     * @return string
     */
    public function getBase64Data($mimeType = null)
    {
        return $this->getResource()->getBase64Data($mimeType);
    }

    /**
     * Set the path where this file can be found for given mime type.
     *
     * @param string $path
     * @param string $mimeType
     * @return $this
     */
    public function setTemporaryFilePath($path, $mimeType)
    {
        $this->getResource()->setTemporaryFilePath($path, $mimeType);

        return $this;
    }

    /**
     * Get a path for this file saved in a temporary location on the filesystem.
     * If no mime type is supplied one is chosen in the following order from the
     * available formats:
     * application/pdf > image/png > image/jpeg > other
     *
     * @param string|null $mimeType
     * @return string
     */
    public function getTemporaryFilePath($mimeType = null)
    {
        return $this->getResource()->getTemporaryFilePath($mimeType);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * This function puts all object properties in an array and returns it.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $values = get_object_vars($this);
        unset($values['resource']);
        unset($values['api']);
        unset($values['uri']);

        return $this->arrayValuesToArray($values);
    }
}
