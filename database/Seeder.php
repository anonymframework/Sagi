<?php
namespace Sagi\Database;


use Symfony\Component\Console\Application;

class Seeder
{

    /**
     * @var string
     */
    protected $seedPath = 'seeds';

    /**
     * @var Application
     */
    protected $application;

    /**
     * Seeder constructor.
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->seedPath = ConfigManager::get('root_path') . $this->seedPath;
        $this->application = $application;
    }

    /**
     * @param null $name
     * @throws SeederException
     */
    public function seed($name = null)
    {
        if (is_string($name)) {
            $file = $this->prepareSeedFile($name);

            include $file;

            $className = MigrationManager::prepareClassName($this->prepareSeedName($name));

            $class = new $className;

            if ($class instanceof SeedManager) {
                $class->setApplication($this->application);
            } else {
                throw new SeederException(sprintf('%s class must have SeedManager parent', $className));
            }

            if (!class_exists($className)) {
                throw new SeederException(sprintf('%s class could not found in %s path', $className, $file));
            }


            if (method_exists($class, "seed")) {
                $class->seed();
            } else {
                throw new SeederException(sprintf("seed method could not found in %s file %s class", $file, $className));
            }

        }
    }


    /**
     * @param $name
     * @return string
     */
    public function prepareSeedName($name)
    {
        return "seed_file_" . $name . ".php";
    }

    /**
     * @param $name
     * @return string
     */
    public function prepareSeedFile($name)
    {
        return $this->seedPath . DIRECTORY_SEPARATOR . $this->prepareSeedName($name);
    }

}