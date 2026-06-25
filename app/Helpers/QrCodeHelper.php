<?php

namespace App\Helpers;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

class QrCodeHelper
{
    public static function svg(string $data, int $size = 100): string
    {
        try {
            $qrCode = new QrCode(data: $data, size: $size);
            $writer = new SvgWriter();
            $result = $writer->write($qrCode);
            return $result->getString();
        } catch (\Exception $e) {
            return '';
        }
    }
}
