const SyncCategories = ({ action }) => {
    $('#action_overlay_button').trigger('click');

    $.post({
        url: action,
        cache: false,
        data: {
            test: 'já foste'
        },
    }).then((response) => {
        console.log(response);
    });
}

export default SyncCategories;
