<?php

namespace yapcdi\controller\resolver\symfony;

use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use yapcdi\dic\DIContainerInterface;
use Doctrine\Common\Cache\Cache;

/**
 * Description of ControllerResolver
 *
 * @author Alexander Schlegel
 */
class ReflectionCacheControllerResolver extends AbstractResolver {

    /**
     *
     * @var Cache
     */
    private $cacheProvider;

    public function __construct(DIContainerInterface $diContainer, Cache $provider, LoggerInterface $logger = null) {
        parent::__construct($diContainer, $logger);
        $this->cacheProvider = $provider;
    }

    public function getArguments(Request $request, $controller) {
        if (!is_array($controller)) {
            throw new \InvalidArgumentException('Can not resolve arguments '
            . 'for the controller type: "'
            . (($controller instanceof \Closure) ? 'closure' : gettype($controller))
            . '" for URI "' . $request->getPathInfo() . '"');
        }
        $id = get_class($controller[0]) . '::' . $controller[1] . '#method-parameters';
        
        if (($parameters = $this->cacheProvider->fetch($id)) === false) {
            $parameters = $this->getParameters($controller[0], $controller[1]);
            $this->cacheProvider->save($id, $parameters);
        }
        $attributes = $request->attributes->all();
        $arguments = array();
        foreach ($parameters as $name => $options) {
            if (array_key_exists($name, $attributes)) {
                $arguments[] = $attributes[$name];
                continue;
            }
            if (array_key_exists('defaultValue', $options)) {
                $arguments[] = $options['defaultValue'];
                continue;
            }
            throw new \RuntimeException('Controller '
            . get_class($controller[0])
            . '::' . $controller[1] . ' requires that you provide a value '
            . 'for the "$' . $name
            . '" argument (because there is no default value or '
            . 'because there is a non optional argument after this one).');
        }
        return $arguments;
    }

    protected function getParameters($class, $method) {
        $ref = new \ReflectionMethod($class, $method);
        $parameters = $ref->getParameters();
        $result = array();
        foreach ($parameters as $param) {
            $result[$param->getName()] = array();
            if ($param->isDefaultValueAvailable()) {
                $result[$param->getName()]['defaultValue'] = $param->getDefaultValue();
            }
        }
        return $result;
    }

}
