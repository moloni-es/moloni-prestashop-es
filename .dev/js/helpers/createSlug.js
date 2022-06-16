const CreateSlug = (string) => {
    return string.replace(/[^a-zA-Z0-9]/g, '');
}

export default CreateSlug;