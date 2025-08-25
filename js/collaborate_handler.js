document.addEventListener('DOMContentLoaded', function () {
    const textarea = document.getElementById('collab-content');
    const wordCountDisplay = document.getElementById('collabWordCount');
    const btnSave = document.getElementById('btnSaveCollab');
    const btnCancel = document.getElementById('btnCancelCollab');
    const form = document.getElementById('collabForm');
    const successBox = document.getElementById('successMessage');

    const storyId = textarea.dataset.storyId;
    const localKey = 'pending_fragment_' + storyId;

    btnSave.disabled = true;

    // LocalStorage si se estaba escribiendo
    const saved = localStorage.getItem(localKey);
    if (saved) {
        textarea.value = saved;
        updateWordCount();
    }

    textarea.addEventListener('input', updateWordCount);

    function updateWordCount() {
        const words = textarea.value.trim().split(/\s+/).filter(w => w.length > 0);
        const wordCount = words.length;

        //600 palabras si se pasa
        if (wordCount > 600) {
            const limited = words.slice(0, 600).join(" ");
            textarea.value = limited;
            textarea.setAttribute('maxlength', textarea.value.length);
        } else {
            textarea.removeAttribute('maxlength');
        }

        // Actualizar contador
        wordCountDisplay.textContent = "Nº palabras: " + wordCount;
        wordCountDisplay.style.color = wordCount > 550 ? "red" : "black";

        // Progreso en localStorage
        localStorage.setItem(localKey, textarea.value);

        // Activar o desactivar botón según palabras
        btnSave.disabled = wordCount < 150 || wordCount > 600;
    }

    // Cancelar: borra fragmento
    btnCancel.addEventListener('click', function () {
        if (confirm("¿Seguro que deseas cancelar tu fragmento?")) {
            textarea.value = "";
            wordCountDisplay.textContent = "Nº palabras: 0";
            wordCountDisplay.style.color = "black";
            localStorage.removeItem(localKey);
            btnSave.disabled = true;
        }
    });

    // Redirección automática si éxito
    form.addEventListener("submit", function (e) {
        e.preventDefault(); 

        const formData = new FormData(form);

        
        fetch("../php/save_collaboration.php", {
            method: "POST",
            body: formData
        })
        .then(response => {
            if (response.redirected) {
                localStorage.removeItem(localKey);
                window.location.href = response.url;
            } else {
                return response.text().then(text => {
                    alert("Error inesperado: " + text);
                });
            }
        })
        .catch(error => {
            alert("Error de red: " + error.message);
        });
    });
});
