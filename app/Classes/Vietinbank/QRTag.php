<?php

namespace App\Classes\Vietinbank;

class QRTag
{
    public const PAY_LOAD = "00";
    public const POINT_OI_METHOD = "01";
    public const MC_ACCOUNT = "26";
    public const MC_ACCOUNT_GUID = "00";
    public const MC_ACCOUNT_MC_ID = "01";
    public const MC_CATEGORY_CODE = "52";
    public const CCY = "53";
    public const AMOUNT = "54";
    public const TIP_AND_FEE = "55";
    public const FIXED_FEE = "56";
    public const PERCENT_FEE = "57";
    public const COUNTRY_CODE = "58";
    public const MC_NAME = "59";
    public const MC_CITY = "60";
    public const MC_PIN_CODE = "61";
    public const ADDTIONAL_DATA = "62";
    public const CRC16 = "63";
    /**
     * For addtional data of Qrcode
     */
    public const BILL_NUMBER = "01";
    public const STORE_ID = "03"; // Terminal name
    public const LOYALTY_NUMBER = "04";
    public const REFERENCE_NUMBER = "05";
    public const CUSTOMER_ID = "06";
    public const TERMINAL_ID = "07";
    public const PERPOSE = "08";
    public const CONSUMER_DATA = "09";
    public const ADDRESS = "A";
    public const MOBILE = "M";
    public const EMAIL = "E";
    public const EXPIRE_DATE = "51";
    public const CHECK_SUM_ADDTIONAL = "52";
    public const TERM = "80";
    public const CRC_LENGHT = "04";
}