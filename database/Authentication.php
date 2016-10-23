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

            $datas[$password] = md5(sha1($datas[$password]));


            if ($this->isValidationUsed()) {


                $find = static::find()
                    ->where($username, $datas[$username]);

                $this->setRules([
                    $username => 'required|digit_min:5|match_db',
                    $password => 'required|digit_min:5|match_db_with:'.$username
                ]);

                $this->setFilters([
                    $username => 'xss|strip_tags',
                    $password => 'xss|strip_tags'
                ]);



                $this->setMessages([
                    'match_db.' . $username => ConfigManager::get('authentication.error_messages.'.$username, 'Wrong Username'),
                    'match_db_with.' . $password =>  ConfigManager::get('authentication.error_messages.'.$username, 'Wrong Password')
                ]);
                if ($this->validate($datas)) {
                    if ($find->exists()) {
                        Identitiy::login($find->one(), $remember);
                        return $find;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                throw new ModuleException('You need to use Validation module');
            }

        } else {
            return false;
        }
    }


}