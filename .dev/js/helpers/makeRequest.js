const MakeRequest = async (action, data) => {
    return $.post({
        url: action,
        cache: false,
        data,
    });
}

export default MakeRequest;
