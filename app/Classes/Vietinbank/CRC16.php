<?php

namespace App\Classes\Vietinbank;

class CRC16
{
    public static function CalcCRC16(string $strInput): string
    {
        $strInput = self::ConvertStringToHex($strInput);
        $crc = 0xFFFF;
        $data = self::StringToByteArray($strInput);
        for ($i = 0; $i < count($data); $i++) {
            $crc ^= ($data[$i] << 8);
            for ($j = 0; $j < 8; $j++) {
                if (($crc & 0x8000) > 0) {
                    $crc = (($crc << 1) ^ 0x1021);
                } else {
                    $crc <<= 1;
                }
            }
        }
        return strtoupper(dechex($crc));
    }

    public static function StringToByteArray(string $hex): array
    {
        $byteArray = [];
        for ($i = 0; $i < strlen($hex); $i += 2) {
            $byteArray[] = hexdec(substr($hex, $i, 2));
        }
        return $byteArray;
    }

    public static function ConvertStringToHex(string $asciiString): string
    {
        $hex = "";
        for ($i = 0; $i < strlen($asciiString); $i++) {
            $tmp = ord($asciiString[$i]);
            $hex .= sprintf("%02x", $tmp);
        }
        return $hex;
    }
}