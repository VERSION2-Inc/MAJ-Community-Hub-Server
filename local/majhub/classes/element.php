<?php
/**
 *  MAJ Hub
 *  
 *  @author  VERSION2, Inc. (http://ver2.jp)
 *  @version $Id: element.php 104 2012-11-23 04:59:04Z malu $
 */
namespace majhub;

/**
 *  HTML element
 */
class element
{
    /** @var string */
    private $tagName;
    /** @var string[] */
    private $attributes;
    /** @var string[] */
    private $styles;
    /** @var element[] */
    private $children;

    /**
     *  Constructor
     *  
     *  @param string $tagName
     */
    public function __construct($tagName)
    {
        $this->tagName    = $tagName;
        $this->attributes = array();
        $this->styles     = array();
        $this->children   = array();
    }

    /**
     *  Stringify
     */
    public function __toString()
    {
        $styles = array();
        if (isset($this->attributes['style']))
            $styles[] = $this->attributes['style'];
        foreach ($this->styles as $name => $value)
            $styles[] = $name . ':' . $value . ';';
        $attr = empty($styles) ? array() : array('style' => implode(' ', $styles));
        return empty($this->children)
             ? \html_writer::empty_tag($this->tagName, $this->attributes + $attr)
             : \html_writer::tag($this->tagName, implode('', $this->children), $this->attributes + $attr);
    }

    /**
     *  Sets an attribute
     *  
     *  @param string $name
     *  @param array $args
     *  @return element
     */
    public function __call($name, array $args)
    {
        if (count($args) != 1)
            throw new \InvalidArgumentException();
        $value = reset($args);
        if (is_bool($value)) {
            if ($value)
                $this->attributes[$name] = $name;
            else
                unset($this->attributes[$name]);
        } else {
            $this->attributes[$name] = $value;
        }
        return $this;
    }

    /**
     *  Sets classes
     *  
     *  @param string[] $class ...
     *  @return element
     */
    public function classes(/*$class, ...*/)
    {
        $classes = self::flatten(func_get_args());
        $this->attributes['class'] = implode(' ', iterator_to_array($classes));
        return $this;
    }

    /**
     *  Sets a style
     *  
     *  @param string $name
     *  @param string $value
     *  @return element
     */
    public function style($name, $value)
    {
        $this->styles[$name] = $value;
        return $this;
    }

    /**
     *  Sets styles
     *  
     *  @param array $styles
     *  @return element
     */
    public function styles(array $styles)
    {
        foreach ($styles as $name => $value)
            $this->styles[$name] = $value;
        return $this;
    }

    /**
     *  Appends a child element
     *  
     *  @param mixed[] $element ...
     *  @return element
     */
    public function append(/*element $element, ...*/)
    {
        foreach (func_get_args() as $element) {
            if (is_array($element))
                call_user_func_array(array($this, 'append'), $element);
            else
                $this->children[] = $element;
        }
        return $this;
    }

    /**
     *  Flattens an array recursively
     *  
     *  @param array $array
     *  @return \Traversable
     */
    private static function flatten(array $array)
    {
        return new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));
    }
}
