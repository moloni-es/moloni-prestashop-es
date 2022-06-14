import MakeRequest from "./makeRequest";

const SyncStock = async ({ action }) => {
    const actionButton = $('#action_overlay_button');
    const actionModal = $('#action_overlay_modal');
    const closeButton = actionModal.find('#action_overlay_button');
    const spinner = actionModal.find('#action_overlay_spinner');
    const content = actionModal.find('#action_overlay_content');
    const error = actionModal.find('#action_overlay_error');

    content.html('').hide();
    closeButton.hide();
    error.hide();
    spinner.show();
    actionButton.trigger('click');

    let page = 1;

    const toogleContent = () => {
        spinner.fadeOut(100, function () {
            content.fadeIn(200);
        });
    }

    const sync = async () => {
        let resp = await MakeRequest(action, { page });
        resp = JSON.parse(resp);

        if (page === 1) {
            toogleContent();
        }

        content.html(resp.overlayContent);

        if (resp.hasMore && actionModal.is(':visible')) {
            page = page + 1;

            return await sync();
        }
    }

    try {
        await sync();
    } catch (ex) {
        spinner.fadeOut(50);
        content.fadeOut(50);
        error.fadeIn(200);
    }

    closeButton.show(200);
}

export default SyncStock;
