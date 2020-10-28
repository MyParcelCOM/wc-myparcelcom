<?php

namespace MyParcelCom\ApiSdk\Resources\Interfaces;

interface CustomsInterface extends \JsonSerializable
{
    const CONTENT_TYPE_MERCHANDISE = 'merchandise';
    const CONTENT_TYPE_SAMPLE_MERCHANDISE = 'sample_merchandise';
    const CONTENT_TYPE_RETURNED_MERCHANDISE = 'returned_merchandise';
    const CONTENT_TYPE_DOCUMENTS = 'documents';
    const CONTENT_TYPE_GIFTS = 'gifts';

    const NON_DELIVERY_RETURN = 'return';
    const NON_DELIVERY_ABANDON = 'abandon';

    const INCOTERM_DDU = 'DDU';
    const INCOTERM_DDP = 'DDP';

    /**
     * @param string $contentType
     * @return $this
     */
    public function setContentType($contentType);

    /**
     * @return string
     */
    public function getContentType();

    /**
     * @param string $invoiceNumber
     * @return $this
     */
    public function setInvoiceNumber($invoiceNumber);

    /**
     * @return string
     */
    public function getInvoiceNumber();

    /**
     * @param string $nonDelivery
     * @return $this
     */
    public function setNonDelivery($nonDelivery);

    /**
     * @return string
     */
    public function getNonDelivery();

    /**
     * @param string $incoterm
     * @return $this
     */
    public function setIncoterm($incoterm);

    /**
     * @return string
     */
    public function getIncoterm();
}
