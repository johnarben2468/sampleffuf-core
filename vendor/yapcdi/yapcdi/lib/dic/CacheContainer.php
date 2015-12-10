<?php

namespace yapcdi\dic;

use Doctrine\Common\Cache\Cache;
use yapcdi\resolver\ResolverInterface;

/**
 * Description of DICacheContainer
 *
 * @author Alexander Schlegel
 */
class CacheContainer extends Container {

    /**
     *
     * @var Cache 
     */
    private $cacheProvider;

    public function __construct(ResolverInterface $resolver, Cache $provider) {
        parent::__construct($resolver);
        $this->cacheProvider = $provider;
    }

    protected function getClassInfoFromCache(\ReflectionClass $refClass) {
        $id = $refClass->getName() . '#info';
        if (($data = $this->cacheProvider->fetch($id)) !== false) {
            return $data;
        }
        $data = $this->resolver->getClassDependencies($refClass);
        $this->cacheProvider->save($id, $data);
        return $data;
    }

    public function reset($class = null) {
        if ($class === null) {
            if ($this->cacheProvider instanceof \Doctrine\Common\Cache\FlushableCache) {
                $this->cacheProvider->flushAll();
            }
            return parent::reset($class);
        }
        $id = $class . '#info';
        $this->cacheProvider->delete($id);
        return parent::reset($class);
    }

}
