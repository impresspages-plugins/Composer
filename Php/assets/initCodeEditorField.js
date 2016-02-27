$(document).on('ipInitForms', function () {
    "use strict";

    //$('.ipsModuleFormPublic .type-onoff').onOffField(); //initializing forms in public interface

    //$('.type-codeeditor textarea').each(function () {
    $('textarea[data-mode]').each(function () {
        var defaultTextareaEditor = $(this);
        var _mode = defaultTextareaEditor.data('mode');
        var _theme = defaultTextareaEditor.data('theme') ? defaultTextareaEditor.data('theme') : 'tomorrow';
        var _editorBox = $('<div>', {
            'class': defaultTextareaEditor.attr('class')
        }).insertBefore(defaultTextareaEditor);

        defaultTextareaEditor.css('display', 'none');
        var aceEditor = ace.edit(_editorBox[0]);
        aceEditor.getSession().setValue(defaultTextareaEditor.val());
        //aceEditor.getSession().setMode("ace/mode/" + _mode, {pure:true}); // Set Ace editor mode
        aceEditor.getSession().setMode({path:"ace/mode/" + _mode, inline:true});
        aceEditor.setTheme("ace/theme/" + _theme); // Set Ace editor theme
        aceEditor.getSession().on('change', function () {
            defaultTextareaEditor.val(aceEditor.getSession().getValue());
        });
    });

});


