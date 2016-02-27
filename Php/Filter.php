<?php
/**
 * @package ImpressPages
 *
 */
namespace Plugin\Php;


class Filter
{

    public static function ipPagePropertiesForm($form, $info)
    {

        if (ipAdminPermission('Php')) {

            $fieldset = new \Ip\Form\Fieldset(__('Php snippet', 'Php'));
            $form->addFieldset($fieldset);

            $form->addField(
                new CodeEditor(
                    array(
                        'name' => 'rawCode',
                        'layout' => \Ip\Form\Field::LAYOUT_NO_LABEL,
                        'value' => Model::getRawCode($info['pageId']),
                        'mode' => 'php',
                        'css' => 'ipPluginPhp-editor',
                    )
                )
            );
        }

        return $form;
    }
}
