<?php

namespace App\Classes\Vietinbank;

class ServiceConfig
{
    public const QR_IMAGE_TYPE = "png";
    public const QR_IMAGE_SIZE = 255;
    public const QR_IMAGE_FOLDER = "/resources/qrcode/";
    public const QR_VIEW_PATH = "http://10.22.7.103:8080/QRCreateAPIRestV2/resources/qrcode/";
    public const QR_VIEW_PATH_INTERNET = "http://14.160.87.122:8080/QRCreateAPIRestV2/resources/qrcode/";
    public const API_ID = "restcreateqr";
    public const LIST_PAYLOAD = "01,02";
    public const PAYLOAD_FORMAT_INDICATOR = "01";
    public const POINT_OF_METHOD_DONG = "12";
    public const POINT_OF_METHOD_TINH = "11";
    public const CCY = "704";
}