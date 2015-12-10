<?php

namespace yapcdi\converter;

/**
 * Description of PHPFile
 *
 * @author Alexander Schlegel
 */
class MetadataToPHPFile extends AbstractConverter implements ConverterInterface {

    private $resolver;
    private $config;
    private $useShortArrayTags;
    private $ignoreAnnotationErrors;

    public function __construct($useShortArrayTags = true, $ignoreAnnotationErrors = true) {
        parent::__construct();
        $this->resolver = new \yapcdi\resolver\MetadataResolver(true);
        $this->useShortArrayTags = $useShortArrayTags;
        $this->ignoreAnnotationErrors = $ignoreAnnotationErrors;
    }

    public function generate(array $classes) {
        $this->config = array();
        foreach ($classes as $class) {
            $ref = new \ReflectionClass($class);
            $className = $ref->getName();
            try {
                $aliases = $this->resolver->getMethodParameterAliases($ref->getConstructor());
                $setters = $this->resolver->getSetters($ref);
                $properties = $this->resolver->getInjectionProperties($ref);
            } catch (\yapcdi\exception\AnnotationException $exc) {
                if ($this->ignoreAnnotationErrors) {
                    continue;
                }
                throw $exc;
            }

            $config = array();
            if (count($aliases) > 0) {
                $config['constructor'] = $this->resolver->getMethodParameters($ref->getConstructor(), $aliases);
            }
            if (count($setters) > 0 && count($properties) === 0) {
                $config['setters'] = $setters;
            }
            if (count($properties) > 0) {
                $config['properties'] = $properties;
            }
            if (count($config) > 0) {
                $this->config[$className] = $config;
            }
        }
    }

    public function getConfig() {
        return $this->config;
    }

    public function getConvertedConfig() {
        $content = "<?php\n\rreturn ";
        $content .= str_replace('\\\\', '\\', var_export($this->getConfig(), true));
        if ($this->useShortArrayTags) {
            $content .= str_replace(
                    array('array (', '),', ');'), array('[', '],', '];'), $content);
        }
        return $content . ";\n";
    }

}
