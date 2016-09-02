<?php
/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 02.09.2016
 * Time: 14:47
 */

namespace Sagi\Database;


trait Authentication
{

    public function login($datas)
    {
        if ($configs = ConfigManager::get('authentication.login')) {

            $username = $configs[0];
            $password = $configs[1];

            if ($this->isValidationUsed()) {
                $this->setRules([
                    $username => 'required|digit_min:5',
                    $password => 'required|digit_min'
                ]);

                $this->setFilters([
                    $username => 'xss|strip_tags',
                    $password => 'xss|strip_tags'
                ]);

                if ($this->validate($datas)) {

                    $find = static::find($datas);

                    if ($find->exists()) {
                        return $find;
                    } else {
                        return false;
                    }

                }
            } else {
                throw new ModuleException('You need to use Validation module');
            }


        } else {
            return false;
        }
    }

}