$(function () {

$('#provider').addOption(providers, false).change(function () {
    var actions = providerActions[$(this).val()].actions;
    $('#action').removeOption(/^.+/i).addOption(actions, false).trigger('change');
});

$('#action').change(function () {
    var providerName = $('#provider').val();
    var methodName = $(this).val();
    var parameters = providerActions[providerName].parameters[methodName];
    var html = '';
    for(var i in parameters) {
        var attrs = parameters[i];
        var fieldId = String(providerName + '-' + methodName + i).toLowerCase();
        html += '<p';
        if (null !== attrs.description) {
            html += ' title="' + attrs.description + '"';
        }
        html += '><label for="' + fieldId + '">' + i + '</label>';
        html += '<input id="' + fieldId + '" name="' + attrs.name + '"';
        switch (attrs.type) {
            case 'bool':
            case 'boolean':
                html += ' type="checkbox"';
                if (!!attrs['default']) {
                    html += ' checked="checked"';
                }
                break;
            case 'string':
            default:
                html += ' type="text"';
                if (!!attrs['default']) {
                    html += ' value="' + attrs['default'] + '"';
                }
                break;
        }
        html += ' /></p>'
    }
    $('#parameterInfo').html(html);
});

$('#zendToolForm').ajaxForm({
    success: function (html) {
        $('#toolInfo pre').html(html);
    },
    dataType: 'html'
});

});