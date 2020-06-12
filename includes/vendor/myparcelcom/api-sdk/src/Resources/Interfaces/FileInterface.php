<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

use Psr\Http\Message\StreamInterface;

/**
 * Interface FileInterface
 *
 * @package MyParcelCom\ApiSdk\Resources\Interfaces
 */
interface FileInterface extends ResourceInterface
{
    const DOCUMENT_TYPE_LABEL = 'label';
    const DOCUMENT_TYPE_PRINTCODE = 'printcode';
    const DOCUMENT_TYPE_INVOICE = 'invoice';

    const MIME_TYPE_JSON = 'application/vnd.api+json';
    const MIME_TYPE_PNG = 'image/png';
    const MIME_TYPE_JPG = 'image/jpeg';
    const MIME_TYPE_PDF = 'application/pdf';

    const FORMAT_MIME_TYPE = 'mime_type';
    const FORMAT_EXTENSION = 'extension';

    /**
     * Set the identifier for this file.
     *
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * Set the type of file. See constants for possible options.
     *
     * @param string $documentType
     * @return $this
     */
    public function setDocumentType($documentType);

    /**
     * Get the type of file. See constants for possible options.
     *
     * @return string
     */
    public function getDocumentType();

    /**
     * Set the formats that this file has available.
     *
     * @param array $formats
     * @return $this
     */
    public function setFormats(array $formats);

    /**
     * Add a format this file is available as.
     *
     * @param string $mimeType
     * @param string $extension
     * @return $this
     */
    public function addFormat($mimeType, $extension);

    /**
     * Get an array of the available formats for this file. Each format is an
     * associative array with keys 'mime_type' and 'extension'.
     *
     * @return array
     */
    public function getFormats();

    /**
     * Set the stream of this file for given mime type.
     *
     * @param StreamInterface $stream
     * @param string          $mimeType
     * @return $this
     */
    public function setStream(StreamInterface $stream, $mimeType);

    /**
     * Get the a stream for this file. If no mime type supplied one is chosen in
     * the following order from the available formats:
     * application/pdf > image/png > image/jpeg > other
     *
     * @param string|null $mimeType
     * @return StreamInterface
     */
    public function getStream($mimeType = null);

    /**
     * Set the base64 encoded data of this file for given mime type.
     *
     * @param string $data
     * @param string $mimeType
     * @return $this
     */
    public function setBase64Data($data, $mimeType);

    /**
     * Get the file data as a base64 encoded string. If no mime type supplied
     * one is chosen in the following order from the available formats:
     * application/pdf > image/png > image/jpeg > other
     *
     * @param string|null $mimeType
     * @return string
     */
    public function getBase64Data($mimeType = null);

    /**
     * Set the path where this file can be found for given mime type.
     *
     * @param string $path
     * @param string $mimeType
     * @return $this
     */
    public function setTemporaryFilePath($path, $mimeType);

    /**
     * Get a path for this file saved in a temporary location on the filesystem.
     * If no mime type is supplied one is chosen in the following order from the
     * available formats:
     * application/pdf > image/png > image/jpeg > other
     *
     * @param string|null $mimeType
     * @return string
     */
    public function getTemporaryFilePath($mimeType = null);
}
