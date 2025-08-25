document.addEventListener('DOMContentLoaded', function () {
    const btnStart = document.getElementById('btnStartCollab');
    const storyId = new URLSearchParams(window.location.search).get('id');

    if (!btnStart) return;

    btnStart.addEventListener('click', async function () {
        try {
            const response = await fetch('/StoryTeller/php/check_login_status.php');
            const data = await response.json();

            if (!data.isLoggedIn) {
                const modal = document.getElementById('loginModal');
                modal.classList.add('show');

                document.getElementById('btnLogin').onclick = () =>
                    window.location.href = '/StoryTeller/views/login.php?redirect=collaborate_story&id=' + storyId;

                document.getElementById('btnRegister').onclick = () =>
                    window.location.href = '/StoryTeller/views/register.php?redirect=collaborate_story&id=' + storyId;

                document.getElementById('btnCancel').onclick = () =>
                    modal.classList.remove('show');
            } else {
                const check = await fetch('/StoryTeller/php/check_collaboration_status.php?story_id=' + storyId);
                const result = await check.json();

                if (result.alreadyCollaborated) {
                    window.location.href = '/StoryTeller/php/consult_tale.php?id=' + storyId + '&notice=already_done';
                } else {
                    window.location.href = '/StoryTeller/php/collaborate_story.php?id=' + storyId;
                }
            }
        } catch (err) {
            console.error("Error comprobando sesión o colaboración:", err);
            alert("Ha ocurrido un error. Inténtalo más tarde.");
        }
    });
});
