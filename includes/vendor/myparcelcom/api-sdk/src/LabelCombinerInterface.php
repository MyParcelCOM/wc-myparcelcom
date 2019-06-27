<?php

namespace MyParcelCom\ApiSdk;

use MyParcelCom\ApiSdk\Exceptions\MyParcelComException;
use MyParcelCom\ApiSdk\Resources\Interfaces\FileInterface;

interface LabelCombinerInterface
{
    const PAGE_SIZE_A4 = 'A4';
    const PAGE_SIZE_A5 = 'A5';
    const PAGE_SIZE_A6 = 'A6';

    const LOCATION_TOP = 1;
    const LOCATION_BOTTOM = 2;
    const LOCATION_RIGHT = 4;
    const LOCATION_LEFT = 8;

    const LOCATION_TOP_LEFT = self::LOCATION_TOP | self::LOCATION_LEFT;
    const LOCATION_TOP_RIGHT = self::LOCATION_TOP | self::LOCATION_RIGHT;
    const LOCATION_BOTTOM_LEFT = self::LOCATION_BOTTOM | self::LOCATION_LEFT;
    const LOCATION_BOTTOM_RIGHT = self::LOCATION_BOTTOM | self::LOCATION_RIGHT;

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
    );
}
