<?php  defined('C5_EXECUTE') or die('Access Denied.');

/**
 * An attribute whose value is a money amount. Values are localized based on 
 * the MONEY_AMOUNT_LOCALE package config value.
 */
class MoneyAmountAttributeTypeController extends NumberAttributeTypeController {
	/**
	 * Hilariously, we need a blank method overriding the parent 
	 * implementation just to get form.php to render.
	 */
	public function form() {}
	
	/**
	 * Return the attribute value in a nicely-formatted way.
	 * 
	 * @return string
	 */
	public function getDisplayValue() {
		return self::formatMoneyAmount($this->getValue());
	}
	
	/**
	 * Format a money amount based on the set locale.
	 * 
	 * @return string
	 */
	public static function formatMoneyAmount($moneyAmount) {
		$locale = Loader::package('clov')->config('MONEY_AMOUNT_LOCALE');
		$localeconvHelper = Loader::helper('clov_localeconv', 'clov');
		return $localeconvHelper->moneyFormatByLocale($moneyAmount, $locale);
	}
	
	// TODO: Add a validateForm method.
}
