<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @package   chiemTools
 * @author    Die Chiemseeler (Andi Platen)
 * @copyright Die Chiemseeler (Andi Platen)
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html
 */

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_page']['palettes']['root'] = str_replace('includeLayout;', 'includeLayout;{chiemTools_legend:hide},chiemToolsClassP,chiemToolsClassA,chiemToolsClassC;', $GLOBALS['TL_DCA']['tl_page']['palettes']['root']);

foreach($GLOBALS['TL_DCA']['tl_page']['palettes'] as $key => $row)
{
    if($key == '__selector__') continue;
    if(!stristr($row, 'hide,')) continue;
    $GLOBALS['TL_DCA']['tl_page']['palettes'][$key] = str_replace('hide,', 'hide,chiemToolsUrlEx,', $GLOBALS['TL_DCA']['tl_page']['palettes'][$key]);
}


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['chiemToolsClassP'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_page']['chiemToolsClassP'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'long', 'rgxp' => 'extnd', 'nospace' => true),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_page']['fields']['chiemToolsClassA'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_page']['chiemToolsClassA'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'long', 'rgxp' => 'extnd', 'nospace' => true),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_page']['fields']['chiemToolsClassC'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_page']['chiemToolsClassC'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'long', 'rgxp' => 'extnd', 'nospace' => true),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_page']['fields']['chiemToolsUrlEx'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_page']['chiemToolsUrlEx'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'w50'),
	'sql' => "char(1) NOT NULL default ''"
);

// chiemToolsClassSel //
$GLOBALS['TL_DCA']['tl_page']['config']['onload_callback'][] = array('tl_page_chiemTools','chiemToolsClassSel');

// chiemToolsUrlEx //
unset($GLOBALS['TL_DCA']['tl_page']['fields']['alias']['save_callback'][0]);
$GLOBALS['TL_DCA']['tl_page']['fields']['alias']['save_callback'][] = array('chiemToolsUrlEx', 'generateAlias');

unset($GLOBALS['TL_DCA']['tl_page']['select']['buttons_callback'][0]);
$GLOBALS['TL_DCA']['tl_page']['select']['buttons_callback'][] = array('chiemToolsUrlEx', 'addAliasButton');




// overwrite default css field //
class tl_page_chiemTools extends tl_page
{
	public function chiemToolsClassSel(DataContainer $dc)
	{
		$objPage = $this->getPageDetails($dc->id);
		$objRootPage = $this->getPageDetails($objPage->rootId);

		if(!empty($objRootPage->chiemToolsClassP))
		{
			$arrOptions = explode(',', $objRootPage->chiemToolsClassP);

			$GLOBALS['TL_DCA']['tl_page']['fields']['cssClass']['inputType'] = 'select';
			$GLOBALS['TL_DCA']['tl_page']['fields']['cssClass']['options'] = $arrOptions;
			$GLOBALS['TL_DCA']['tl_page']['fields']['cssClass']['eval'] = array('includeBlankOption' => true, 'chosen' => true, 'multiple'=> true, 'tl_class'=>'w50');
			$GLOBALS['TL_DCA']['tl_page']['fields']['cssClass']['load_callback'][] = array('tl_page_chiemTools', 'loadArray');
			$GLOBALS['TL_DCA']['tl_page']['fields']['cssClass']['save_callback'][] = array('tl_page_chiemTools', 'saveArray');
		}
	}




	public function loadArray($varValue, DataContainer $dc)
	{
		$classArray = explode(' ', $varValue);
		$varValue = serialize($classArray);

		return $varValue;
	}




	public function saveArray($varValue, DataContainer $dc)
	{
		$classArray = unserialize($varValue);
		if($classArray != false)
		{
			$varValue = implode(' ', $classArray);
		}

		return $varValue;
	}
}