<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/06/2017
 * Time: 21:29
 */

namespace Sagi\Database\Builder\Grammers;


class MysqlGrammer implements GrammerInterface
{

    /**
     * @var array
     */
    private $get = [
        'group' => 'SELECT :select FROM :from :join :where :group :having :order :limit',
        'without_group' => 'SELECT :select FROM :from :join :group :having :where :order :limit',
    ];

    /**
     * @var bool
     */
    private $group = false;

    /**
     * @param $group
     * @return $this
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return string
     */
    public function getReadQuery()
    {
        return $this->group === true ? $this->get['group'] : $this->get['without_group'];
    }


    /**
     * @return string
     */
    public function getInsertQuery()
    {
        return 'INSERT INTO :from :insert';
    }

    /**
     * @return string
     */
    public function getUpdateQuery()
    {
        return 'UPDATE :from SET :update :where';
    }

    /**
     * @return string
     */
    public function getDeleteQuery()
    {
        return 'DELETE FROM :from :where';
    }
}
