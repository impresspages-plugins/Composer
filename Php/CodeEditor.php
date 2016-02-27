<?php
/**
 * @package   ImpressPages
 */

namespace Plugin\Php;


class CodeEditor extends \Ip\Form\Field
{

    protected $mode;
    protected $theme;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {

        parent::__construct($options);

        if (!empty($options['mode'])) {
            $this->setMode($options['mode']);
        }

        if (!empty($options['theme'])) {
            $this->setTheme($options['theme']);
        }
    }

    public function render($doctype, $environment)
    {
        return '
        <textarea ' . $this->getAttributesStr($doctype) . ' class="form-control ' . implode(
            ' ',
            $this->getClasses()
        ) . '" name="' . escattr($this->getName()) . '"'
        . $this->getMode() . ' ' . $this->getTheme() . ' ' . $this->getValidationAttributesStr(
            $doctype
        ) . ' >' . escTextarea($this->getValue()) .
        '</textarea>';
    }

    public function getTypeClass()
    {
        return 'codeeditor';
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    public function getMode()
    {
        if ($this->mode) {
            return ' data-mode="' . $this->mode . '"';
        }
    }

    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    public function getTheme()
    {
        if ($this->theme) {
            return ' data-theme="' . $this->theme . '"';
        }
    }

}
