import MakeRequest from "./makeRequest";

const SyncStock = async ({ action }) => {
    const actionButton = $('#action_overlay_button');
    const actionModal = $('#action_overlay_modal');
    const closeButton = actionModal.find('#action_overlay_button');
    const spinner = actionModal.find('#action_overlay_spinner');
    const content = actionModal.find('#action_overlay_content');

    content.html('').hide();
    closeButton.hide();
    spinner.show();
    actionButton.trigger('click');

    let page = 1;
    let syncedProducts = [];
    let errorProducts = [];

    const sync = async () => {
        let resp = await MakeRequest(action, { page });

        console.log(resp);

        if (page === 0) {
            spinner.hide(100, function () {
                content.show(200);
            });
        }

        content.html(resp.overlayContent);

        syncedProducts = syncedProducts.concat(resp.products);
        errorProducts = errorProducts.concat(resp.errorProducts);

        if (resp.hasMore && actionModal.is(':visible')) {
            page = page + 1;

            return await sync();
        }
    }

    await sync();

    closeButton.show(200);

    console.log(syncedProducts);
    console.log(errorProducts);
}

export default SyncStock;
