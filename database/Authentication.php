<?php

namespace Sagi\Database;


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

        if (Identitiy::isLogined()) {
            return Identitiy::user();
        }

        if ($configs = ConfigManager::get('authentication.login')) {

            $username = $configs[0];
            $password = $configs[1];


            if ($this->isValidationUsed()) {
                $this->setRules([
                    $username => 'required|digit_min:5',
                    $password => 'required|digit_min:5'
                ]);

                $this->setFilters([
                    $username => 'xss|strip_tags',
                    $password => 'xss|strip_tags'
                ]);


                if ($this->validate($datas)) {
                    $datas[$password] = md5(sha1($datas[$password]));
                    $find = static::find()
                        ->where($username, $datas[$username])
                        ->where($password, $datas[$password]);

                    if ($find->exists()) {
                        Identitiy::login($find, $remember);
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


    public function logout()
    {
        if (SessionManager::has('identity')) {
            SessionManager::delete('identity');
        }
    }


}