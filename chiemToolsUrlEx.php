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

class chiemToolsUrlEx extends Backend
{
	/**
	 * Auto-generate a page alias if it has not been set yet
	 * @param mixed
	 * @param \DataContainer
	 * @return string
	 * @throws \Exception
	 */
	public function generateAlias($varValue, DataContainer $dc)
	{
		$autoAlias = false;

		// Generate an alias if there is none
		if($varValue == '')
		{
			$autoAlias = true;
			$varValue = standardize(String::restoreBasicEntities($dc->activeRecord->title));

			// Generate folder URL aliases (see #4933)
			if($GLOBALS['TL_CONFIG']['folderUrl'])
			{
				$dc->activeRecord->alias = $varValue;
				$objPage = $this->getPageDetails($dc->activeRecord);

				// Update the folderURL entry
				if($objPage->parentAlias != '' && $objPage->pid != $objPage->rootId)
				{
					$objPage->folderUrl = $objPage->parentAlias . '/' . $varValue;
				}
				else
				{
					$objPage->folderUrl = $varValue;
				}
				$objPage->folderUrl = $this->checkParentAlias($objPage->folderUrl, $objPage); // Check parent alias to exclude from url

				if($objPage->folderUrl != '')
				{
					$varValue = $objPage->folderUrl;
				}
				$varValue = $this->checkParentAlias($varValue, $objPage); // Check parent alias to exclude from url
			}
		}

		$objAlias = $this->Database->prepare("SELECT * FROM tl_page WHERE id=? OR alias=?")
								   ->execute($dc->id, $varValue);

		// Check whether the page alias exists
		if($objAlias->numRows > ($autoAlias ? 0 : 1))
		{
			$arrPages = array();
			$strDomain = '';
			$strLanguage = '';

			while($objAlias->next())
			{
				$objCurrentPage = $this->getPageDetails($objAlias);
				$domain = $objCurrentPage->domain ?: '*';
				$language = (!$objCurrentPage->rootIsFallback) ? $objCurrentPage->rootLanguage : '*';

				// Store the current page's data
				if($objCurrentPage->id == $dc->id)
				{
					// Get the DNS and language settings from the POST data (see #4610)
					if($objCurrentPage->type == 'root')
					{
						$strDomain = Input::post('dns');
						$strLanguage = Input::post('language');
					}
					else
					{
						$strDomain = $domain;
						$strLanguage = $language;
					}
				}
				else
				{
					// Check the domain and language or the domain only
					if($GLOBALS['TL_CONFIG']['addLanguageToUrl'])
					{
						$arrPages[$domain][$language][] = $objAlias->id;
					}
					else
					{
						$arrPages[$domain][] = $objAlias->id;
					}
				}
			}

			$arrCheck = $GLOBALS['TL_CONFIG']['addLanguageToUrl'] ? $arrPages[$strDomain][$strLanguage] : $arrPages[$strDomain];

			// Check if there are multiple results for the current domain
			if(!empty($arrCheck))
			{
				if($autoAlias)
				{
					$varValue .= '-' . $dc->id;
				}
				else
				{
					throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
				}
			}
		}

		return $varValue;
	}




	/**
	 * Automatically generate the folder URL aliases
	 * @param array
 	 * @return array
	 */
	public function addAliasButton($arrButtons)
	{
		if (Input::post('FORM_SUBMIT') == 'tl_select' && isset($_POST['alias']))
		{
			$session = $this->Session->getData();
			$ids = $session['CURRENT']['IDS'];

			foreach ($ids as $id)
			{
				$objPage = PageModel::findWithDetails($id);

				if ($objPage === null)
				{
					continue;
				}

				// Set the new alias
				$strAlias = standardize(String::restoreBasicEntities($objPage->title));

				// Prepend the folder URL
				if ($GLOBALS['TL_CONFIG']['folderUrl'])
				{
					$strAlias = $objPage->folderUrl . $strAlias;
				}
				$strAlias = $this->checkParentAlias($strAlias, $objPage); // Check parent alias to exclude from url

				// The alias has not changed
				if ($strAlias == $objPage->alias)
				{
					continue;
				}

				// Initialize the version manager
				$objVersions = new Versions('tl_page', $id);
				$objVersions->initialize();

				// Store the new alias
				$this->Database->prepare("UPDATE tl_page SET alias=? WHERE id=?")
							   ->execute($strAlias, $id);

				// Create a new version
				$objVersions->create();
			}

			$this->redirect($this->getReferer());
		}
		
		$arrButtons['alias'] = '<input type="submit" name="alias" id="alias" class="tl_submit" accesskey="a" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['aliasSelected']).'"> ';
		
		return $arrButtons;
	}



	// parent alias checker
	protected function checkParentAlias($varValue, $objPage)
	{
		$arrPages = array();
		$arrExclude = array();
		$intPageID = $objPage->id;
		$newFolderUrl = $varValue;
		do
        {
            $objPages = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")
                            ->limit(1)
                            ->execute($intPageID);

            $strType = $objPages->type;
            $intPageID = $objPages->pid;
            $arrPages[] = $objPages->row();
            if($objPages->chiemToolsUrlEx == 1)
            {
            	$arrExclude[] = $objPages->alias;
            }
        }
        while($intPageID > 0 && $strType != 'root' && $objPages->numRows);

        if($strType == 'root')
        {
            array_pop($arrPages);
        }

        foreach($arrExclude as $key => $value)
        {
        	$newFolderUrl = str_replace($value . '/', '', $newFolderUrl);
        }

		return $newFolderUrl;
	}
}

