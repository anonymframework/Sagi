<?php

namespace Sagi\Database;

use Sagi\Database\SesssionManager;

trait Authentication
{

    /**
     * @param $datas
     * @param bool $remember
     * @return bool
     * @throws ModuleException
     */
    public function login($datas, $remember = false)
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

                    $datas[$password] = md5(sha1($datas[$password]));

                    $find = static::find($datas);

                    if ($find->exists()) {

                        if ($remember === true) {
                            CookieManager::set('identity', $find, 7200);
                        } else {
                            SesssionManager::set('identity', $find);
                        }


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