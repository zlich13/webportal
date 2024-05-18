function previewImage(event) {
    console.log
    const field = document.getElementById('input-file');
    const file = event.target.files[0];
    const reader = new FileReader();
    if (!file || !file.type.match(/image\/(png|jpe?g|jfif)/)) {
        alert("Only PNG, JPG, JPEG, and JFIF files are allowed!");
        field.value = '';
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        alert("Image size exceeds 2MB limit!");
        field.value = '';
        return;
    }
    reader.onload = function() {
        document.getElementById(`img-preview-file-input`).src = reader.result;
    }
    reader.readAsDataURL(file);
}