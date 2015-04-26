<?php  defined('C5_EXECUTE') or die('Access Denied.');

$name = $controller->field('value');
$value = $controller->getValue();

// Determine the input precision based on the locale.
$clov = Package::getByHandle('clov');
$locale = $clov->config('MONEY_AMOUNT_LOCALE');
$localeconvHelper = Loader::helper('clov_localeconv', 'clov');
// TODO: If international formatting becomes an option, this will need to 
// use 'int_frac_digits' when appropriate.
$fractionalDigits = $localeconvHelper->locales[$locale]['frac_digits'];
$step = pow(0.1, $fractionalDigits); // e.g. 0.01 for $fractionalDigits == "2"

// Format the input as if it were a money amount (with currency sign, etc).
$input = '<input name="'.$name.'" value="'.$value.'" type="number" step="'.$step.'" />';
echo $localeconvHelper->moneyFormatByLocale($input, $locale);
