document.addEventListener('DOMContentLoaded', async function () {
    const storyForm = document.getElementById('createStoryForm');
    const btnSaveStory = document.getElementById('btnSaveStory');
    const btnCancelStory = document.getElementById('btnCancelStory');
    const wordCount = document.getElementById('wordCount');
    const textarea = document.getElementById('story-content');
    btnSaveStory.disabled = true;

    // Historia pendiente
    const savedStory = localStorage.getItem('pending_story');
    if (savedStory) {
        const storyData = JSON.parse(savedStory);
        document.getElementById('story-title').value = storyData.title;
        document.getElementById('story-theme').value = storyData.theme;
        document.getElementById('guide-word').value = storyData.guideWord;
        document.getElementById('story-steps').value = storyData.steps;
        textarea.value = storyData.content;

        const words = textarea.value.trim().split(/\s+/).filter(word => word.length > 0);
        wordCount.textContent = "Nº palabras: " + words.length;

        storyForm.dispatchEvent(new Event('input', { bubbles: true }));
        setTimeout(() => {
            ['story-theme', 'guide-word', 'story-steps', 'story-content'].forEach(id => {
                document.getElementById(id).dispatchEvent(new Event('input', { bubbles: true }));
            });
        }, 100);
    }

    // Validación y autoguardado
    storyForm.addEventListener('input', function () {
        const title = document.getElementById('story-title').value.trim();
        const theme = document.getElementById('story-theme').value.trim();
        const guideInput = document.getElementById('guide-word');
        const steps = parseInt(document.getElementById('story-steps').value);
        const content = textarea.value.trim();

        const guideWords = guideInput.value.trim().split(/\s+/);
        if (guideWords.length > 1) {
            guideInput.value = guideWords[0];
        }

        const guideWord = guideInput.value.trim();
        const words = content.split(/\s+/).filter(word => word.length > 0);
        const wordLength = words.length;

        if (wordLength >= 600) {
            const limitedContent = words.slice(0, 600).join(" ");
            textarea.value = limitedContent;
            textarea.setAttribute('maxlength', textarea.value.length);
            wordCount.textContent = "Nº palabras: " + wordLength + " (máximo alcanzado)";
            wordCount.style.color = "red";
            btnSaveStory.disabled = true;
        } else {
            textarea.removeAttribute('maxlength');
            wordCount.textContent = "Nº palabras: " + wordLength;
            wordCount.style.color = wordLength > 550 ? "red" : "black";
            btnSaveStory.disabled = false;
        }

        localStorage.setItem('pending_story', JSON.stringify({
            title, theme, guideWord, steps, content, wordCount: wordLength, timestamp: new Date().getTime()
        }));

        const isFormValid =
            title.length > 0 &&
            theme.length > 0 &&
            steps >= 5 && steps <= 15 &&
            wordLength >= 150 && wordLength <= 600;

        btnSaveStory.disabled = !isFormValid;
    });

    // Cancelar
    btnCancelStory.addEventListener('click', function () {
        if (confirm("¿Seguro que deseas borrar el contenido?")) {
            storyForm.reset();
            wordCount.textContent = "Nº palabras: 0";
            wordCount.style.color = "black";
            localStorage.removeItem('pending_story');
        }
    });

    // Guardar
    btnSaveStory.addEventListener('click', async function (e) {
        e.preventDefault();

        try {
            const response = await fetch('/StoryTeller/php/check_login_status.php');
            const data = await response.json();

            if (!data.isLoggedIn) {
                const modal = document.getElementById('loginModal');
                modal.classList.add('show');

                document.getElementById('btnLogin').onclick = function () {
                    window.location.href = '/StoryTeller/views/login.php?redirect=create_story';
                };
                document.getElementById('btnRegister').onclick = function () {
                    window.location.href = '/StoryTeller/views/register.php?redirect=create_story';
                };
                document.getElementById('btnCancel').onclick = function () {
                    modal.classList.remove('show');
                };
            } else {
                const formData = new FormData(storyForm);
                formData.append('user_id', data.user_id);

                const saveResponse = await fetch('/StoryTeller/php/create_story.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await saveResponse.json();
                const errorBox = document.getElementById('errorMessage');
                errorBox.style.display = 'none';

                if (result.success) {
               
                    localStorage.removeItem('pending_story');
                    btnSaveStory.style.display = 'none';
                    btnCancelStory.style.display = 'none';
                    
                    const successBox = document.getElementById('successMessage');
                    if (successBox) {
                        successBox.innerHTML =
                            "<p>¡Cuento guardado con éxito!</p>" +
                            "<button id='goToProfile'>Ir a mi perfil</button>";
                        successBox.style.display = 'block';
                        errorBox.style.display = 'none';

                        const profileBtn = document.getElementById('goToProfile');
                        if (profileBtn) {
                            profileBtn.addEventListener('click', function () {
                                window.location.href = "/StoryTeller/php/profile.php?story_saved=" + result.story_saved;
                            });
                        }
                    }
                } else {
                    errorBox.style.display = 'block';
                    if (errorBox) {
                        errorBox.innerHTML =
                            "<p class><strong></strong> " +
                            (result.message ? result.message : "Error desconocido al guardar.") +
                            "</p>";
                        errorBox.style.display = 'block';
                    }
                }
            }
        } catch (err) {
            console.error("Error al verificar el login o guardar:", err);
            const errorBox = document.getElementById('errorMessage');
            if (errorBox) {
                errorBox.innerHTML = "<p><strong>Error inesperado.</strong> Inténtalo más tarde.</p>";
                errorBox.style.display = 'block';
            }
        }
    });
});
