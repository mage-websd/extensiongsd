<?php 
/**
 ::[Header]::
 */



/**
 * This interface represents a string which can be localized.
 * 
 * 
 * @author Thomas Hunziker
 *
 */
interface Customweb_I18n_ILocalizableString {
	
	/**
	 * Returns the untranslated string. Which can be used as the key for the translation.
	 * 
	 * @return string
	 */
	public function getUntranslatedString();
	
	/**
	 * 
	 * @return array
	 */
	public function getArguments();
	
	/**
	 * Returns the translated string.
	 * 
	 * @return Translated String
	 */
	public function __toString();
	
}