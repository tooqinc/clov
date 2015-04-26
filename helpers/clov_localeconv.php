<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Helper functions for locale-based formatting.
 */
class ClovLocaleconvHelper {
	const MONETARY_SEPARATE_SYMBOL_FROM_QUANTITY = 1;
	const MONETARY_SEPARATE_SIGN_FROM_SYMBOL = 2;
	
	const MONETARY_ENCLOSE_PARENTHESES = 0;
	const MONETARY_SIGN_BEFORE_EVERYTHING = 1;
	const MONETARY_SIGN_AFTER_EVERYTHING = 2;
	const MONETARY_SIGN_BEFORE_SYMBOL = 3;
	const MONETARY_SIGN_AFTER_SYMBOL = 4;
	
	/**
	 * Will contain an array of locale identifier => localeconv array.
	 * See http://php.net/localeconv for details about the array format.
	 */
	public $locales = array();
	
	/**
	 * Load the locale file.
	 */
	public function __construct() {
		$clov = Package::getByHandle('clov');
		$this->locales = require($clov->config('LOCALECONV_FILE'));
	}
	
	/**
	 * Return a format array for a locale. If a format array is passed in, 
	 * it is returned as-is.
	 * 
	 * @return array
	 */
	private function getFormatFromLocaleConv($localeOrFormat) {
		// Allow passing in a format array or a locale identifier.
		if(is_array($localeOrFormat)) {
			return $localeOrFormat;
		} else {
			return $this->locales[$localeOrFormat];
		}
	}
	
	/**
	 * Format a money amount according to locale in a way that is similar to 
	 * money_format('%n', $number) or money_format('%i', $number). If this is 
	 * passed something that is non-numeric, it will still be formatted as 
	 * much as possible (including a currency sign, etc), so it might return 
	 * something like "$foo" or "foo €". Non-numeric values are treated like 
	 * positive numbers in terms of format rules.
	 * 
	 * @return string
	 */
	// FIXME: Since this can accept non-numbers, some of these variable names 
	// are misleading.
	public function moneyFormatByLocale($number, $localeOrFormat, $international = false) {
		$format = self::getFormatFromLocaleConv($localeOrFormat);
		
		if($international) {
			$symbol = $format['int_curr_symbol'];
			$fractionalDigits = $format['int_frac_digits'];
		} else {
			$symbol = $format['currency_symbol'];
			$fractionalDigits = $format['frac_digits'];
		}
		
		// Some symbols have whitespace after them.
		// FIXME: This causes some locales to return international formatting 
		// inconsistent with money_format('%i', ...), however removing it 
		// causes many other locales to have incorrect formatting. This is the 
		// lesser of two evils and the one that seems more adherent to the 
		// spec/documentation.
		$symbol = rtrim($symbol);
		
		// If $number isn't numeric, always use positive formatting.
		if(is_numeric($number) && $number < 0) {
			$sign = $format['negative_sign'];
			$signPosition = $format['n_sign_posn'];
			$separatedBySpace = $format['n_sep_by_space'];
			$symbolPrecedes = $format['n_cs_precedes'];
		} else {
			$sign = $format['positive_sign'];
			$signPosition = $format['p_sign_posn'];
			$separatedBySpace = $format['p_sep_by_space'];
			$symbolPrecedes = $format['p_cs_precedes'];
		}
		
		// Determine spacing.
		$betweenSymbolAndQuantity = '';
		$betweenSignAndSymbol = '';
		if($separatedBySpace == self::MONETARY_SEPARATE_SYMBOL_FROM_QUANTITY) {
			$betweenSymbolAndQuantity = ' ';
		} else if($separatedBySpace == self::MONETARY_SEPARATE_SIGN_FROM_SYMBOL) {
			$betweenSignAndSymbol = ' ';
		}
		
		if(is_numeric($number)) {
			// Format the number part.
			$formattedAbsoluteValue = sprintf('%0.'.$fractionalDigits.'F', abs($number));
			$formattedAbsoluteValue = self::numberFormatByLocale($formattedAbsoluteValue, array(
				'decimal_point' => $format['mon_decimal_point'],
				'thousands_sep' => $format['mon_thousands_sep'],
				'grouping' => $format['mon_grouping'],
			));
		} else {
			// Just use the input as-is if it's not numeric.
			$formattedAbsoluteValue = $number;
		}
		
		if($symbolPrecedes) {
			switch($signPosition) {
				case self::MONETARY_ENCLOSE_PARENTHESES:
					$formattedAmount = '('.$symbol.$betweenSymbolAndQuantity.$formattedAbsoluteValue.')';
					break;
				case self::MONETARY_SIGN_BEFORE_EVERYTHING:
					$formattedAmount = $symbol.$betweenSymbolAndQuantity.$formattedAbsoluteValue;
					if($sign) {
						$formattedAmount = $sign.$betweenSignAndSymbol.$formattedAmount;
					}
					break;
				case self::MONETARY_SIGN_AFTER_EVERYTHING:
					$formattedAmount = $symbol.$betweenSymbolAndQuantity.$formattedAbsoluteValue;
					if($sign) {
						$formattedAmount .= $betweenSignAndSymbol.$sign;
					}
					break;
				case self::MONETARY_SIGN_BEFORE_SYMBOL:
					// FIXME: This is the same as 
					// MONETARY_SIGN_BEFORE_EVERYTHING; should it be?
					$formattedAmount = $symbol.$betweenSymbolAndQuantity.$formattedAbsoluteValue;
					if($sign) {
						$formattedAmount = $sign.$betweenSignAndSymbol.$formattedAmount;
					}
					break;
				case self::MONETARY_SIGN_AFTER_SYMBOL:
					$formattedAmount = $symbol.$betweenSignAndSymbol.$sign.$betweenSymbolAndQuantity.$formattedAbsoluteValue;
					break;
			}
		} else {
			switch($signPosition) {
				case self::MONETARY_ENCLOSE_PARENTHESES:
					$formattedAmount = '('.$formattedAbsoluteValue.$betweenSymbolAndQuantity.$symbol.')';
					break;
				case self::MONETARY_SIGN_BEFORE_EVERYTHING:
					$formattedAmount = $sign.$formattedAbsoluteValue.$betweenSymbolAndQuantity.$symbol;
					break;
				case self::MONETARY_SIGN_AFTER_EVERYTHING:
					$formattedAmount = $formattedAbsoluteValue.$betweenSymbolAndQuantity.$symbol;
					if($sign) {
						$formattedAmount .= $betweenSignAndSymbol.$sign;
					}
					break;
				case self::MONETARY_SIGN_BEFORE_SYMBOL:
					$formattedAmount = $formattedAbsoluteValue.$betweenSymbolAndQuantity.$sign.$betweenSignAndSymbol.$symbol;
					break;
				case self::MONETARY_SIGN_AFTER_SYMBOL:
					$formattedAmount = $formattedAbsoluteValue.$betweenSymbolAndQuantity.$symbol;
					if($sign) {
						$formattedAmount .= $betweenSignAndSymbol.$sign;
					}
					break;
			}
		}
		
		return $formattedAmount;
	}
	
	/**
	 * Format a number according to locale. Will preserve the post-decimal 
	 * part if passed a string like '1.00'.
	 * 
	 * @return string
	 */
	public function numberFormatByLocale($number, $localeOrFormat) {
		$format = self::getFormatFromLocaleConv($localeOrFormat);
		
		// Chunk the number into whole digits and decimal digits. 
		// Notice that these will always be positive.
		$splitNumber = explode('.', $number);
		$wholeDigits = abs($splitNumber[0]);
		$fractionalDigits = isset($splitNumber[1]) ? $splitNumber[1] : null;
		
		// See http://php.net/localeconv for information about how grouping is 
		// specified.
		$wholeDigitGroups = array();
		$remainingWholeDigits = $wholeDigits;
		$groupLengths = $format['grouping'];
		$previousLength = CHAR_MAX;
		while(!empty($remainingWholeDigits)) {
			$length = array_shift($groupLengths);
			// "If an array element is equal to 0, the previous element 
			// should be used."
			if($length == 0) {
				$length = $previousLength;
			}
			
			// "If an array element is equal to CHAR_MAX, no further 
			// grouping is done."
			if($length == CHAR_MAX) {
				// Plop in the remaining digits as-is.
				array_unshift($wholeDigitGroups, $remainingWholeDigits);
				break;
			}
			
			// If we ran out of group lengths, keep using the last one 
			// specified.
			if(empty($groupLengths)) {
				$groupLengths = array($length);
			}
			
			// Add the next group (the last $length digits of 
			// $remainingWholeDigits) to the beginning of the array.
			array_unshift($wholeDigitGroups, substr($remainingWholeDigits, 0 - $length));
			$digitsLeft = strlen($remainingWholeDigits) - $length;
			if($digitsLeft > 0) {
				$remainingWholeDigits = substr($remainingWholeDigits, 0, $digitsLeft);
			} else {
				$remainingWholeDigits = '';
			}
			
			$previousLength = $length;
		}
		
		// Glue all of the pieces back together.
		$wholePart = implode($format['thousands_sep'], $wholeDigitGroups) ?: '0';
		$decimalPart = isset($fractionalDigits) ? $format['decimal_point'].$fractionalDigits : '';
		return ($number < 0 ? '-'.$wholePart.$decimalPart : $wholePart.$decimalPart);
	}
}
