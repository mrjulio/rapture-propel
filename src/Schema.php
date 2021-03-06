<?php

namespace Rapture\Propel;

/**
 * Schema tweaks for PropelORM
 */
class Schema
{
    protected $schema;

    /**
     * Schema constructor.
     *
     * @param string $file XML file to import
     */
    public function __construct($file)
    {
        $this->schema = simplexml_load_file($file);
    }

    /**
     * setDatabaseAttribute
     *
     * Ex: $schema->setDatabaseAttribute('namespace', 'Backend\Domain\Model');
     * Ex: $schema->setDatabaseAttribute('name', 'db_name');
     *
     * @param string $attribute XML attribute name
     * @param string $value     XML attribute value
     *
     * @return $this
     */
    public function setDatabaseAttribute($attribute, $value)
    {
        $this->schema->xpath("/database")[0][$attribute] = $value;

        return $this;
    }

    /**
     * setTableAttribute
     * Ex: Schema::setTableAttribute('user', 'identifierQuoting', true);
     *
     * @param string $table     Table name
     * @param string $attribute XML attribute name
     * @param string $value     XML attribute value
     *
     * @return $this
     */
    public function setTableAttribute($table, $attribute, $value)
    {
        $this->schema->xpath("/database/table[@name='{$table}']")[0][$attribute] = $value;

        return $this;
    }

    /**
     * @param string $table     Table name
     * @param string $column    Column name
     * @param string $refTable  Reference table name
     * @param string $refColumn Reference column name
     * @param null   $phpName   PHP name
     *
     * @return $this
     */
    public function addVirtualRelation($table, $column, $refTable, $refColumn, $phpName = null)
    {
        static $index = 0;
        $fkName = "fk{$index}_propel";

        $count = count($this->schema->xpath("/database/table[@name='{$table}']/foreign-key"));

        $this->schema->xpath("/database/table[@name='{$table}']")[0]->addChild('foreign-key');
        $this->schema->xpath("/database/table[@name='{$table}']/foreign-key")[$count]->addAttribute('foreignTable', $refTable);
        $this->schema->xpath("/database/table[@name='{$table}']/foreign-key")[$count]->addAttribute('name', "{$fkName}");
        $this->schema->xpath("/database/table[@name='{$table}']/foreign-key")[$count]->addChild('reference');
        $this->schema->xpath("/database/table[@name='{$table}']/foreign-key[@name='{$fkName}']/reference")[0]->addAttribute('local', $column);
        $this->schema->xpath("/database/table[@name='{$table}']/foreign-key[@name='{$fkName}']/reference")[0]->addAttribute('foreign', $refColumn);

        if ($phpName) {
            $this->schema->xpath("/database/table[@name='{$table}']/foreign-key[@name='{$fkName}']")[0]->addAttribute('phpName', $phpName);
        }

        $index += 2; // don't ask why

        return $this;
    }

    /**
     * setVirtualCombinedPrimaryKey
     *
     * @param string $table   Table name
     * @param array  $columns Column names
     *
     * @return $this
     */
    public function setVirtualCombinedPrimaryKey($table, array $columns)
    {
        foreach ($columns as $column) {
            $this->schema->xpath("/database/table[@name='{$table}']/column[@name='{$column}']")[0]->addAttribute('primaryKey', 'true');
        }

        return $this;
    }

    /**
     * addBehavior
     *
     * @param string $table    Table name
     * @param string $behavior Behaviour name
     *
     * @return $this
     */
    public function addBehavior($table, $behavior)
    {
        $count = count($this->schema->xpath("/database/table[@name='{$table}']/behavior"));

        $this->schema->xpath("/database/table[@name='{$table}']")[0]->addChild('behavior');
        $this->schema->xpath("/database/table[@name='{$table}']/behavior")[$count]->addAttribute('name', $behavior);

        return $this;
    }

    /**
     * @param string $table      Table name
     * @param string $behavior   Behavior name
     * @param string $paramName  Param name
     * @param mixed  $paramValue Param value
     *
     * @return $this
     */
    public function addBehaviorParameter($table, $behavior, $paramName, $paramValue)
    {
        $count = count($this->schema->xpath("/database/table[@name='{$table}']/behavior[@name='{$behavior}']/parameter"));

        $this->schema->xpath("/database/table[@name='{$table}']/behavior[@name='{$behavior}']")[0]->addChild('parameter');
        $this->schema->xpath("/database/table[@name='{$table}']/behavior[@name='{$behavior}']/parameter")[$count]->addAttribute('name', $paramName);
        $this->schema->xpath("/database/table[@name='{$table}']/behavior[@name='{$behavior}']/parameter")[$count]->addAttribute('value', $paramValue);

        return $this;
    }

    /**
     * @param string $table   Table name
     * @param int    $fkIndex Foreign key index
     * @param string $phpName PHP name
     *
     * @return $this
     */
    public function setRelationAlias($table, $fkIndex, $phpName)
    {
        $this->schema->xpath("/database/table[@name='{$table}']/foreign-key")[$fkIndex]->addAttribute('phpName', $phpName);

        return $this;
    }

    /**
     * getSchema
     *
     * @return \SimpleXMLElement
     */
    public function getSchema()
    {
        return $this->schema;
    }
}
