<?php

/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://propel.phpdb.org>.
 */

require_once 'propel/engine/builder/om/OMBuilder.php';

/**
 * Generates the PHP5 map builder class for user object model (OM).
 *
 * This class replaces the MapBuilder.tpl, with the intent of being easier for users
 * to customize (through extending & overriding).
 *
 * @author     Hans Lellelid <hans@xmpl.org>
 * @package    propel.engine.builder.om.php5
 */
class PHP5MapBuilderBuilder extends OMBuilder {

	/**
	 * Gets the package for the map builder classes.
	 * @return     string
	 */
	public function getPackage()
	{
		return parent::getPackage() . '.map';
	}

	/**
	 * Returns the name of the current class being built.
	 * @return     string
	 */
	public function getUnprefixedClassname()
	{
		return $this->getTable()->getPhpName() . 'MapBuilder';
	}

	/**
	 * Adds the include() statements for files that this class depends on or utilizes.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addIncludes(&$script)
	{
	} // addIncludes()

	/**
	 * Adds class phpdoc comment and openning of class.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addClassOpen(&$script)
	{
		$table = $this->getTable();
		$script .= "

/**
 * This class adds structure of '".$table->getName()."' table to '".$table->getDatabase()->getName()."' DatabaseMap object.
 *
 *";
		if ($this->getBuildProperty('addTimeStamp')) {
			$now = strftime('%c');
			$script .= "
 * This class was autogenerated by Propel " . $this->getBuildProperty('version') . " on:
 *
 * $now
 *";
		}
		$script .= "
 *
 * These statically-built map classes are used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    ".$this->getPackage()."
 */
class ".$this->getClassname()." implements MapBuilder {
";
	}

	/**
	 * Specifies the methods that are added as part of the map builder class.
	 * This can be overridden by subclasses that wish to add more methods.
	 * @see        ObjectBuilder::addClassBody()
	 */
	protected function addClassBody(&$script)
	{
		$this->addConstants($script);
		$this->addAttributes($script);

		$this->addIsBuilt($script);
		$this->addGetDatabaseMap($script);
		$this->addDoBuild($script);
	}

	/**
	 * Adds any constants needed for this MapBuilder class.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addConstants(&$script)
	{
		$script .= "
	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = '".$this->getClasspath()."';
";
	}

	/**
	 * Adds any attributes needed for this MapBuilder class.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addAttributes(&$script)
	{
		$script .= "
	/**
	 * The database map.
	 */
	private \$dbMap;
";
	}

	/**
	 * Closes class.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addClassClose(&$script)
	{
		$script .= "
} // " . $this->getClassname() . "
";
	}

	/**
	 * Adds the method that indicates whether this map has already been built.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addIsBuilt(&$script)
	{
		$script .= "
	/**
	 * Tells us if this DatabaseMapBuilder is built so that we
	 * don't have to re-build it every time.
	 *
	 * @return     boolean true if this DatabaseMapBuilder is built, false otherwise.
	 */
	public function isBuilt()
	{
		return (\$this->dbMap !== null);
	}
";
	}

	/**
	 * Adds the DatabaseMap accessor method.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addGetDatabaseMap(&$script)
	{
		$script .= "
	/**
	 * Gets the databasemap this map builder built.
	 *
	 * @return     the databasemap
	 */
	public function getDatabaseMap()
	{
		return \$this->dbMap;
	}
";
	}

	/**
	 * Adds the main doBuild() method to the map builder class.
	 * @param      string &$script The script will be modified in this method.
	 */
	protected function addDoBuild(&$script)
	{

		$table = $this->getTable();
		$platform = $this->getPlatform();

		$script .= "
	/**
	 * The doBuild() method builds the DatabaseMap
	 *
	 * @return     void
	 * @throws     PropelException
	 */
	public function doBuild()
	{
		\$this->dbMap = Propel::getDatabaseMap(".$this->getPeerClassname()."::DATABASE_NAME);

		\$tMap = \$this->dbMap->addTable(".$this->getPeerClassname()."::TABLE_NAME);
		\$tMap->setPhpName('".$table->getPhpName()."');
		\$tMap->setClassname('" . $this->getObjectClassname() . "');
";
		if ($table->getIdMethod() == "native") {
			$script .= "
		\$tMap->setUseIdGenerator(true);
";
		} else {
			$script .= "
		\$tMap->setUseIdGenerator(false);
";
		}

		if ($table->getIdMethodParameters()) {
			$params = $table->getIdMethodParameters();
			$imp = $params[0];
			$script .= "
		\$tMap->setPrimaryKeyMethodInfo('".$imp->getValue()."');
";
		} elseif ($table->getIdMethod() == "native" && ($platform->getNativeIdMethod() == Platform::SEQUENCE)) {
			$script .= "
		\$tMap->setPrimaryKeyMethodInfo('".$this->getSequenceName()."');
";
		} elseif ($table->getIdMethod() == "native" && ($platform->getNativeIdMethod() == Platform::SEQUENCE)) {
			$script .= "
		\$tMap->setPrimaryKeyMethodInfo('".$table->getName()."');
";
		}

		// Add columns to map
		foreach ($table->getColumns() as $col) {
			$cfc=$col->getPhpName();
			$cup=strtoupper($col->getName());
			if (!$col->getSize()) {
				$size = "null";
			} else {
				$size = $col->getSize();
			}
			if ($col->isPrimaryKey()) {
				if ($col->isForeignKey()) {
					$script .= "
		\$tMap->addForeignPrimaryKey('$cup', '$cfc', '".$col->getType()."' , '".$col->getRelatedTableName()."', '".strtoupper($col->getRelatedColumnName())."', ".($col->isNotNull() ? 'true' : 'false').", ".$size.");
";
				} else {
					$script .= "
		\$tMap->addPrimaryKey('$cup', '$cfc', '".$col->getType()."', ".var_export($col->isNotNull(), true).", ".$size.");
";
				}
			} else {
				if ($col->isForeignKey()) {
					$script .= "
		\$tMap->addForeignKey('$cup', '$cfc', '".$col->getType()."', '".$col->getRelatedTableName()."', '".strtoupper($col->getRelatedColumnName())."', ".($col->isNotNull() ? 'true' : 'false').", ".$size.");
";
			} else {
					$script .= "
		\$tMap->addColumn('$cup', '$cfc', '".$col->getType()."', ".var_export($col->isNotNull(), true).", ".$size.");
";
				}
			} // if col-is prim key
		} // foreach

		foreach ($table->getValidators() as $val) {
			$col = $val->getColumn();
			$cup = strtoupper($col->getName());
			foreach ($val->getRules() as $rule) {
				if ($val->getTranslate() !== Validator::TRANSLATE_NONE) {
					$script .= "
		\$tMap->addValidator('$cup', '".$rule->getName()."', '".$rule->getClass()."', '".$rule->getValue()."', ".$val->getTranslate()."('".str_replace("'", "\'", $rule->getMessage())."'));
";
				} else {
					$script .= "
		\$tMap->addValidator('$cup', '".$rule->getName()."', '".$rule->getClass()."', '".$rule->getValue()."', '".str_replace("'", "\'", $rule->getMessage())."');
";
				} // if ($rule->getTranslation() ...
  			} // foreach rule
		}  // foreach validator

		$script .= "
	} // doBuild()
";

	}

} // PHP5ExtensionPeerBuilder
