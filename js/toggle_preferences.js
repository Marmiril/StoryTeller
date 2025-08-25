document.addEventListener("DOMContentLoaded", function () {
    const viewBlock = document.getElementById("preferences-view");
    const formBlock = document.getElementById("preferencesForm");
    const editBtn = document.getElementById("edit-preferences-btn");
    const cancelBtn = document.getElementById("cancel-edit-btn");

    if (editBtn) {
        editBtn.addEventListener("click", function () {
            viewBlock.style.display = "none";
            formBlock.style.display = "block";
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener("click", function () {
            formBlock.style.display = "none";
            viewBlock.style.display = "block";
        });
    }
});
