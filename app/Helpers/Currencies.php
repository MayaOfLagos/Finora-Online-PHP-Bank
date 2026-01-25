<?php

namespace App\Helpers;

class Currencies
{
    /**
     * Get all currencies as associative array [code => name with symbol]
     */
    public static function all(): array
    {
        return [
            'USD' => 'US Dollar ($)',
            'EUR' => 'Euro (€)',
            'GBP' => 'British Pound (£)',
            'JPY' => 'Japanese Yen (¥)',
            'AUD' => 'Australian Dollar (A$)',
            'CAD' => 'Canadian Dollar (C$)',
            'CHF' => 'Swiss Franc (CHF)',
            'CNY' => 'Chinese Yuan (¥)',
            'HKD' => 'Hong Kong Dollar (HK$)',
            'NZD' => 'New Zealand Dollar (NZ$)',
            'SEK' => 'Swedish Krona (kr)',
            'KRW' => 'South Korean Won (₩)',
            'SGD' => 'Singapore Dollar (S$)',
            'NOK' => 'Norwegian Krone (kr)',
            'MXN' => 'Mexican Peso ($)',
            'INR' => 'Indian Rupee (₹)',
            'RUB' => 'Russian Ruble (₽)',
            'ZAR' => 'South African Rand (R)',
            'TRY' => 'Turkish Lira (₺)',
            'BRL' => 'Brazilian Real (R$)',
            'TWD' => 'Taiwan Dollar (NT$)',
            'DKK' => 'Danish Krone (kr)',
            'PLN' => 'Polish Zloty (zł)',
            'THB' => 'Thai Baht (฿)',
            'IDR' => 'Indonesian Rupiah (Rp)',
            'HUF' => 'Hungarian Forint (Ft)',
            'CZK' => 'Czech Koruna (Kč)',
            'ILS' => 'Israeli Shekel (₪)',
            'CLP' => 'Chilean Peso ($)',
            'PHP' => 'Philippine Peso (₱)',
            'AED' => 'UAE Dirham (د.إ)',
            'COP' => 'Colombian Peso ($)',
            'SAR' => 'Saudi Riyal (﷼)',
            'MYR' => 'Malaysian Ringgit (RM)',
            'RON' => 'Romanian Leu (lei)',
            'NGN' => 'Nigerian Naira (₦)',
            'EGP' => 'Egyptian Pound (£)',
            'VND' => 'Vietnamese Dong (₫)',
            'PKR' => 'Pakistani Rupee (₨)',
            'BDT' => 'Bangladeshi Taka (৳)',
            'KES' => 'Kenyan Shilling (KSh)',
            'GHS' => 'Ghanaian Cedi (₵)',
            'UGX' => 'Ugandan Shilling (USh)',
            'TZS' => 'Tanzanian Shilling (TSh)',
            'XOF' => 'West African CFA Franc (CFA)',
            'XAF' => 'Central African CFA Franc (FCFA)',
            'MAD' => 'Moroccan Dirham (د.م.)',
            'QAR' => 'Qatari Riyal (﷼)',
            'KWD' => 'Kuwaiti Dinar (د.ك)',
            'BHD' => 'Bahraini Dinar (.د.ب)',
            'OMR' => 'Omani Rial (﷼)',
            'JOD' => 'Jordanian Dinar (د.ا)',
            'LKR' => 'Sri Lankan Rupee (₨)',
            'MMK' => 'Myanmar Kyat (K)',
            'NPR' => 'Nepalese Rupee (₨)',
            'ARS' => 'Argentine Peso ($)',
            'PEN' => 'Peruvian Sol (S/)',
            'UAH' => 'Ukrainian Hryvnia (₴)',
            'BGN' => 'Bulgarian Lev (лв)',
            'HRK' => 'Croatian Kuna (kn)',
            'RSD' => 'Serbian Dinar (дин)',
            'ISK' => 'Icelandic Króna (kr)',
        ];
    }

    /**
     * Get currencies formatted for dropdown options
     */
    public static function forDropdown(): array
    {
        $currencies = [];
        foreach (self::all() as $code => $name) {
            $currencies[] = [
                'label' => "{$name}",
                'value' => $code,
            ];
        }

        return $currencies;
    }

    /**
     * Get common currencies (subset for quick selection)
     */
    public static function common(): array
    {
        return [
            'USD' => 'US Dollar ($)',
            'EUR' => 'Euro (€)',
            'GBP' => 'British Pound (£)',
            'NGN' => 'Nigerian Naira (₦)',
            'CAD' => 'Canadian Dollar (C$)',
            'AUD' => 'Australian Dollar (A$)',
            'JPY' => 'Japanese Yen (¥)',
            'CHF' => 'Swiss Franc (CHF)',
            'INR' => 'Indian Rupee (₹)',
            'CNY' => 'Chinese Yuan (¥)',
        ];
    }

    /**
     * Get common currencies formatted for dropdown options
     */
    public static function commonForDropdown(): array
    {
        $currencies = [];
        foreach (self::common() as $code => $name) {
            $currencies[] = [
                'label' => "{$name}",
                'value' => $code,
            ];
        }

        return $currencies;
    }

    /**
     * Get currency symbol by code
     */
    public static function getSymbol(string $code): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'CHF' => 'CHF',
            'CNY' => '¥',
            'INR' => '₹',
            'NGN' => '₦',
            'ZAR' => 'R',
            'BRL' => 'R$',
            'KRW' => '₩',
            'TRY' => '₺',
            'AED' => 'د.إ',
            'SAR' => '﷼',
            'PHP' => '₱',
            'THB' => '฿',
            'VND' => '₫',
            'UAH' => '₴',
            'ILS' => '₪',
            'RUB' => '₽',
            'PKR' => '₨',
            'BDT' => '৳',
            'GHS' => '₵',
        ];

        return $symbols[$code] ?? $code;
    }

    /**
     * Check if currency code is valid
     */
    public static function isValid(string $code): bool
    {
        return array_key_exists($code, self::all());
    }
}
