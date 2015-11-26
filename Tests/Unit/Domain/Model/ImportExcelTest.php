<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Andrea Schmuttermair 
 *  			
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Test case for class Tx_ImportExcel_Domain_Model_ImportExcel.
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @package TYPO3
 * @subpackage Import Excel files to database
 *
 * @author Andrea Schmuttermair 
 */
class Tx_ImportExcel_Domain_Model_ImportExcelTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var Tx_ImportExcel_Domain_Model_ImportExcel
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new Tx_ImportExcel_Domain_Model_ImportExcel();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getTitleReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setTitleForStringSetsTitle() { 
		$this->fixture->setTitle('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getTitle()
		);
	}
	
	/**
	 * @test
	 */
	public function getDescriptionReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setDescriptionForStringSetsDescription() { 
		$this->fixture->setDescription('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getDescription()
		);
	}
	
	/**
	 * @test
	 */
	public function getFieldIntReturnsInitialValueForInteger() { 
		$this->assertSame(
			0,
			$this->fixture->getFieldInt()
		);
	}

	/**
	 * @test
	 */
	public function setFieldIntForIntegerSetsFieldInt() { 
		$this->fixture->setFieldInt(12);

		$this->assertSame(
			12,
			$this->fixture->getFieldInt()
		);
	}
	
}
?>