<?php

namespace MondayV2SDK\ColumnTypes;

/**
 * Number column type for Monday.com
 *
 * Handles number columns with validation and formatting.
 */
class NumberColumn extends AbstractColumnType
{
    private float $number;
    private ?string $format;

    /**
     * Constructor
     *
     * @param string      $columnId The column ID
     * @param float|int   $number   The number value
     * @param string|null $format   The number format (optional)
     */
    public function __construct(string $columnId, float|int $number, ?string $format = null)
    {
        $this->number = (float) $number;
        $this->format = $format;

        parent::__construct($columnId, $this->number);
    }

    /**
     * Get the column type identifier
     *
     * @return string
     */
    public function getType(): string
    {
        return 'number';
    }

    /**
     * Validate the number value
     *
     * @throws \InvalidArgumentException
     */
    public function validate(): void
    {
        parent::validate();

        if ($this->format && !$this->isValidFormat($this->format)) {
            throw new \InvalidArgumentException("Invalid number format: {$this->format}");
        }
    }

    /**
     * Get the number value
     *
     * @return float
     */
    public function getValue(): float
    {
        return $this->number;
    }

    /**
     * Get the column value in Monday.com API format
     *
     * @return array<string, mixed>
     */
    public function getApiValue(): array
    {
        $value = ['number' => $this->number];

        if ($this->format) {
            $value['format'] = $this->format;
        }

        return $value;
    }

    /**
     * Create a number column with formatting
     *
     * @param  string    $columnId The column ID
     * @param  float|int $number   The number value
     * @param  string    $format   The number format
     * @return self
     */
    public static function withFormat(string $columnId, $number, string $format): self
    {
        return new self($columnId, $number, $format);
    }

    /**
     * Create a number column for currency
     *
     * @param  string    $columnId The column ID
     * @param  float|int $amount   The amount
     * @param  string    $currency The currency code (e.g., 'USD', 'EUR')
     * @return self
     */
    public static function currency(string $columnId, $amount, string $currency = 'USD'): self
    {
        return new self($columnId, $amount, "currency_{$currency}");
    }

    /**
     * Create a number column for percentage
     *
     * @param  string    $columnId   The column ID
     * @param  float|int $percentage The percentage value
     * @return self
     */
    public static function percentage(string $columnId, $percentage): self
    {
        return new self($columnId, $percentage, 'percentage');
    }

    /**
     * Create a number column for time duration
     *
     * @param  string    $columnId The column ID
     * @param  float|int $duration The duration in minutes
     * @return self
     */
    public static function duration(string $columnId, $duration): self
    {
        return new self($columnId, $duration, 'duration');
    }

    /**
     * Create an empty number column
     *
     * @param  string $columnId The column ID
     * @return self
     */
    public static function empty(string $columnId): self
    {
        return new self($columnId, 0);
    }

    /**
     * Get the number value
     *
     * @return float
     */
    public function getNumber(): float
    {
        return $this->number;
    }

    /**
     * Get the number format
     *
     * @return string|null
     */
    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * Validate number format
     *
     * @param  string $format
     * @return bool
     */
    private function isValidFormat(string $format): bool
    {
        $validFormats = [
            'number',
            'percentage',
            'duration',
            'currency_USD',
            'currency_EUR',
            'currency_GBP',
            'currency_JPY',
            'currency_CAD',
            'currency_AUD',
            'currency_CHF',
            'currency_CNY',
            'currency_INR',
            'currency_BRL',
            'currency_MXN',
            'currency_KRW',
            'currency_RUB',
            'currency_ZAR',
            'currency_SEK',
            'currency_NOK',
            'currency_DKK',
            'currency_PLN',
            'currency_CZK',
            'currency_HUF',
            'currency_ILS',
            'currency_SGD',
            'currency_HKD',
            'currency_NZD',
            'currency_THB',
            'currency_MYR',
            'currency_IDR',
            'currency_PHP',
            'currency_VND',
            'currency_TRY',
            'currency_ARS',
            'currency_CLP',
            'currency_COP',
            'currency_PEN',
            'currency_UYU',
            'currency_BOB',
            'currency_PYG',
            'currency_GUY',
            'currency_SRD',
            'currency_BBD',
            'currency_JMD',
            'currency_TTD',
            'currency_HTG',
            'currency_DOP',
            'currency_ANG',
            'currency_XCD',
            'currency_AWG',
            'currency_KYD',
            'currency_BMD',
            'currency_FJD',
            'currency_WST',
            'currency_TOP',
            'currency_SBD',
            'currency_VUV',
            'currency_PGK',
            'currency_KPW',
            'currency_LAK',
            'currency_KHR',
            'currency_MMK',
            'currency_BDT',
            'currency_NPR',
            'currency_LKR',
            'currency_MVR',
            'currency_BTN',
            'currency_AFN',
            'currency_TJS',
            'currency_TMM',
            'currency_UZS',
            'currency_KGS',
            'currency_MNT',
            'currency_KZT',
            'currency_AZN',
            'currency_GEL',
            'currency_AMD',
            'currency_IRR',
            'currency_IQD',
            'currency_SYP',
            'currency_LBP',
            'currency_JOD',
            'currency_OMR',
            'currency_QAR',
            'currency_AED',
            'currency_YER',
            'currency_KWD',
            'currency_BHD',
            'currency_EGP',
            'currency_LYD',
            'currency_TND',
            'currency_DZD',
            'currency_MAD',
            'currency_MRO',
            'currency_MUR',
            'currency_SCR',
            'currency_KES',
            'currency_TZS',
            'currency_UGX',
            'currency_BIF',
            'currency_RWF',
            'currency_DJF',
            'currency_ETB',
            'currency_SOS',
            'currency_SDD',
            'currency_ERN',
            'currency_DJF',
            'currency_KMF',
            'currency_MGA',
            'currency_MWK',
            'currency_ZMW',
            'currency_NAD',
            'currency_BWP',
            'currency_LSL',
            'currency_SZL',
            'currency_CVE',
            'currency_STD',
            'currency_GMD',
            'currency_GNF',
            'currency_SLL',
            'currency_LRD',
            'currency_CDF',
            'currency_AOA',
            'currency_GHS',
            'currency_NGN',
            'currency_XAF',
            'currency_XOF',
            'currency_XPF',
            'currency_CDF',
            'currency_GMD',
            'currency_GNF',
            'currency_SLL',
            'currency_LRD',
            'currency_CDF',
            'currency_AOA',
            'currency_GHS',
            'currency_NGN',
            'currency_XAF',
            'currency_XOF',
            'currency_XPF'
        ];

        return in_array($format, $validFormats);
    }
}
