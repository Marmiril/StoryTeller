document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("preferencesForm");
    const colorInput = document.getElementById("favorite_color");

    const commonColors = [
        'Rojo', 'Azul', 'Verde', 'Amarillo', 'Negro', 'Blanco', 'Gris', 'Marrón',
        'Naranja', 'Rosa', 'Morado', 'Violeta', 'Turquesa', 'Beige', 'Dorado',
        'Plateado', 'Coral', 'Cian', 'Magenta', 'Lavanda', 'Índigo', 'Carmesí',
        'Esmeralda', 'Aguamarina', 'Salmón', 'Oliva', 'Granate', 'Fucsia',
        'Ocre', 'Púrpura', 'Celeste', 'Borgoña', 'Malva', 'Crema', 'Escarlata',
        'Jade', 'Lila', 'Marfil', 'Cobre', 'Ámbar', 'Melocotón', 'Menta',
        'Cereza', 'Ciruela', 'Castaño', 'Añil', 'Terracota', 'Chocolate',
        'Vainilla', 'Zafiro', 'Verde lima', 'Arena', 'Caqui', 'Gris claro',
        'Gris oscuro', 'Nieve', 'Humo', 'Océano', 'Carbón', 'Perla',
        'Rubí', 'Topacio', 'Bronce', 'Trigo', 'Fresón', 'Ceniza'
    ];

    const datalist = document.createElement("datalist");
    datalist.id = "color-list";

    commonColors.forEach(color => {
        const option = document.createElement("option");
        option.value = color;
        datalist.appendChild(option);
    });

    document.body.appendChild(datalist);
    colorInput.setAttribute("list", "color-list");

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        clearErrors();
    
        let isValid = true;
    
        // SOLO si se ha escrito algo
        const color = colorInput.value.trim();
        if (color) {
            const colorValid = commonColors.some(c => c.toLowerCase() === color.toLowerCase());
            if (!colorValid) {
                showError("favorite_color", "Color no válido o fuera de la lista.");
                isValid = false;
            }
        }
    
        ["favorite_number", "height", "weight", "age"].forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field.value && isNaN(field.value)) {
                showError(fieldId, "Valor inválido (debe ser numérico).");
                isValid = false;
            }
        });
        
        if (!isValid) return;
    
        const formData = new FormData(form);
    
        fetch("/StoryTeller/php/save_preferences.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert("Error al guardar: " + data.message);
            }
        })
        .catch(err => {
            console.error("Error:", err);
            alert("Hubo un error al conectar con el servidor.");
        });
    });

    function showError(inputId, message) {
        const input = document.getElementById(inputId);
        const span = document.createElement("span");
        span.className = "error-message";
        span.textContent = message;
        input.parentElement.appendChild(span);
    }

    function clearErrors() {
        document.querySelectorAll(".error-message").forEach(el => el.remove());
    }    
});


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
