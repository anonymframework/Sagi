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
                    $password => 'required|digit_min:5|match_db'
                ]);

                $this->setFilters([
                    $username => 'xss|strip_tags',
                    $password => 'xss|strip_tags'
                ]);

                $this->setMessages([
                    'match_db.' . $username => 'Kullanıcı Adınızı Yanlış Girdiniz',
                    'match_db.' . $password => 'Şifrenizi Yanlış Girdiniz'
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