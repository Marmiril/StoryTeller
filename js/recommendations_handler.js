document.addEventListener('DOMContentLoaded', function () {
    const btnCreateStory = document.getElementById('btnCreateStory');
    const recommendationsSection = document.querySelector('.recommendations-section');
    const btnBeginTale = document.getElementById('btnBeginTale');

    if (btnCreateStory && recommendationsSection) {
        btnCreateStory.addEventListener('click', function () {
            this.style.display = 'none';
            recommendationsSection.classList.add('active');
        });
    }

    if (btnBeginTale) {
        btnBeginTale.addEventListener('click', function () {
            window.location.href = '/StoryTeller/views/create_story.php';
        });
    }
});
