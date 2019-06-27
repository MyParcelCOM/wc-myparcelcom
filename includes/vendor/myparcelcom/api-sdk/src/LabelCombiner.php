<?php

namespace MyParcelCom\ApiSdk;

use MyParcelCom\ApiSdk\Exceptions\LabelCombinerException;
use MyParcelCom\ApiSdk\Exceptions\MyParcelComException;
use MyParcelCom\ApiSdk\Resources\File;
use MyParcelCom\ApiSdk\Resources\Interfaces\FileInterface;
use setasign\Fpdi\Fpdi;

class LabelCombiner implements LabelCombinerInterface
{
    const LABEL_WIDTH = 420.94;
    const LABEL_HEIGHT = 297.64;

    /**
     * Combines given labels into 1 file and returns it. When a starting
     * location on the page is chosen, up to 3 locations will be left blank.
     * This allows for re-using of label paper that has 1 or more labels
     * already used.
     *
     * The following are the order of positions per page size:
     *
     * PAGE_SIZE_A4
     * - LOCATION_TOP_LEFT
     * - LOCATION_TOP_RIGHT
     * - LOCATION_BOTTOM_LEFT
     * - LOCATION_BOTTOM_RIGHT
     *
     * PAGE_SIZE_A5
     * - LOCATION_TOP
     * - LOCATION_BOTTOM
     *
     * PAGE_SIZE_A6
     * - LOCATION_TOP
     *
     * @param FileInterface[] $files
     * @param string          $pageSize
     * @param int             $startLocation
     * @param int             $margin
     * @throws MyParcelComException
     * @return FileInterface
     */
    public function combineLabels(
        array $files,
        $pageSize = self::PAGE_SIZE_A4,
        $startLocation = self::LOCATION_TOP_LEFT,
        $margin = 0
    ) {
        list($labelsPerRow, $labelsPerColumn) = $this->getLabelsPerPage($pageSize);
        $labelsPerPage = $labelsPerRow * $labelsPerColumn;

        $pdf = new Fpdi(
            $this->getOrientation($pageSize),
            'pt',
            $pageSize === self::PAGE_SIZE_A6
                ? [self::LABEL_HEIGHT, self::LABEL_WIDTH]
                : $pageSize
        );

        // If we're not starting at position 0, add a page, because it won't be
        // added in the loop.
        if (($labelPosition = $this->getStartPosition($pageSize, $startLocation)) > 0) {
            $pdf->AddPage();
        }

        foreach ($files as $file) {
            // Whenever we the max number of labels is on a page, create a
            // new page.
            if ($labelPosition % $labelsPerPage === 0) {
                $pdf->AddPage();
            }

            list($x, $y, $width) = $this->calculateDimensions($labelPosition, $labelsPerRow, $labelsPerColumn, $margin);

            $formats = $file->getFormats();
            $format = reset($formats);
            if ($format[FileInterface::FORMAT_MIME_TYPE] !== FileInterface::MIME_TYPE_PDF) {
                // Add the image (label) to the pdf.
                $pdf->Image($file->getTemporaryFilePath(), $x, $y, $width);
            } else {
                // Add the page (label) to the pdf.
                $pdf->setSourceFile($file->getTemporaryFilePath());
                $pageId = $pdf->importPage(1);
                $pdf->useTemplate($pageId, $x, $y, $width);
            }

            $labelPosition++;
        }

        return (new File())
            ->addFormat(FileInterface::MIME_TYPE_PDF, 'pdf')
            ->setBase64Data(
                base64_encode($pdf->Output('S')),
                FileInterface::MIME_TYPE_PDF
            );
    }

    /**
     * Get an array with the number of labels per row and column for given page
     * size.
     *
     * @param string $pageSize
     * @return array
     */
    private function getLabelsPerPage($pageSize)
    {
        switch ($pageSize) {
            case self::PAGE_SIZE_A4:
                return [2, 2];
            case self::PAGE_SIZE_A5:
                return [1, 2];
            case self::PAGE_SIZE_A6:
                return [1, 1];
            default:
                throw new LabelCombinerException('unknown page size: ' . $pageSize);
        }
    }

    /**
     * Get the orientation for given page size.
     *
     * @param string $pageSize
     * @return string
     */
    private function getOrientation($pageSize)
    {
        switch ($pageSize) {
            case self::PAGE_SIZE_A4:
            case self::PAGE_SIZE_A6:
                return 'L';
            case self::PAGE_SIZE_A5:
                return 'P';
            default:
                throw new LabelCombinerException('unknown page size: ' . $pageSize);
        }
    }

    /**
     * Return the start position, 0 being top left and 3 being bottom right.
     *
     * @param string $pageSize
     * @param int    $startLocation
     * @return int
     */
    private function getStartPosition($pageSize, $startLocation)
    {
        $top = $startLocation & self::LOCATION_TOP;
        $left = $startLocation & self::LOCATION_LEFT;

        switch ($pageSize) {
            case self::PAGE_SIZE_A4:
                return ($top ? 0 : 2) + ($left ? 0 : 1);
            case self::PAGE_SIZE_A5:
                return $top ? 0 : 1;
            case self::PAGE_SIZE_A6:
                return 0;
            default:
                throw new LabelCombinerException('unknown page size: ' . $pageSize);
        }
    }

    /**
     * Get an array with the x position, y position and width of the label on
     * given label position.
     *
     * @param int $labelPosition
     * @param int $labelsPerRow
     * @param int $labelsPerColumn
     * @param int $margin
     * @return array
     */
    private function calculateDimensions($labelPosition, $labelsPerRow, $labelsPerColumn, $margin)
    {
        // The x position should be the margin, plus the label width for
        // every label printed on the right side.
        $x = $margin + ($labelPosition % $labelsPerRow) * self::LABEL_WIDTH;

        // The y position should be the margin scaled to the margin for
        // x (√2:2), plus the label height if the label is on the
        // bottom.
        $y = $margin * sqrt(2) / 2 + (floor($labelPosition / $labelsPerRow) % $labelsPerColumn) * self::LABEL_HEIGHT;

        // The width is the label width, minus twice the margin (1 for
        // each side).
        $width = self::LABEL_WIDTH - $margin * 2;

        return [
            $x,
            $y,
            $width,
        ];
    }
}
