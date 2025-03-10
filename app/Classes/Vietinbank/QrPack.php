<?php

namespace App\Classes\Vietinbank;

use IntlChar;
use Normalizer;

class QrPack
{
    private function padLeft(int $length): string
    {
        if ($length > 99) {
            throw new \Exception("Length field cannot be greater than 99");
        }
        return str_pad((string)$length, 2, '0', STR_PAD_LEFT);
    }

    private const EXPIRE_DATE_EMPTY = "0000000000";

    private function packMerchant(QRBean $bean): string
    {
        $content = "";
        if (!empty($bean->masterMerchant)) {
            $content .= QRTag::MC_ACCOUNT_GUID;
            $content .= $this->padLeft(strlen($bean->masterMerchant));
            $content .= $bean->masterMerchant;
        }
        if (!empty($bean->merchantCode)) {
            $content .= QRTag::MC_ACCOUNT_MC_ID;
            $content .= $this->padLeft(strlen($bean->merchantCode));
            $content .= $bean->merchantCode;
        }
        return $content;
    }

    public function pack(QRBean $bean, string $privateKey): QRPackBean
    {
        $content = "";
        if (!empty($bean->payLoad)) {
            $content .= QRTag::PAY_LOAD;
            $content .= $this->padLeft(strlen($bean->payLoad));
            $content .= $bean->payLoad;
        }
        if (!empty($bean->pointOIMethod)) {
            $content .= QRTag::POINT_OI_METHOD;
            $content .= $this->padLeft(strlen($bean->pointOIMethod));
            $content .= $bean->pointOIMethod;
        }
        $merchantAccount = $this->packMerchant($bean);
        if (!empty($merchantAccount)) {
            $content .= QRTag::MC_ACCOUNT;
            $content .= $this->padLeft(strlen($merchantAccount));
            $content .= $merchantAccount;
        }
        if (!empty($bean->merchantCC)) {
            $content .= QRTag::MC_CATEGORY_CODE;
            $content .= $this->padLeft(strlen($bean->merchantCC));
            $content .= $bean->merchantCC;
        }
        if (!empty($bean->ccy)) {
            $content .= QRTag::CCY;
            $content .= $this->padLeft(strlen($bean->ccy));
            $content .= $bean->ccy;
        }
        if (!empty($bean->amount)) {
            $content .= QRTag::AMOUNT;
            $content .= $this->padLeft(strlen($bean->amount));
            $content .= $bean->amount;
        }
        if (!empty($bean->tipAndFee)) {
            $content .= QRTag::TIP_AND_FEE;
            $content .= $this->padLeft(strlen($bean->tipAndFee));
            $content .= $bean->tipAndFee;
        }
        if (!empty($bean->fixedFee)) {
            $content .= QRTag::FIXED_FEE;
            $content .= $this->padLeft(strlen($bean->fixedFee));
            $content .= $bean->fixedFee;
        }
        if (!empty($bean->percentFee)) {
            $content .= QRTag::PERCENT_FEE;
            $content .= $this->padLeft(strlen($bean->percentFee));
            $content .= $bean->percentFee;
        }
        if (!empty($bean->countryCode)) {
            $content .= QRTag::COUNTRY_CODE;
            $content .= $this->padLeft(strlen($bean->countryCode));
            $content .= $bean->countryCode;
        }
        if (!empty($bean->merchantName)) {
            $content .= QRTag::MC_NAME;
            $content .= $this->padLeft(strlen($bean->merchantName));
            $content .= $bean->merchantName;
        }
        if (!empty($bean->merchantCity)) {
            $content .= QRTag::MC_CITY;
            $content .= $this->padLeft(strlen($bean->merchantCity));
            $content .= $bean->merchantCity;
        }
        if (!empty($bean->pinCode)) {
            $content .= QRTag::MC_PIN_CODE;
            $content .= $this->padLeft(strlen($bean->pinCode));
            $content .= $bean->pinCode;
        }
        $additionalData = $this->packAddtional($bean->addtionalBean, $privateKey);
        $bean->addtionalData = $additionalData;
        if (!empty($additionalData)) {
            $content .= QRTag::ADDTIONAL_DATA;
            $content .= $this->padLeft(strlen($additionalData));
            $content .= $additionalData;
        }
        if (!empty($bean->term)) {
            $content .= QRTag::TERM;
            $content .= $this->padLeft(strlen($bean->term));
            $content .= $bean->term;
        }
        $dataToCRC = $content . QRTag::CRC16 . QRTag::CRC_LENGHT;
        $crc16 = CRC16::CalcCRC16($dataToCRC);
        // Đảm bảo $crc16 có độ dài 4 ký tự
        // Kiểm tra độ dài và thực hiện padding nếu cần.
        if (strlen($crc16) < 4) {
            $crc16 = str_pad($crc16, 4, '0', STR_PAD_LEFT);
        } else if (strlen($crc16) > 4){
            $crc16 = substr($crc16, -4);
        }
        $bean->crc16 = $crc16;
        $pack = new QRPackBean();
        $pack->qrBean = $bean;
        $pack->qrData = $dataToCRC . $crc16;
        return $pack;
    }

    public function packAddtional(QRAddtionalBean $bean, string $privateKey): string
    {
        $content = "";
        if (!empty($bean->billNumber)) {
            $content .= QRTag::BILL_NUMBER;
            $content .= $this->padLeft(strlen($bean->billNumber));
            $content .= $bean->billNumber;
        }
        if (!empty($bean->storeID)) {
            $content .= QRTag::STORE_ID;
            $content .= $this->padLeft(strlen($bean->storeID));
            $content .= $bean->storeID;
        }
        if (!empty($bean->referenceID)) {
            $referenceID = "";
            $clearReferenceID = substr($bean->referenceID, 2);
            $prefix = substr($bean->referenceID, 0, 2);
            if (!empty($bean->expDate)) {
                $referenceID = $prefix . $bean->expDate . $clearReferenceID;
            } else {
                $referenceID = $prefix . self::EXPIRE_DATE_EMPTY . $clearReferenceID;
            }
            $content .= QRTag::REFERENCE_NUMBER;
            $content .= $this->padLeft(strlen($referenceID));
            $content .= $referenceID;
        }
        if (!empty($bean->customerID)) {
            $content .= QRTag::CUSTOMER_ID;
            $content .= $this->padLeft(strlen($bean->customerID));
            $content .= $bean->customerID;
        }
        if (!empty($bean->terminalID)) {
            $content .= QRTag::TERMINAL_ID;
            $content .= $this->padLeft(strlen($bean->terminalID));
            $content .= $bean->terminalID;
        }
        if (!empty($bean->purpose)) {
            $content .= QRTag::PERPOSE;
            $content .= $this->padLeft(strlen($bean->purpose));
            $content .= $this->removeDiacritics($bean->purpose);
        }
        $consumerData = "";
        if (!empty($bean->consumerAddress)) {
            $consumerData = $bean->consumerAddress;
        }
        if (!empty($bean->consumerMobile)) {
            $consumerData .= $bean->consumerMobile;
        }
        if (!empty($bean->consumerEmail)) {
            $consumerData .= $bean->consumerEmail;
        }
        if (!empty($consumerData)) {
            $content .= QRTag::CONSUMER_DATA;
            $content .= $this->padLeft(strlen($consumerData));
            $content .= $consumerData;
        }
        return $content;
    }

    public function removeDiacritics(string $text): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        $normalizedString = Normalizer::normalize($text, Normalizer::FORM_D);
        $stringBuilder = '';

        for ($i = 0; $i < mb_strlen($normalizedString); $i++) {
            $char = mb_substr($normalizedString, $i, 1);
            $unicodeCategory = IntlChar::charType($char);

            if ($unicodeCategory != IntlChar::CHAR_CATEGORY_NON_SPACING_MARK) {
                $stringBuilder .= $char;
            }
        }

        return Normalizer::normalize($stringBuilder, Normalizer::FORM_C);
    }
}