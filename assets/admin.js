$(document).ready(function () {
    "use strict";
    $('.ipsComposerCommand').on('click', function (e) {e.preventDefault(); executeComposerCommand($(this).data('command'), displayReponse); });
    $('.ipsEditComposerJson').on('click', function (e) {e.preventDefault(); editConfigJson(); });
    $('.ipsCancelComposerJson').on('click', function (e) {e.preventDefault(); location.reload();});
    $('.ipsSaveComposerJson').on('click', function (e) {e.preventDefault(); saveConfigJson();});

});

function saveConfigJson() {
    var config = $('textarea[data-mode]').val();
    var data = {
        securityToken: ip.securityToken,
        aa: 'Composer.saveConfig',
        config: config
    };
    $.post(ip.baseUrl, data, function (data) {
        location.reload();
    });

}

function editConfigJson() {
    $('.ipsSaveComposerJson').removeClass('hidden');
    $('.ipsComposerJsonPreview').addClass('hidden');

    $('.ipsEditComposerJson').addClass('hidden');
    $('.ipsComposerJsonForm').removeClass('hidden');
    $('.ipsCancelComposerJson').removeClass('hidden');
}

function executeComposerCommand(command, callback) {
    $('.ipsCommandCenter').addClass('hidden');
    $('.ipsLoader').removeClass('hidden');
    var data = {
        securityToken: ip.securityToken,
        aa: 'Composer.executeComposerCommand',
        command: command
    };
    $.post(ip.baseUrl, data, function (data) {
        callback(data.result);
    });

}

function displayReponse(response)
{
    $('.ipsCommandCenter').removeClass('hidden');
    $('.ipsLoader').addClass('hidden');
    var $modal = $('.ipsResumeModal');
    $modal.find('.ipsOk').off().on('click', function () {$modal.modal('hide');});
    $modal.find('.ipsModalBody').html(response);
    $modal.modal();

}
