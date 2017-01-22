<?php
namespace Sagi\Database;


use Symfony\Component\Console\Output\OutputInterface;

class Seeder
{

    /**
     * @var string
     */
    protected $seedPath = 'seeds';

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Seeder constructor.
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->seedPath = ConfigManager::get('root_path') . $this->seedPath;
        $this->output = $output;
    }

    /**
     * @param null $name
     * @throws SeederException
     */
    public function seed($name = null)
    {
        if (is_string($name)) {
            $file = $this->prepareSeedFile($name);

            if (file_exists($file) === false) {
                throw new SeederException(sprintf('%s file could not found', $file));
            }

            \Composer\Autoload\includeFile($file);

            $className = MigrationManager::prepareClassName($this->prepareSeedName($name));

            $className = "Seeds\\".$className;

            $class = new $className;

            if ($class instanceof SeedManager) {
                $class->setOutput($this->output);
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
        return "seed_file__" . $name;
    }

    /**
     * @param $name
     * @return string
     */
    public function prepareSeedFile($name)
    {
        return $this->seedPath . DIRECTORY_SEPARATOR . $this->prepareSeedName($name).'.php';
    }

    /**
     * @return string
     */
    public function getSeedPath()
    {
        return $this->seedPath;
    }

    /**
     *
     * @param string $seedPath
     * @return Seeder
     */
    public function setSeedPath($seedPath)
    {
        $this->seedPath = $seedPath;
        return $this;
    }

}
