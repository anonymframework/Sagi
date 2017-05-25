<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/08/2017
 * Time: 01:51
 */

namespace Sagi\Database\Driver\Traits;

use Sagi\Database\ConfigManager;
use Sagi\Database\Exceptions\PolicyException;
use Sagi\Database\Interfaces\PolicyInterface;


trait PolicyCable
{

    /**
     * @var mixed
     */
    protected $policy;

    /**
     * @throws PolicyException
     */
    public function bootPolicyCable()
    {
        if ($this->isCableBooted('Event') === false) {
            throw new PolicyException('You must boot event cable first');
        }

    }

    /**
     * @return mixed|PolicyInterface
     * @throws PolicyException
     */
    public function getPolicy()
    {
        if ( ! $this->policy instanceof PolicyInterface) {
            if ($policy = ConfigManager::get('policies.'.get_called_class())) {
                if ( ! is_string($policy) || ! class_exists($policy)) {
                    throw new PolicyException('Policy does not exists');
                }
            } elseif ( ! empty($this->policy)) {
                $policy = $this->policy;
            }

            $this->policy(new $policy);
        }

        return $this->policy;
    }

    private function preparePolicyFunction()
    {
        return function ($model, $method) {
            if (call_user_func([$model->getPolicy(), $method], $model) !== true) {

                $policyKey = sprintf('policies.messages.%s_%s', $model->getTable(), $method);
                $default = sprintf('You cannot use %s method', $method);
                throw new PolicyException(
                    ConfigManager::get($policyKey, $default)
                );
            }
        };
    }

    public function connectPolicies()
    {
        $this->getEventManager()
            ->listen('before_update', $this->preparePolicyFunction($this, 'update'))
            ->listen('before_save', $this->preparePolicyFunction($this, 'save'))
            ->listen('before_delete', $this->preparePolicyFunction($this, 'delete'))
            ->listen('before_get', $this->preparePolicyFunction($this, 'get'));
    }

    /**
     * @param PolicyInterface $policy
     * @return $this
     */
    public function policy(PolicyInterface $policy)
    {

        $this->policy = $policy;

        return $this;
    }
}
