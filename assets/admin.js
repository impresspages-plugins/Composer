$(document).ready(function () {
    "use strict";
    $('.ipsComposerCommand').on('click', function (e) {e.preventDefault(); executeComposerCommand($(this).data('command')); });
    $('.ipsEditComposerJson').on('click', function (e) {e.preventDefault(); editConfigJson(); });
    $('.ipsCancelComposerJson').on('click', function (e) {e.preventDefault(); location.reload();});
});

function editConfigJson() {
    $('.ipsSaveComposerJson').removeClass('hidden');
    $('.ipsComposerJsonPreview').addClass('hidden');

    $('.ipsEditComposerJson').addClass('hidden');
    $('.ipsComposerJsonForm').removeClass('hidden');
    $('.ipsCancelComposerJson').removeClass('hidden');
}

function executeComposerCommand(command) {
    var data = {
        securityToken: ip.securityToken,
        aa: 'Composer.' + command
    };
    $.post(ip.baseUrl + "error_report.php", data, function (data) {
        alert(data.response);
    });
}
