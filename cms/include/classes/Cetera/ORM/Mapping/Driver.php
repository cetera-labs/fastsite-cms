<?php
namespace Cetera\ORM\Mapping;

use \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use \Doctrine\Common\Persistence\Mapping\ClassMetadata;
use \Doctrine\Common\Inflector\Inflector;
use \Doctrine\DBAL\Schema\AbstractSchemaManager;
use \Doctrine\DBAL\Schema\Table;
use \Doctrine\DBAL\Schema\Column;
use \Doctrine\DBAL\Types\Type;
use \Doctrine\ORM\Mapping\ClassMetadataBuildingContext;
use \Doctrine\ORM\Mapping\ComponentMetadata;
use \Doctrine\ORM\Mapping\ClassMetadataInfo;
use \Doctrine\ORM\Mapping\MappingException;
use \Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use \InvalidArgumentException;

class Driver implements MappingDriver {
    
    /**
     * @var AbstractSchemaManager
     */
    private $_sm;    
    
    private $od;
    
    private $classToTableNames = [
        'Cetera\Entity\Node' => 'dir_structure'
    ];
    
    private $classNamesForTables = [
        'dir_data'      => 'Section',
        'dir_structure' => 'Node'
    ];
    
    /**
     * @var array|null
     */
    private $tables = null;  
    
    /**
     * @param AbstractSchemaManager $schemaManager
     */
    public function __construct(AbstractSchemaManager $schemaManager)
    {
        $this->_sm = $schemaManager;
    }  

    /**
     * Set the namespace for the generated entities.
     *
     * @param string $namespace
     *
     * @return void
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }  
    
    public function generateClasses()
    {
        $path = ENTITY_CLASSES_DIR.'/Cetera/Entity';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        
        foreach(\Cetera\ObjectDefinition::enum() as $od) {
            $className = $this->classNamesForTables[$od->getTable()] ?? Inflector::classify(strtolower($od->getTable()));
            $f = fopen($path.'/'.$className.'.php', 'w');
            if ($className == 'Section') {
                $superClass = 'AbstractSection';
            }
            else {
                $superClass = 'AbstractMaterial';
            }
            fwrite($f,<<<EOF
<?php
namespace Cetera\Entity;

class $className extends $superClass {


EOF
);

            foreach($od->getFields() as $field) {
                fwrite($f,'    public $'.Inflector::camelize($field['name']).';'.PHP_EOL);
            }

            fwrite($f,PHP_EOL.'}'.PHP_EOL);
            fclose($f);
        }
        
        $loader = new \Composer\Autoload\ClassLoader();
        $loader->add('Cetera\Entity', ENTITY_CLASSES_DIR);
        $loader->register();        
    }    
    
    /**
     * Loads the metadata for the specified class into the provided container.
     *
     * @return void
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        $this->getTypes();
        
        if ( ! isset($this->classToTableNames[$className])) {
            throw new \InvalidArgumentException("Unknown class " . $className);
        }    
        
        $tableName = $this->classToTableNames[$className];

        $metadata->name = $className;
        $metadata->table['name'] = $tableName;
        
        if ($tableName == 'dir_structure') {
            
            $builder = new ClassMetadataBuilder($metadata);
            $builder->createField('id', 'integer')->isPrimaryKey()->generatedValue()->build(); 
            $builder->addField('lft', 'integer');
            $builder->addField('rght', 'integer');
            $builder->addField('level', 'integer');

            $builder->createManyToOne('parent', '\\Cetera\\Entity\\Node')->addJoinColumn('parent_id', 'id', true, false, 'CASCADE', null)->inversedBy('children')->build();
            $builder->createOneToMany('children', '\\Cetera\\Entity\\Section')->mappedBy('parent')->build();
            
            $builder->createManyToOne('section', '\\Cetera\\Entity\\Section')->addJoinColumn('data_id', 'id')->inversedBy('nodes')->build();  

            $metadata->setCustomRepositoryClass('Cetera\\ORM\\Repository\\NestedTreeRepository');            
           
        }
        else {
            $this->buildIndexes($metadata);
            $this->buildFieldMappings($metadata);
            $this->buildAssociationMappings($metadata);

			$builder = new ClassMetadataBuilder($metadata);
			
            if ($tableName == 'dir_data') {
                $builder->createOneToMany('nodes', '\\Cetera\\Entity\\Node')->mappedBy('section')->build();  
            }
			else {
				$builder->createManyToOne('section', '\\Cetera\\Entity\\Section')->addJoinColumn('idcat', 'id', true, false, 'CASCADE', null)->inversedBy('materials')->build();
			}
            
        }
        
        //print_r($metadata);
        return $metadata;
    }
    
    /**
     * Gets the names of all mapped classes known to this driver.
     *
     * @return string[] The names of all mapped classes known to this driver.
     */
    public function getAllClassNames() : array
    {
        return [];
    }
    
    /**
     * Sets class name for a objectDefinition.
     *
     * @param string $objectDefinition
     * @param string $className
     *
     * @return void
     */
    public function setClassNameForTable($objectDefinition, $className)
    {
        $this->classNamesForTables[$objectDefinition->getTable()] = $className;
    }    
    
    /**
     * Returns whether the class with the specified name should have its metadata loaded.
     * This is only the case if it is either mapped as an Entity or a MappedSuperclass.
     *
     * @param string $className
     */
    public function isTransient($className) : bool
    {
        return true;
    } 
    
    private function getClassNameForObjectDefinition($od)
    {
        return 'Cetera\\Entity\\' . (
            $this->classNamesForTables[$od->getTable()]
                ?? Inflector::classify(strtolower($od->getTable()))
        );
    }    

    private function getTypes()
    {
        if ($this->od !== null) {
            return;
        }        
        
        $list = \Cetera\ObjectDefinition::enum();
        $generate = 0;
        
        foreach($list as $od) {
            $className = $this->getClassNameForObjectDefinition($od);
            if (!class_exists($className)) {
                $generate = 1;
            }
            $tableName = $od->getTable();
            $this->od[$tableName] = $od;
            $this->tables[$tableName] = $this->_sm->listTableDetails($tableName);
            $this->classToTableNames[$className] = $tableName;
        }
                    
        if ($generate) {
            $this->generateClasses();
        }        
        
        //print_r($this->classToTableNames);
    }
    
    /**
     * Build indexes from a class metadata.
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata
     */
    private function buildIndexes(ClassMetadataInfo $metadata)
    {
        $tableName = $metadata->table['name'];
        $indexes   = $this->tables[$tableName]->getIndexes();

        foreach ($indexes as $index) {
            if ($index->isPrimary()) {
                continue;
            }

            $indexName      = $index->getName();
            $indexColumns   = $index->getColumns();
            $constraintType = $index->isUnique()
                ? 'uniqueConstraints'
                : 'indexes';

            $metadata->table[$constraintType][$indexName]['columns'] = $indexColumns;
        }
    }

    /**
     * Build field mapping from class metadata.
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata
     */
    private function buildFieldMappings(ClassMetadataInfo $metadata)
    {
        $tableName      = $metadata->table['name'];
        $od             = $this->od[$tableName];
        $fields         = $od->getFields();
        $columns        = $this->tables[$tableName]->getColumns();
        $primaryKeys    = ['id'];

        $ids           = [];
        $fieldMappings = [];

        foreach ($columns as $column) {
            
            $colName = $column->getName();
            
            if (isset($fields[$colName]) && is_subclass_of($fields[$colName], 'Cetera\ObjectFieldLinkAbstract')) {
                continue;
            }

            $fieldMapping = $this->buildFieldMapping($tableName, $column);

            if ($primaryKeys && in_array($colName, $primaryKeys)) {
                $fieldMapping['id'] = true;
                $ids[] = $fieldMapping;
            }

            $fieldMappings[] = $fieldMapping;
        }

        // We need to check for the columns here, because we might have associations as id as well.
        if ($ids && count($primaryKeys) == 1) {
            $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_AUTO);
        }

        foreach ($fieldMappings as $fieldMapping) {
            $metadata->mapField($fieldMapping);
        }
    }

    /**
     * Build field mapping from a schema column definition
     *
     * @param string                       $tableName
     * @param \Doctrine\DBAL\Schema\Column $column
     *
     * @return array
     */
    private function buildFieldMapping($tableName, Column $column)
    {
        $fieldMapping = [
            'fieldName'  => $this->getFieldNameForColumn($tableName, $column->getName(), false),
            'columnName' => $column->getName(),
            'type'       => $column->getType()->getName(),
            'nullable'   => ( ! $column->getNotnull()),
        ];

        // Type specific elements
        switch ($fieldMapping['type']) {
            case Type::TARRAY:
            case Type::BLOB:
            case Type::GUID:
            case Type::JSON_ARRAY:
            case Type::OBJECT:
            case Type::SIMPLE_ARRAY:
            case Type::STRING:
            case Type::TEXT:
                $fieldMapping['length'] = $column->getLength();
                $fieldMapping['options']['fixed']  = $column->getFixed();
                break;

            case Type::DECIMAL:
            case Type::FLOAT:
                $fieldMapping['precision'] = $column->getPrecision();
                $fieldMapping['scale']     = $column->getScale();
                break;

            case Type::INTEGER:
            case Type::BIGINT:
            case Type::SMALLINT:
                $fieldMapping['options']['unsigned'] = $column->getUnsigned();
                break;
        }

        // Comment
        if (($comment = $column->getComment()) !== null) {
            $fieldMapping['options']['comment'] = $comment;
        }

        // Default
        if (($default = $column->getDefault()) !== null) {
            $fieldMapping['options']['default'] = $default;
        }

        return $fieldMapping;
    }
    
    /**
     * Build to one (one to one, many to one) association mapping from class metadata.
     *
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata
     */
    private function buildAssociationMappings(ClassMetadataInfo $metadata)
    {
        $tableName   = $metadata->table['name'];      
        $od     = $this->od[$tableName];
        $fields = $od->getFields();

        foreach($fields as $field) {
            if (! is_subclass_of($field, 'Cetera\ObjectFieldLinkAbstract')) {
                continue;
            }
            
            $associationMapping = [];            
            
            if (is_subclass_of($field, 'Cetera\ObjectFieldLinkSetAbstract')) {

                $associationMapping['fieldName'] = $this->getFieldNameForColumn($tableName, $field->name, true);
                $associationMapping['targetEntity'] = $this->getClassNameForObjectDefinition($field->getObjectDefinition());
                
                $associationMapping['inversedBy'] = $this->getFieldNameForColumn($field->getObjectDefinition()->getTable(), 'id', true);
                $associationMapping['joinTable'] = [
                    'name' => $field->getLinkTable(),
                    'joinColumns' => [
                        [
                            'name' => 'id',
                            'referencedColumnName' => 'id',
                        ]
                    ],
                    'inverseJoinColumns' => [
                        [
                            'name' => 'dest',
                            'referencedColumnName' => 'id',
                        ]                    
                    ],
                ]; 

                $metadata->mapManyToMany($associationMapping);
            
            }  
            else {
                       
                $associationMapping['fieldName'] = $this->getFieldNameForColumn($tableName, $field->name, true);
                $associationMapping['targetEntity'] = $this->getClassNameForObjectDefinition($field->getObjectDefinition());

                if (isset($metadata->fieldMappings[$associationMapping['fieldName']])) {
                    $associationMapping['fieldName'] .= '2'; // "foo" => "foo2"
                }

                $associationMapping['joinColumns'][] = [
                    'name'                 => $field->name,
                    'referencedColumnName' => 'id',
                ];
                
                $metadata->mapOneToOne($associationMapping);
                
            }
        }
    }   

    /**
     * Return the mapped field name for a column, if it exists. Otherwise return camelized version.
     *
     * @param string  $tableName
     * @param string  $columnName
     * @param boolean $fk Whether the column is a foreignkey or not.
     *
     * @return string
     */
    private function getFieldNameForColumn($tableName, $columnName, $fk = false)
    {
        if (isset($this->fieldNamesForColumns[$tableName]) && isset($this->fieldNamesForColumns[$tableName][$columnName])) {
            return $this->fieldNamesForColumns[$tableName][$columnName];
        }

        $columnName = strtolower($columnName);

        // Replace _id if it is a foreignkey column
        if ($fk) {
            $columnName = str_replace('_id', '', $columnName);
        }
        return Inflector::camelize($columnName);
    }    

}