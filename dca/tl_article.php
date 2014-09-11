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
 * Fields
 */
$GLOBALS['TL_DCA']['tl_article']['config']['onload_callback'][] = array('tl_article_chiemTools','chiemToolsClassSel');
$GLOBALS['TL_DCA']['tl_article']['config']['onsubmit_callback'][] = array('tl_article_chiemTools','chiemToolsSave');


// overwrite default css field //
class tl_article_chiemTools extends tl_article
{
	public function chiemToolsClassSel(DataContainer $dc)
	{
		$parentPage = $this->Database->prepare("SELECT pid FROM tl_article WHERE id=?")->limit(1)->execute($dc->id);
		$parentPage = $parentPage->row();
		$parentPage = $parentPage['pid'];
		
		$objPage = $this->getPageDetails($parentPage);
		$objRootPage = $this->getPageDetails($objPage->rootId);
		
		if(!empty($objRootPage->chiemToolsClassA))
		{
			$this->useChiemTools = 1;
			$arrOptions = explode(',', $objRootPage->chiemToolsClassA);

			foreach($GLOBALS['TL_DCA']['tl_article']['palettes'] as $key => $row)
		    {
    			if($key == '__selector__') continue;
    			if($key == 'html') continue;
    			if($key == 'accordion') continue;
    			if($key == 'accordionmooStop') continue;
    			if($key == 'article') continue;
   		 		$GLOBALS['TL_DCA']['tl_article']['palettes'][$key] = str_replace('cssID,', 'chiemToolsID,chiemToolsClassSelC,', $GLOBALS['TL_DCA']['tl_article']['palettes'][$key]);
			}

			$GLOBALS['TL_DCA']['tl_article']['fields']['chiemToolsID']['label'] = &$GLOBALS['TL_LANG']['tl_article']['chiemToolsCssID'];
			$GLOBALS['TL_DCA']['tl_article']['fields']['chiemToolsID']['inputType'] = 'text';
			//$GLOBALS['TL_DCA']['tl_article']['fields']['chiemToolsID']['exclude'] = true;
			$GLOBALS['TL_DCA']['tl_article']['fields']['chiemToolsID']['eval'] = array('doNotSaveEmpty' => true, 'tl_class'=>'w50 clr');
			$GLOBALS['TL_DCA']['tl_article']['fields']['chiemToolsID']['load_callback'][] = array('tl_article_chiemTools', 'loadCssID');
			$GLOBALS['TL_DCA']['tl_article']['fields']['chiemToolsID']['save_callback'][] = array('tl_article_chiemTools', 'saveCssID');

			$GLOBALS['TL_DCA']['tl_article']['fields']['chiemToolsClassSelC']['label'] = &$GLOBALS['TL_LANG']['tl_article']['chiemToolsCssClass'];
			$GLOBALS['TL_DCA']['tl_article']['fields']['chiemToolsClassSelC']['inputType'] = 'select';
			//$GLOBALS['TL_DCA']['tl_article']['fields']['chiemToolsClassSelC']['exclude'] = true;
			$GLOBALS['TL_DCA']['tl_article']['fields']['chiemToolsClassSelC']['options'] = $arrOptions;
			$GLOBALS['TL_DCA']['tl_article']['fields']['chiemToolsClassSelC']['eval'] = array('includeBlankOption' => true, 'chosen' => true, 'multiple' => true, 'doNotSaveEmpty' => true, 'tl_class'=>'w50');
			$GLOBALS['TL_DCA']['tl_article']['fields']['chiemToolsClassSelC']['load_callback'][] = array('tl_article_chiemTools', 'loadArray');
			$GLOBALS['TL_DCA']['tl_article']['fields']['chiemToolsClassSelC']['save_callback'][] = array('tl_article_chiemTools', 'saveArray');
		}
	}





	public function chiemToolsSave(DataContainer $dc)
	{
		if($this->useChiemTools == 1)
		{
			$cssIDArray = array();
			$cssIDArray[0] = $dc->activeRecord->chiemToolsID;
			$cssIDArray[1] = $dc->activeRecord->chiemToolsClassSelC;
			$cssIDArray = serialize($cssIDArray);

			$this->Database->prepare("UPDATE tl_article SET cssID=? WHERE id=?")->execute($cssIDArray, $dc->id);
		}
	}




	public function loadCssID($varValue, DataContainer $dc)
	{
		if(!empty($dc->activeRecord->cssID))
		{
			$arr = unserialize($dc->activeRecord->cssID);
			$varValue = $arr[0];
		}

		return $varValue;
	}


	public function saveCssID($varValue, DataContainer $dc)
	{
		$dc->activeRecord->chiemToolsID = $varValue;
		$varValue = '';

		return $varValue;
	}




	public function loadArray($varValue, DataContainer $dc)
	{
		if(!empty($dc->activeRecord->cssID))
		{
			$arr = unserialize($dc->activeRecord->cssID);
			$varValue = $arr[1];
		}

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

		$dc->activeRecord->chiemToolsClassSelC = $varValue;
		$varValue = '';

		return $varValue;
	}
}