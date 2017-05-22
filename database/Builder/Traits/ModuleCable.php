<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/08/2017
 * Time: 13:27
 */

namespace Sagi\Database\Builder\Traits;


use Sagi\Database\Model;

trait ModuleCable
{


    /**
     * @var array
     */
    protected $usedModules = [];

    /**
     * @var array
     */
    protected $bootedModules = [];


    protected function getUsedModules(){
         $traits = class_uses(static::className());


        $modelModules = class_uses(Model::className());
        array_shift($modelModules);

        return $this->usedModules = $traits = array_merge($modelModules, $traits);
    }
    /**
     *
     */
    protected function bootTraits()
    {
        $traits = $this->getUsedModules();

        foreach ($traits as $trait) {
            if (method_exists($this, $method = 'boot' . $this->classBaseName($trait))) {

                $this->bootedModules[] = $trait;
                call_user_func_array([$this, $method], []);
            }
        }
    }

    /**
     * @param $name
     * @return bool
     */
    protected function isCableBooted($name){
        $name = __NAMESPACE__.'\\'.$name.'Cable';


        return in_array($name, $this->bootedModules);
    }

    /**
     * @param string $class
     * @return string
     */
    private function classBaseName($class)
    {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }

    /**
     * @return bool
     */
    public function isValidationUsed()
    {
        return $this->isModuleUsed('Sagi\Database\Validation');
    }

    /**
     * @return bool
     */
    public function isAuthorizationUsed()
    {
        return $this->isModuleUsed('Sagi\Database\Authorization');
    }

    /**
     * @return bool
     */
    public function isCacheUsed()
    {
        return $this->isModuleUsed('Sagi\Database\Cache');
    }

    /**
     * @param string $module
     * @return bool
     */
    public function isModuleUsed($module)
    {
        return in_array($module, $this->usedModules);
    }
}